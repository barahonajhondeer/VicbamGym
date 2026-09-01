<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   RECIBIR DATOS
========================================= */

$id_cliente =
    (int) ($_POST["id_cliente"] ?? 0);

$id_membresia =
    (int) ($_POST["id_membresia"] ?? 0);

$valor =
    (float) ($_POST["valor"] ?? 0);

$metodo_pago =
    trim(
        $_POST["metodo_pago"] ?? ""
    );

$fecha_pago =
    trim(
        $_POST["fecha_pago"] ?? ""
    );


/* =========================================
   VALIDACIONES BÁSICAS
========================================= */

if (
    $id_cliente <= 0 ||
    $id_membresia <= 0 ||
    $valor <= 0 ||
    $metodo_pago === "" ||
    $fecha_pago === ""
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Complete correctamente todos los campos."
        )
    );

    exit();
}


/* =========================================
   VALIDAR MÉTODO
========================================= */

$metodosPermitidos = [
    "Efectivo",
    "Transferencia"
];


if (
    !in_array(
        $metodo_pago,
        $metodosPermitidos,
        true
    )
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Método de pago no válido."
        )
    );

    exit();
}


/* =========================================
   BUSCAR MEMBRESÍA
========================================= */

$sqlMembresia = "
    SELECT

        id_membresia,
        id_cliente,
        valor

    FROM membresias

    WHERE id_membresia = ?

    AND id_cliente = ?

    LIMIT 1
";


$stmtMembresia =
    mysqli_prepare(
        $conexion,
        $sqlMembresia
    );


mysqli_stmt_bind_param(
    $stmtMembresia,
    "ii",
    $id_membresia,
    $id_cliente
);


mysqli_stmt_execute(
    $stmtMembresia
);


$resultadoMembresia =
    mysqli_stmt_get_result(
        $stmtMembresia
    );


$membresia =
    mysqli_fetch_assoc(
        $resultadoMembresia
    );


if (!$membresia) {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "La membresía seleccionada no corresponde al cliente."
        )
    );

    exit();
}


/* =========================================
   CALCULAR TOTAL PAGADO

   NO CONTAR ANULADOS
========================================= */

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
    $id_membresia
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


$totalPagado =
    (float)
    $filaPagado["pagado"];


$valorMembresia =
    (float)
    $membresia["valor"];


$saldo =
    $valorMembresia -
    $totalPagado;


/* =========================================
   VALIDAR SALDO
========================================= */

if ($saldo <= 0) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Esta membresía ya se encuentra pagada completamente."
        )
    );

    exit();
}


if ($valor > $saldo) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El pago supera el saldo pendiente de $" .
            number_format(
                $saldo,
                2
            )
        )
    );

    exit();
}


/* =========================================
   REGISTRAR PAGO
========================================= */

$sqlInsertar = "
    INSERT INTO pagos
    (
        id_cliente,
        id_membresia,
        valor,
        metodo_pago,
        fecha_pago,
        estado
    )

    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        'Registrado'
    )
";


$stmtInsertar =
    mysqli_prepare(
        $conexion,
        $sqlInsertar
    );


mysqli_stmt_bind_param(
    $stmtInsertar,
    "iidss",
    $id_cliente,
    $id_membresia,
    $valor,
    $metodo_pago,
    $fecha_pago
);


if (
    mysqli_stmt_execute(
        $stmtInsertar
    )
) {

    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode(
            "Pago registrado correctamente."
        )
    );

} else {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el pago."
        )
    );
}

exit();