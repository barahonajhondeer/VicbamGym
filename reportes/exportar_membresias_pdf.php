<?php

require_once("../config/conexion.php");
require_once("../librerias/fpdf/fpdf.php");

/* =========================================
   ACTUALIZAR MEMBRESÍAS VENCIDAS
========================================= */

$sqlActualizar = "UPDATE membresias
                  SET estado = 'Vencida'
                  WHERE fecha_fin < CURDATE()
                  AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizar);

/* =========================================
   CONSULTAR MEMBRESÍAS
========================================= */

$sql = "SELECT
            m.id_membresia,
            c.cedula,
            c.nombres,
            c.apellidos,
            m.tipo,
            m.valor,
            m.fecha_inicio,
            m.fecha_fin,
            m.estado
        FROM membresias m
        INNER JOIN clientes c
            ON m.id_cliente = c.id_cliente
        ORDER BY m.id_membresia ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die(
        "Error al consultar las membresías: " .
        mysqli_error($conexion)
    );
}

/* =========================================
   CONTADORES
========================================= */

$totalMembresias = 0;
$totalActivas = 0;
$totalVencidas = 0;

/* =========================================
   CREAR PDF
========================================= */

$pdf = new FPDF("L", "mm", "A4");

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();

/* =========================================
   TÍTULO
========================================= */

$pdf->SetFont("Arial", "B", 18);

$pdf->Cell(
    0,
    10,
    "VICBAMGYM",
    0,
    1,
    "C"
);

$pdf->SetFont("Arial", "B", 14);

$pdf->Cell(
    0,
    9,
    utf8_decode("REPORTE DE MEMBRESÍAS"),
    0,
    1,
    "C"
);

$pdf->SetFont("Arial", "", 10);

$pdf->Cell(
    0,
    7,
    utf8_decode(
        "Fecha de generación: " .
        date("d/m/Y H:i")
    ),
    0,
    1,
    "C"
);

$pdf->Ln(5);

/* =========================================
   ENCABEZADOS DE LA TABLA
========================================= */

$pdf->SetFillColor(190, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(80, 80, 80);
$pdf->SetFont("Arial", "B", 9);

$pdf->Cell(12, 9, "ID", 1, 0, "C", true);

$pdf->Cell(
    25,
    9,
    utf8_decode("Cédula"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    55,
    9,
    "Cliente",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    32,
    9,
    utf8_decode("Membresía"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    25,
    9,
    "Valor",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    35,
    9,
    "Fecha inicio",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    35,
    9,
    "Fecha fin",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    35,
    9,
    "Estado",
    1,
    1,
    "C",
    true
);

/* =========================================
   DATOS
========================================= */

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont("Arial", "", 9);

while ($membresia = mysqli_fetch_assoc($resultado)) {

    $totalMembresias++;

    if ($membresia["estado"] === "Activa") {
        $totalActivas++;
    } else {
        $totalVencidas++;
    }

    $nombreCompleto =
        $membresia["nombres"] .
        " " .
        $membresia["apellidos"];

    $nombreCompleto = mb_strimwidth(
        $nombreCompleto,
        0,
        35,
        "...",
        "UTF-8"
    );

    $fechaInicio = date(
        "d/m/Y",
        strtotime($membresia["fecha_inicio"])
    );

    $fechaFin = date(
        "d/m/Y",
        strtotime($membresia["fecha_fin"])
    );

    $valor = "$" . number_format(
        (float) $membresia["valor"],
        2
    );

    $pdf->Cell(
        12,
        8,
        $membresia["id_membresia"],
        1,
        0,
        "C"
    );

    $pdf->Cell(
        25,
        8,
        utf8_decode($membresia["cedula"]),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        55,
        8,
        utf8_decode($nombreCompleto),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        32,
        8,
        utf8_decode($membresia["tipo"]),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        25,
        8,
        $valor,
        1,
        0,
        "R"
    );

    $pdf->Cell(
        35,
        8,
        $fechaInicio,
        1,
        0,
        "C"
    );

    $pdf->Cell(
        35,
        8,
        $fechaFin,
        1,
        0,
        "C"
    );

    /*
    | Cambiar el color del texto según el estado.
    */

    if ($membresia["estado"] === "Activa") {

        $pdf->SetTextColor(0, 130, 0);

    } else {

        $pdf->SetTextColor(200, 0, 0);
    }

    $pdf->SetFont("Arial", "B", 9);

    $pdf->Cell(
        35,
        8,
        utf8_decode($membresia["estado"]),
        1,
        1,
        "C"
    );

    /*
    | Volver al color y fuente normales.
    */

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont("Arial", "", 9);
}

/* =========================================
   RESUMEN
========================================= */

$pdf->Ln(6);

$pdf->SetFont("Arial", "B", 10);

$pdf->Cell(
    80,
    8,
    utf8_decode(
        "Total de membresías: " .
        $totalMembresias
    ),
    1,
    0,
    "C"
);

$pdf->SetTextColor(0, 130, 0);

$pdf->Cell(
    80,
    8,
    "Activas: " . $totalActivas,
    1,
    0,
    "C"
);

$pdf->SetTextColor(200, 0, 0);

$pdf->Cell(
    80,
    8,
    "Vencidas: " . $totalVencidas,
    1,
    1,
    "C"
);

$pdf->SetTextColor(0, 0, 0);

/* =========================================
   MOSTRAR PDF
========================================= */

$pdf->Output(
    "I",
    "reporte_membresias_" .
    date("Y-m-d") .
    ".pdf"
);

?>