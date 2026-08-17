<?php

require_once("../config/conexion.php");
require_once("../librerias/fpdf/fpdf.php");

/* =========================================
   ACTUALIZAR ESTADOS
========================================= */

$sqlActualizar = "UPDATE membresias
                  SET estado = 'Vencida'
                  WHERE fecha_fin < CURDATE()
                  AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizar);

/* =========================================
   RECIBIR FILTROS
========================================= */

$buscar = trim($_GET["buscar"] ?? "");
$tipoFiltro = trim($_GET["tipo"] ?? "");
$estadoFiltro = trim($_GET["estado"] ?? "");

$textoBuscar = "%" . $buscar . "%";

/* =========================================
   CONSULTAR MEMBRESÍAS FILTRADAS
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
        WHERE
            (
                c.cedula LIKE ?
                OR c.nombres LIKE ?
                OR c.apellidos LIKE ?
                OR CONCAT(c.nombres, ' ', c.apellidos) LIKE ?
            )
            AND (? = '' OR m.tipo = ?)
            AND (? = '' OR m.estado = ?)
        ORDER BY m.id_membresia ASC";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssss",
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $tipoFiltro,
    $tipoFiltro,
    $estadoFiltro,
    $estadoFiltro
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

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

/* =========================================
   MOSTRAR FILTROS APLICADOS
========================================= */

$filtrosAplicados = [];

if ($buscar !== "") {
    $filtrosAplicados[] =
        "Cliente: " . $buscar;
}

if ($tipoFiltro !== "") {
    $filtrosAplicados[] =
        "Tipo: " . $tipoFiltro;
}

if ($estadoFiltro !== "") {
    $filtrosAplicados[] =
        "Estado: " . $estadoFiltro;
}

if (!empty($filtrosAplicados)) {

    $pdf->SetFont("Arial", "", 9);

    $pdf->Cell(
        0,
        7,
        utf8_decode(
            "Filtros: " .
            implode(" | ", $filtrosAplicados)
        ),
        0,
        1,
        "C"
    );
}

$pdf->Ln(5);

/* =========================================
   CABECERAS
========================================= */

$pdf->SetFillColor(190, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(80, 80, 80);
$pdf->SetFont("Arial", "B", 9);

$pdf->Cell(12, 9, "ID", 1, 0, "C", true);

$pdf->Cell(
    27,
    9,
    utf8_decode("Cédula"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    58,
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
    34,
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
    }

    if ($membresia["estado"] === "Vencida") {
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
        strtotime(
            $membresia["fecha_inicio"]
        )
    );

    $fechaFin = date(
        "d/m/Y",
        strtotime(
            $membresia["fecha_fin"]
        )
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
        27,
        8,
        utf8_decode(
            $membresia["cedula"]
        ),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        58,
        8,
        utf8_decode(
            $nombreCompleto
        ),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        32,
        8,
        utf8_decode(
            $membresia["tipo"]
        ),
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

    if ($membresia["estado"] === "Activa") {

        $pdf->SetTextColor(
            0,
            130,
            0
        );

    } else {

        $pdf->SetTextColor(
            200,
            0,
            0
        );
    }

    $pdf->SetFont(
        "Arial",
        "B",
        9
    );

    $pdf->Cell(
        34,
        8,
        utf8_decode(
            $membresia["estado"]
        ),
        1,
        1,
        "C"
    );

    $pdf->SetTextColor(
        0,
        0,
        0
    );

    $pdf->SetFont(
        "Arial",
        "",
        9
    );
}

/* =========================================
   SIN RESULTADOS
========================================= */

if ($totalMembresias === 0) {

    $pdf->SetFont(
        "Arial",
        "I",
        10
    );

    $pdf->Cell(
        258,
        12,
        utf8_decode(
            "No se encontraron membresías con los filtros seleccionados."
        ),
        1,
        1,
        "C"
    );
}

/* =========================================
   RESUMEN
========================================= */

$pdf->Ln(6);

$pdf->SetFont(
    "Arial",
    "B",
    10
);

/* TOTAL */

$pdf->SetTextColor(
    0,
    0,
    0
);

$pdf->Cell(
    86,
    9,
    utf8_decode(
        "Total de membresías: " .
        $totalMembresias
    ),
    1,
    0,
    "C"
);

/* ACTIVAS */

$pdf->SetTextColor(
    0,
    130,
    0
);

$pdf->Cell(
    86,
    9,
    "Activas: " .
    $totalActivas,
    1,
    0,
    "C"
);

/* VENCIDAS */

$pdf->SetTextColor(
    200,
    0,
    0
);

$pdf->Cell(
    86,
    9,
    "Vencidas: " .
    $totalVencidas,
    1,
    1,
    "C"
);

$pdf->SetTextColor(
    0,
    0,
    0
);

/* =========================================
   NOMBRE DEL ARCHIVO
========================================= */

$nombreArchivo =
    "reporte_membresias";

if ($tipoFiltro !== "") {

    $nombreArchivo .=
        "_" .
        strtolower(
            $tipoFiltro
        );
}

if ($estadoFiltro !== "") {

    $nombreArchivo .=
        "_" .
        strtolower(
            $estadoFiltro
        );
}

$nombreArchivo .=
    "_" .
    date("Y-m-d") .
    ".pdf";

/* =========================================
   MOSTRAR PDF
========================================= */

$pdf->Output(
    "I",
    $nombreArchivo
);

?>