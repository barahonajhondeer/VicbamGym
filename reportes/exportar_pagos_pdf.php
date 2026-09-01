<?php

require_once("../config/conexion.php");
require_once("../librerias/fpdf/fpdf.php");

/* =========================================
   RECIBIR FILTROS
========================================= */

$buscar = trim($_GET["buscar"] ?? "");
$fecha_inicio = $_GET["fecha_inicio"] ?? "";
$fecha_fin = $_GET["fecha_fin"] ?? "";
$metodoFiltro = trim($_GET["metodo"] ?? "");

$textoBuscar = "%" . $buscar . "%";

/* =========================================
   CONSULTA FILTRADA
========================================= */

$sql = "SELECT
            p.id_pago,
            p.valor,
            p.metodo_pago,
            p.fecha_pago,

            c.cedula,
            c.nombres,
            c.apellidos,

            m.tipo

        FROM pagos p

        INNER JOIN clientes c
            ON p.id_cliente = c.id_cliente

        INNER JOIN membresias m
            ON p.id_membresia = m.id_membresia

        WHERE p.estado = 'Registrado'
            (
                c.cedula LIKE ?
                OR c.nombres LIKE ?
                OR c.apellidos LIKE ?
                OR CONCAT(c.nombres, ' ', c.apellidos) LIKE ?
            )

            AND (
                ? = ''
                OR p.fecha_pago >= ?
            )

            AND (
                ? = ''
                OR p.fecha_pago <= ?
            )

            AND (
                ? = ''
                OR p.metodo_pago = ?
            )

        ORDER BY
            p.fecha_pago ASC,
            p.id_pago ASC";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssss",

    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,

    $fecha_inicio,
    $fecha_inicio,

    $fecha_fin,
    $fecha_fin,

    $metodoFiltro,
    $metodoFiltro
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    die(
        "Error al consultar los pagos: " .
        mysqli_error($conexion)
    );
}

/* =========================================
   CREAR PDF
========================================= */

$pdf = new FPDF(
    "L",
    "mm",
    "A4"
);

$pdf->SetMargins(
    10,
    10,
    10
);

$pdf->SetAutoPageBreak(
    true,
    15
);

$pdf->AddPage();

/* =========================================
   TÍTULO
========================================= */

$pdf->SetFont(
    "Arial",
    "B",
    18
);

$pdf->Cell(
    0,
    10,
    "VICBAMGYM",
    0,
    1,
    "C"
);

$pdf->SetFont(
    "Arial",
    "B",
    14
);

$pdf->Cell(
    0,
    9,
    "REPORTE DE PAGOS",
    0,
    1,
    "C"
);

$pdf->SetFont(
    "Arial",
    "",
    10
);

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
   MOSTRAR FILTROS
========================================= */

$filtrosAplicados = [];

if ($buscar !== "") {

    $filtrosAplicados[] =
        "Cliente: " . $buscar;
}

if ($fecha_inicio !== "") {

    $filtrosAplicados[] =
        "Desde: " .
        date(
            "d/m/Y",
            strtotime($fecha_inicio)
        );
}

if ($fecha_fin !== "") {

    $filtrosAplicados[] =
        "Hasta: " .
        date(
            "d/m/Y",
            strtotime($fecha_fin)
        );
}

if ($metodoFiltro !== "") {

    $filtrosAplicados[] =
        "Método: " .
        $metodoFiltro;
}

if (!empty($filtrosAplicados)) {

    $pdf->SetFont(
        "Arial",
        "",
        9
    );

    $pdf->Cell(
        0,
        7,
        utf8_decode(
            "Filtros: " .
            implode(
                " | ",
                $filtrosAplicados
            )
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

$pdf->SetFillColor(
    190,
    0,
    0
);

$pdf->SetTextColor(
    255,
    255,
    255
);

$pdf->SetDrawColor(
    80,
    80,
    80
);

$pdf->SetFont(
    "Arial",
    "B",
    9
);

$pdf->Cell(
    15,
    9,
    "ID",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    28,
    9,
    utf8_decode("Cédula"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    65,
    9,
    "Cliente",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    38,
    9,
    utf8_decode("Membresía"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    30,
    9,
    "Valor",
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    45,
    9,
    utf8_decode("Método"),
    1,
    0,
    "C",
    true
);

$pdf->Cell(
    37,
    9,
    "Fecha",
    1,
    1,
    "C",
    true
);

/* =========================================
   DATOS
========================================= */

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

$totalPagos = 0;
$totalRecaudado = 0;

while (
    $pago =
    mysqli_fetch_assoc($resultado)
) {

    $totalPagos++;

    $totalRecaudado +=
        (float) $pago["valor"];

    $nombreCompleto =
        $pago["nombres"] .
        " " .
        $pago["apellidos"];

    $nombreCompleto =
        mb_strimwidth(
            $nombreCompleto,
            0,
            40,
            "...",
            "UTF-8"
        );

    $valor =
        "$" .
        number_format(
            (float) $pago["valor"],
            2
        );

    $fechaPago =
        date(
            "d/m/Y",
            strtotime(
                $pago["fecha_pago"]
            )
        );

    $pdf->Cell(
        15,
        8,
        $pago["id_pago"],
        1,
        0,
        "C"
    );

    $pdf->Cell(
        28,
        8,
        utf8_decode(
            $pago["cedula"]
        ),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        65,
        8,
        utf8_decode(
            $nombreCompleto
        ),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        38,
        8,
        utf8_decode(
            $pago["tipo"]
        ),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        30,
        8,
        $valor,
        1,
        0,
        "R"
    );

    $pdf->Cell(
        45,
        8,
        utf8_decode(
            $pago["metodo_pago"]
        ),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        37,
        8,
        $fechaPago,
        1,
        1,
        "C"
    );
}

/* =========================================
   SIN RESULTADOS
========================================= */

if ($totalPagos === 0) {

    $pdf->SetFont(
        "Arial",
        "I",
        10
    );

    $pdf->Cell(
        258,
        12,
        utf8_decode(
            "No se encontraron pagos con los filtros seleccionados."
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

/* PAGOS ENCONTRADOS */

$pdf->SetTextColor(
    0,
    0,
    0
);

$pdf->Cell(
    120,
    9,
    "Pagos encontrados: " .
    $totalPagos,
    1,
    0,
    "C"
);

/* TOTAL RECAUDADO */

$pdf->SetTextColor(
    0,
    130,
    0
);

$pdf->Cell(
    138,
    9,
    "Total recaudado: $" .
    number_format(
        $totalRecaudado,
        2
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

/* =========================================
   NOMBRE DEL ARCHIVO
========================================= */

$nombreArchivo =
    "reporte_pagos";

if ($fecha_inicio !== "") {

    $nombreArchivo .=
        "_desde_" .
        $fecha_inicio;
}

if ($fecha_fin !== "") {

    $nombreArchivo .=
        "_hasta_" .
        $fecha_fin;
}

if ($metodoFiltro !== "") {

    $nombreArchivo .=
        "_" .
        strtolower(
            $metodoFiltro
        );
}

$nombreArchivo .=
    ".pdf";

/* =========================================
   MOSTRAR PDF
========================================= */

$pdf->Output(
    "I",
    $nombreArchivo
);

?>