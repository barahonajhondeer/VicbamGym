<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   RESPUESTA JSON
========================================= */

header(
    "Content-Type: application/json; charset=UTF-8"
);


/* =========================================
   SOLO GET
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "error" => "Método no permitido."
    ]);

    exit();
}


/* =========================================
   RECIBIR CLIENTE
========================================= */

$id_cliente = filter_input(
    INPUT_GET,
    "id_cliente",
    FILTER_VALIDATE_INT
);


if (
    !$id_cliente ||
    $id_cliente <= 0
) {

    echo json_encode(
        [],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}


/* =========================================
   OBTENER MEMBRESÍAS ACTIVAS
   CON PAGADO Y SALDO
========================================= */

$sql = "
    SELECT

        m.id_membresia,
        m.tipo,
        m.fecha_inicio,
        m.fecha_fin,
        m.valor,
        m.estado,

        COALESCE(
            SUM(
                CASE
                    WHEN p.estado = 'Registrado'
                    THEN p.valor
                    ELSE 0
                END
            ),
            0
        ) AS pagado

    FROM membresias m

    INNER JOIN clientes c
        ON c.id_cliente = m.id_cliente

    LEFT JOIN pagos p
        ON p.id_membresia = m.id_membresia

    WHERE
        m.id_cliente = ?

    AND
        c.estado = 'Activo'

    AND
        m.estado = 'Activa'

    AND
        m.fecha_fin >= CURDATE()

    GROUP BY

        m.id_membresia,
        m.tipo,
        m.fecha_inicio,
        m.fecha_fin,
        m.valor,
        m.estado

    ORDER BY
        m.fecha_inicio DESC
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando consulta de membresías para pagos: " .
        mysqli_error($conexion)
    );

    http_response_code(500);

    echo json_encode([
        "error" =>
            "No se pudo obtener la información."
    ]);

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
);


if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Error ejecutando consulta de membresías para pagos: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    http_response_code(500);

    echo json_encode([
        "error" =>
            "No se pudo obtener la información."
    ]);

    exit();
}


$resultado = mysqli_stmt_get_result(
    $stmt
);


$membresias = [];


/* =========================================
   PREPARAR RESPUESTA
========================================= */

while (
    $membresia =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $valorMembresia = round(
        (float)
        $membresia["valor"],
        2
    );


    $pagado = round(
        (float)
        $membresia["pagado"],
        2
    );


    $saldo = round(
        $valorMembresia -
        $pagado,
        2
    );


    if ($saldo < 0) {

        $saldo = 0;
    }


    $membresias[] = [

        "id_membresia" =>
            (int)
            $membresia["id_membresia"],

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
}


/* =========================================
   CERRAR CONSULTA
========================================= */

mysqli_stmt_close(
    $stmt
);


/* =========================================
   DEVOLVER JSON
========================================= */

echo json_encode(
    $membresias,
    JSON_UNESCAPED_UNICODE |
    JSON_PRESERVE_ZERO_FRACTION
);

exit();
?>