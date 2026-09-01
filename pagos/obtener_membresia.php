<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

header(
    "Content-Type: application/json; charset=UTF-8"
);


/* =========================================
   RECIBIR CLIENTE
========================================= */

$id_cliente =
    (int) ($_GET["id_cliente"] ?? 0);


if ($id_cliente <= 0) {

    echo json_encode([]);
    exit();
}


/* =========================================
   OBTENER MEMBRESÍAS DEL CLIENTE
========================================= */

$sql = "
    SELECT

        m.id_membresia,
        m.tipo,
        m.fecha_inicio,
        m.fecha_fin,
        m.valor,
        m.estado

    FROM membresias m

    INNER JOIN clientes c
        ON c.id_cliente = m.id_cliente

    WHERE
        m.id_cliente = ?

    AND
        c.estado = 'Activo'

    ORDER BY
        m.fecha_inicio DESC
";


$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
);


mysqli_stmt_execute(
    $stmt
);


$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


$membresias = [];


/* =========================================
   CALCULAR PAGADO Y SALDO
========================================= */

while (
    $membresia =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $idMembresia =
        (int)
        $membresia["id_membresia"];


    /*
       IMPORTANTE:
       SOLO SUMAR PAGOS REGISTRADOS
    */

    $sqlPagado = "
        SELECT

            COALESCE(
                SUM(valor),
                0
            ) AS pagado

        FROM pagos

        WHERE id_membresia = ?

        AND estado = 'Registrado'
    ";


    $stmtPagado =
        mysqli_prepare(
            $conexion,
            $sqlPagado
        );


    mysqli_stmt_bind_param(
        $stmtPagado,
        "i",
        $idMembresia
    );


    mysqli_stmt_execute(
        $stmtPagado
    );


    $resultadoPagado =
        mysqli_stmt_get_result(
            $stmtPagado
        );


    $filaPagado =
        mysqli_fetch_assoc(
            $resultadoPagado
        );


    $pagado =
        (float)
        $filaPagado["pagado"];


    $valorMembresia =
        (float)
        $membresia["valor"];


    $saldo =
        $valorMembresia -
        $pagado;


    if ($saldo < 0) {
        $saldo = 0;
    }


    $membresias[] = [

        "id_membresia" =>
            $idMembresia,

        "tipo" =>
            $membresia["tipo"],

        "fecha_inicio" =>
            $membresia["fecha_inicio"],

        "fecha_fin" =>
            $membresia["fecha_fin"],

        "valor" =>
            $valorMembresia,

        "pagado" =>
            $pagado,

        "saldo" =>
            $saldo,

        "estado" =>
            $membresia["estado"]

    ];


    mysqli_stmt_close(
        $stmtPagado
    );
}


echo json_encode(
    $membresias,
    JSON_UNESCAPED_UNICODE
);