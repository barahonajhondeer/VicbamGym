<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

/* =========================================
   CARGAR FPDF
========================================= */

/*
   Ajusta esta ruta únicamente si tu carpeta
   de FPDF está ubicada en otro lugar.
*/

require_once("../librerias/fpdf/fpdf.php");


/* =========================================
   RECIBIR FILTROS
========================================= */

$buscar =
    trim(
        $_GET["buscar"] ?? ""
    );

$fecha_inicio =
    trim(
        $_GET["fecha_inicio"] ?? ""
    );

$fecha_fin =
    trim(
        $_GET["fecha_fin"] ?? ""
    );

$metodo =
    trim(
        $_GET["metodo"] ?? ""
    );


/* =========================================
   VALIDAR MÉTODO
========================================= */

$metodosPermitidos = [
    "",
    "Efectivo",
    "Transferencia"
];

if (
    !in_array(
        $metodo,
        $metodosPermitidos,
        true
    )
) {

    $metodo = "";
}


/* =========================================
   CONSULTA BASE
========================================= */

$sql = "
    SELECT
        p.id_pago,
        p.fecha_pago,
        p.valor,
        p.metodo_pago,
        p.estado,

        c.cedula,
        c.nombres,
        c.apellidos,

        m.tipo

    FROM pagos p

    INNER JOIN clientes c
        ON c.id_cliente = p.id_cliente

    INNER JOIN membresias m
        ON m.id_membresia = p.id_membresia

    WHERE p.estado = 'Registrado'
";


/* =========================================
   PARÁMETROS
========================================= */

$tipos = "";

$parametros = [];


/* =========================================
   FILTRO BUSCADOR
========================================= */

if ($buscar !== "") {

    $sql .= "
        AND
        (
            c.cedula LIKE ?

            OR c.nombres LIKE ?

            OR c.apellidos LIKE ?

            OR CONCAT(
                c.nombres,
                ' ',
                c.apellidos
            ) LIKE ?

            OR m.tipo LIKE ?

            OR p.metodo_pago LIKE ?
        )
    ";


    $buscarLike =
        "%" . $buscar . "%";


    $tipos .=
        "ssssss";


    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;

    $parametros[] =
        $buscarLike;
}


/* =========================================
   FECHA INICIO
========================================= */

if ($fecha_inicio !== "") {

    $sql .= "
        AND p.fecha_pago >= ?
    ";

    $tipos .= "s";

    $parametros[] =
        $fecha_inicio;
}


/* =========================================
   FECHA FIN
========================================= */

if ($fecha_fin !== "") {

    $sql .= "
        AND p.fecha_pago <= ?
    ";

    $tipos .= "s";

    $parametros[] =
        $fecha_fin;
}


/* =========================================
   MÉTODO DE PAGO
========================================= */

if ($metodo !== "") {

    $sql .= "
        AND p.metodo_pago = ?
    ";

    $tipos .= "s";

    $parametros[] =
        $metodo;
}


/* =========================================
   ORDENAR
========================================= */

$sql .= "
    ORDER BY
        p.fecha_pago DESC,
        p.id_pago DESC
";


/* =========================================
   PREPARAR CONSULTA
========================================= */

$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    die(
        "Error al preparar el reporte: " .
        mysqli_error($conexion)
    );
}


/* =========================================
   ASIGNAR PARÁMETROS
========================================= */

if (
    count($parametros) > 0
) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$parametros
    );
}


/* =========================================
   EJECUTAR CONSULTA
========================================= */

mysqli_stmt_execute(
    $stmt
);


$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


/* =========================================
   GUARDAR DATOS
========================================= */

$pagos = [];

$totalIngresos = 0;


while (
    $fila =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $pagos[] =
        $fila;


    $totalIngresos +=
        (float)
        $fila["valor"];
}


/* =========================================
   CLASE PDF
========================================= */

class PDF extends FPDF
{

    function Header()
    {

        /* ================================
           TÍTULO
        ================================= */

        $this->SetFont(
            "Arial",
            "B",
            18
        );


        $this->Cell(
            0,
            10,
            "VICBAMGYM",
            0,
            1,
            "C"
        );


        $this->SetFont(
            "Arial",
            "B",
            13
        );


        $this->Cell(
            0,
            8,
            "REPORTE DE PAGOS",
            0,
            1,
            "C"
        );


        $this->Ln(3);


        /* ================================
           LÍNEA
        ================================= */

        $this->SetDrawColor(
            200,
            0,
            0
        );


        $this->SetLineWidth(
            0.8
        );


        $this->Line(
            10,
            $this->GetY(),
            200,
            $this->GetY()
        );


        $this->Ln(7);

    }


    function Footer()
    {

        $this->SetY(
            -15
        );


        $this->SetFont(
            "Arial",
            "I",
            8
        );


        $this->SetTextColor(
            100,
            100,
            100
        );


        $this->Cell(
            0,
            10,
            "Pagina " .
            $this->PageNo(),
            0,
            0,
            "C"
        );

    }

}


/* =========================================
   CREAR PDF
========================================= */

$pdf =
    new PDF(
        "P",
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
    18
);


$pdf->AddPage();


/* =========================================
   INFORMACIÓN DEL REPORTE
========================================= */

$pdf->SetFont(
    "Arial",
    "",
    9
);


$pdf->SetTextColor(
    0,
    0,
    0
);


$pdf->Cell(
    0,
    6,
    "Fecha de generacion: " .
    date(
        "d/m/Y H:i"
    ),
    0,
    1
);


/* =========================================
   MOSTRAR FILTROS
========================================= */

if ($buscar !== "") {

    $pdf->Cell(
        0,
        6,
        "Busqueda: " .
        utf8_decode(
            $buscar
        ),
        0,
        1
    );
}


if (
    $fecha_inicio !== ""
) {

    $pdf->Cell(
        0,
        6,
        "Fecha inicial: " .
        date(
            "d/m/Y",
            strtotime(
                $fecha_inicio
            )
        ),
        0,
        1
    );
}


if (
    $fecha_fin !== ""
) {

    $pdf->Cell(
        0,
        6,
        "Fecha final: " .
        date(
            "d/m/Y",
            strtotime(
                $fecha_fin
            )
        ),
        0,
        1
    );
}


if ($metodo !== "") {

    $pdf->Cell(
        0,
        6,
        "Metodo: " .
        utf8_decode(
            $metodo
        ),
        0,
        1
    );
}


$pdf->Ln(5);


/* =========================================
   CABECERA TABLA
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


$pdf->SetFont(
    "Arial",
    "B",
    8
);


$pdf->Cell(
    13,
    8,
    "ID",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    27,
    8,
    "Cedula",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    50,
    8,
    "Cliente",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    25,
    8,
    "Membresia",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    27,
    8,
    "Metodo",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    25,
    8,
    "Fecha",
    1,
    0,
    "C",
    true
);


$pdf->Cell(
    23,
    8,
    "Valor",
    1,
    1,
    "C",
    true
);


/* =========================================
   FILAS
========================================= */

$pdf->SetTextColor(
    0,
    0,
    0
);


$pdf->SetFont(
    "Arial",
    "",
    7.5
);


if (
    count($pagos) === 0
) {

    $pdf->Cell(
        190,
        10,
        "No se encontraron pagos registrados con los filtros seleccionados.",
        1,
        1,
        "C"
    );

} else {

    foreach (
        $pagos as $fila
    ) {

        $nombreCompleto =
            $fila["nombres"] .
            " " .
            $fila["apellidos"];


        /*
           Limitamos el nombre para
           evitar que desborde la tabla.
        */

        if (
            strlen(
                $nombreCompleto
            ) > 28
        ) {

            $nombreCompleto =
                substr(
                    $nombreCompleto,
                    0,
                    25
                ) .
                "...";
        }


        $pdf->Cell(
            13,
            8,
            $fila["id_pago"],
            1,
            0,
            "C"
        );


        $pdf->Cell(
            27,
            8,
            $fila["cedula"],
            1,
            0,
            "C"
        );


        $pdf->Cell(
            50,
            8,
            utf8_decode(
                $nombreCompleto
            ),
            1,
            0,
            "L"
        );


        $pdf->Cell(
            25,
            8,
            utf8_decode(
                $fila["tipo"]
            ),
            1,
            0,
            "C"
        );


        $pdf->Cell(
            27,
            8,
            utf8_decode(
                $fila[
                    "metodo_pago"
                ]
            ),
            1,
            0,
            "C"
        );


        $pdf->Cell(
            25,
            8,
            date(
                "d/m/Y",
                strtotime(
                    $fila[
                        "fecha_pago"
                    ]
                )
            ),
            1,
            0,
            "C"
        );


        $pdf->Cell(
            23,
            8,
            "$" .
            number_format(
                (float)
                $fila["valor"],
                2
            ),
            1,
            1,
            "R"
        );

    }

}


/* =========================================
   TOTAL
========================================= */

$pdf->Ln(5);


$pdf->SetFont(
    "Arial",
    "B",
    11
);


$pdf->SetTextColor(
    0,
    0,
    0
);


$pdf->Cell(
    140,
    9,
    "TOTAL DE INGRESOS:",
    0,
    0,
    "R"
);


$pdf->SetFillColor(
    230,
    230,
    230
);


$pdf->Cell(
    50,
    9,
    "$" .
    number_format(
        $totalIngresos,
        2
    ),
    1,
    1,
    "R",
    true
);


/* =========================================
   NOTA
========================================= */

$pdf->Ln(5);


$pdf->SetFont(
    "Arial",
    "I",
    8
);


$pdf->SetTextColor(
    90,
    90,
    90
);


$pdf->MultiCell(
    0,
    5,
    utf8_decode(
        "Nota: Los pagos anulados no se incluyen en este reporte ni en el total de ingresos."
    )
);


/* =========================================
   GENERAR PDF
========================================= */

$pdf->Output(
    "I",
    "Reporte_Pagos_VICBAMGYM.pdf"
);


mysqli_stmt_close(
    $stmt
);

exit();

?>