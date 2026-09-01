<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../librerias/fpdf/fpdf.php");


/* =========================================
   RECIBIR FILTRO
========================================= */

$buscar = trim(
    $_GET["buscar"] ?? ""
);


/* =========================================
   LIMITAR BÚSQUEDA
========================================= */

if (
    mb_strlen($buscar) > 100
) {

    $buscar = mb_substr(
        $buscar,
        0,
        100
    );
}


/* =========================================
   CONSULTAR CLIENTES
========================================= */

$sql = "
    SELECT
        id_cliente,
        cedula,
        nombres,
        apellidos,
        telefono,
        correo,
        direccion,
        fecha_registro
    FROM clientes
    WHERE
        cedula LIKE ?
        OR nombres LIKE ?
        OR apellidos LIKE ?
        OR telefono LIKE ?
        OR correo LIKE ?
        OR direccion LIKE ?
    ORDER BY
        id_cliente ASC
";


$textoBuscar =
    "%" . $buscar . "%";


$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    error_log(
        "Error preparando PDF de clientes: " .
        mysqli_error($conexion)
    );

    header(
        "Location: reporte_cliente.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el PDF."
        )
    );

    exit();
}


/* =========================================
   ASIGNAR PARÁMETROS
========================================= */

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",

    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar
);


/* =========================================
   EJECUTAR CONSULTA
========================================= */

if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Error ejecutando PDF de clientes: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reporte_cliente.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el PDF."
        )
    );

    exit();
}


/* =========================================
   OBTENER RESULTADO
========================================= */

$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


if (!$resultado) {

    error_log(
        "No se pudo obtener el resultado del PDF de clientes."
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reporte_cliente.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo obtener la información."
        )
    );

    exit();
}


/* =========================================
   CREAR PDF
========================================= */

$pdf =
    new FPDF(
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
    utf8_decode(
        "REPORTE DE CLIENTES"
    ),
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
   MOSTRAR BÚSQUEDA SI EXISTE
========================================= */

if (
    $buscar !== ""
) {

    $pdf->SetFont(
        "Arial",
        "",
        9
    );


    $pdf->Cell(
        0,
        7,
        utf8_decode(
            "Filtro aplicado: " .
            $buscar
        ),
        0,
        1,
        "C"
    );
}


$pdf->Ln(5);


/* =========================================
   ENCABEZADOS
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
    10,
    9,
    "ID",
    1,
    0,
    "C",
    true
);


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
    35,
    9,
    "Nombres",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    35,
    9,
    "Apellidos",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    25,
    9,
    utf8_decode("Teléfono"),
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    50,
    9,
    "Correo",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    55,
    9,
    utf8_decode("Dirección"),
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    32,
    9,
    "Registro",
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
    8
);


$totalClientes = 0;


while (
    $cliente =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $totalClientes++;


    $fechaRegistro =
        "Sin fecha";


    if (
        !empty(
            $cliente["fecha_registro"]
        )
    ) {

        $fechaRegistro = date(
            "d/m/Y",
            strtotime(
                $cliente["fecha_registro"]
            )
        );
    }


    /* =========================================
       LIMITAR TEXTOS LARGOS
    ========================================= */

    $nombres = mb_strimwidth(
        $cliente["nombres"],
        0,
        24,
        "...",
        "UTF-8"
    );


    $apellidos = mb_strimwidth(
        $cliente["apellidos"],
        0,
        24,
        "...",
        "UTF-8"
    );


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


    /* ID */

    $pdf->Cell(
        10,
        8,
        (int)
        $cliente["id_cliente"],
        1,
        0,
        "C"
    );


    /* CÉDULA */

    $pdf->Cell(
        25,
        8,
        utf8_decode(
            $cliente["cedula"]
        ),
        1,
        0,
        "C"
    );


    /* NOMBRES */

    $pdf->Cell(
        35,
        8,
        utf8_decode(
            $nombres
        ),
        1,
        0,
        "L"
    );


    /* APELLIDOS */

    $pdf->Cell(
        35,
        8,
        utf8_decode(
            $apellidos
        ),
        1,
        0,
        "L"
    );


    /* TELÉFONO */

    $pdf->Cell(
        25,
        8,
        utf8_decode(
            $cliente["telefono"]
        ),
        1,
        0,
        "C"
    );


    /* CORREO */

    $pdf->Cell(
        50,
        8,
        utf8_decode(
            $correo
        ),
        1,
        0,
        "L"
    );


    /* DIRECCIÓN */

    $pdf->Cell(
        55,
        8,
        utf8_decode(
            $direccion
        ),
        1,
        0,
        "L"
    );


    /* FECHA REGISTRO */

    $pdf->Cell(
        32,
        8,
        $fechaRegistro,
        1,
        1,
        "C"
    );
}


/* =========================================
   SIN RESULTADOS
========================================= */

if (
    $totalClientes === 0
) {

    $pdf->SetFont(
        "Arial",
        "I",
        10
    );


    $pdf->Cell(
        267,
        12,
        utf8_decode(
            "No se encontraron clientes con el filtro seleccionado."
        ),
        1,
        1,
        "C"
    );
}


/* =========================================
   RESUMEN
========================================= */

$pdf->Ln(5);


$pdf->SetFont(
    "Arial",
    "B",
    10
);


$pdf->Cell(
    0,
    8,
    utf8_decode(
        "Total de clientes registrados: " .
        $totalClientes
    ),
    0,
    1,
    "R"
);


/* =========================================
   CERRAR CONSULTA
========================================= */

mysqli_stmt_close(
    $stmt
);


/* =========================================
   MOSTRAR PDF
========================================= */

$pdf->Output(
    "I",
    "reporte_clientes_" .
    date("Y-m-d") .
    ".pdf"
);

exit();

?>