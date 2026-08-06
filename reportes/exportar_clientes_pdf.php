<?php

require_once("../config/conexion.php");
require_once("../librerias/fpdf/fpdf.php");

/*
|--------------------------------------------------------------------------
| Consultar clientes
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id_cliente,
            cedula,
            nombres,
            apellidos,
            telefono,
            correo,
            direccion,
            fecha_registro
        FROM clientes
        ORDER BY id_cliente ASC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error al consultar los clientes: " . mysqli_error($conexion));
}

/*
|--------------------------------------------------------------------------
| Crear PDF
|--------------------------------------------------------------------------
|
| L  = orientación horizontal
| mm = unidad de medida
| A4 = tamaño de página
|
*/

$pdf = new FPDF("L", "mm", "A4");

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();

/*
|--------------------------------------------------------------------------
| Título
|--------------------------------------------------------------------------
*/

$pdf->SetFont("Arial", "B", 18);

$pdf->Cell(
    0,
    10,
    utf8_decode("VICBAMGYM"),
    0,
    1,
    "C"
);

$pdf->SetFont("Arial", "B", 14);

$pdf->Cell(
    0,
    9,
    utf8_decode("REPORTE DE CLIENTES"),
    0,
    1,
    "C"
);

$pdf->SetFont("Arial", "", 10);

$pdf->Cell(
    0,
    7,
    utf8_decode(
        "Fecha de generación: " . date("d/m/Y H:i")
    ),
    0,
    1,
    "C"
);

$pdf->Ln(5);

/*
|--------------------------------------------------------------------------
| Encabezados de la tabla
|--------------------------------------------------------------------------
*/

$pdf->SetFillColor(190, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(80, 80, 80);
$pdf->SetFont("Arial", "B", 9);

$pdf->Cell(10, 9, "ID", 1, 0, "C", true);
$pdf->Cell(25, 9, utf8_decode("Cédula"), 1, 0, "C", true);
$pdf->Cell(35, 9, "Nombres", 1, 0, "C", true);
$pdf->Cell(35, 9, "Apellidos", 1, 0, "C", true);
$pdf->Cell(25, 9, utf8_decode("Teléfono"), 1, 0, "C", true);
$pdf->Cell(50, 9, "Correo", 1, 0, "C", true);
$pdf->Cell(55, 9, utf8_decode("Dirección"), 1, 0, "C", true);
$pdf->Cell(32, 9, "Registro", 1, 1, "C", true);

/*
|--------------------------------------------------------------------------
| Datos de la tabla
|--------------------------------------------------------------------------
*/

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont("Arial", "", 8);

$totalClientes = 0;

while ($cliente = mysqli_fetch_assoc($resultado)) {

    $totalClientes++;

    $fechaRegistro = "Sin fecha";

    if (!empty($cliente["fecha_registro"])) {

        $fechaRegistro = date(
            "d/m/Y",
            strtotime($cliente["fecha_registro"])
        );
    }

    /*
    | Limitar textos largos para evitar que salgan de las celdas.
    */

    $correo = mb_strimwidth(
        $cliente["correo"],
        0,
        30,
        "...",
        "UTF-8"
    );

    $direccion = mb_strimwidth(
        $cliente["direccion"],
        0,
        35,
        "...",
        "UTF-8"
    );

    $pdf->Cell(
        10,
        8,
        $cliente["id_cliente"],
        1,
        0,
        "C"
    );

    $pdf->Cell(
        25,
        8,
        utf8_decode($cliente["cedula"]),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        35,
        8,
        utf8_decode($cliente["nombres"]),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        35,
        8,
        utf8_decode($cliente["apellidos"]),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        25,
        8,
        utf8_decode($cliente["telefono"]),
        1,
        0,
        "C"
    );

    $pdf->Cell(
        50,
        8,
        utf8_decode($correo),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        55,
        8,
        utf8_decode($direccion),
        1,
        0,
        "L"
    );

    $pdf->Cell(
        32,
        8,
        $fechaRegistro,
        1,
        1,
        "C"
    );
}

/*
|--------------------------------------------------------------------------
| Resumen
|--------------------------------------------------------------------------
*/

$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 10);

$pdf->Cell(
    0,
    8,
    utf8_decode(
        "Total de clientes registrados: " . $totalClientes
    ),
    0,
    1,
    "R"
);

/*
|--------------------------------------------------------------------------
| Mostrar PDF
|--------------------------------------------------------------------------
|
| I = abrir dentro del navegador
| D = descargar directamente
|
*/

$pdf->Output(
    "I",
    "reporte_clientes_" . date("Y-m-d") . ".pdf"
);

?>