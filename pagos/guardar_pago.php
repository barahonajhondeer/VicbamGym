<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   SOLO POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode("Solicitud no válida.")
    );

    exit();
}


/* =========================================
   VALIDAR CSRF
========================================= */

verificar_csrf();


/* =========================================
   RECIBIR DATOS
========================================= */

$id_cliente = filter_input(
    INPUT_POST,
    "id_cliente",
    FILTER_VALIDATE_INT
);

$id_membresia = filter_input(
    INPUT_POST,
    "id_membresia",
    FILTER_VALIDATE_INT
);

$valor = filter_input(
    INPUT_POST,
    "valor",
    FILTER_VALIDATE_FLOAT
);

$metodo_pago = trim(
    $_POST["metodo_pago"] ?? ""
);

$fecha_pago = trim(
    $_POST["fecha_pago"] ?? ""
);


/* =========================================
   VALIDACIONES BÁSICAS
========================================= */

if (
    !$id_cliente ||
    $id_cliente <= 0 ||
    !$id_membresia ||
    $id_membresia <= 0 ||
    $valor === false ||
    $valor === null ||
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
   VALIDAR MÉTODO DE PAGO
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
   VALIDAR FECHA
========================================= */

$fechaObjeto = DateTime::createFromFormat(
    "Y-m-d",
    $fecha_pago
);


if (
    !$fechaObjeto ||
    $fechaObjeto->format("Y-m-d") !== $fecha_pago
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La fecha de pago no es válida."
        )
    );

    exit();
}


/* =========================================
   NO PERMITIR FECHAS FUTURAS
========================================= */

$hoy = date("Y-m-d");


if ($fecha_pago > $hoy) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La fecha del pago no puede ser futura."
        )
    );

    exit();
}


/* =========================================
   VERIFICAR CLIENTE
========================================= */

$sqlCliente = "
    SELECT
        id_cliente,
        estado
    FROM clientes
    WHERE id_cliente = ?
    LIMIT 1
";


$stmtCliente = mysqli_prepare(
    $conexion,
    $sqlCliente
);


if (!$stmtCliente) {

    error_log(
        "Error preparando consulta de cliente en pagos: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el cliente."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtCliente,
    "i",
    $id_cliente
);


mysqli_stmt_execute(
    $stmtCliente
);


$resultadoCliente = mysqli_stmt_get_result(
    $stmtCliente
);


$cliente = mysqli_fetch_assoc(
    $resultadoCliente
);


mysqli_stmt_close(
    $stmtCliente
);


if (!$cliente) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no existe."
        )
    );

    exit();
}


if ($cliente["estado"] !== "Activo") {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se pueden registrar pagos para un cliente inactivo."
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
        valor,
        estado,
        fecha_inicio,
        fecha_fin
    FROM membresias
    WHERE id_membresia = ?
    AND id_cliente = ?
    LIMIT 1
";


$stmtMembresia = mysqli_prepare(
    $conexion,
    $sqlMembresia
);


if (!$stmtMembresia) {

    error_log(
        "Error preparando consulta de membresía en pagos: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar la membresía."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtMembresia,
    "ii",
    $id_membresia,
    $id_cliente
);


mysqli_stmt_execute(
    $stmtMembresia
);


$resultadoMembresia = mysqli_stmt_get_result(
    $stmtMembresia
);


$membresia = mysqli_fetch_assoc(
    $resultadoMembresia
);


mysqli_stmt_close(
    $stmtMembresia
);


if (!$membresia) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía seleccionada no corresponde al cliente."
        )
    );

    exit();
}


/* =========================================
   VALIDAR ESTADO DE MEMBRESÍA
========================================= */

if ($membresia["estado"] !== "Activa") {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Solo se pueden registrar pagos en una membresía activa."
        )
    );

    exit();
}


/* =========================================
   VALIDAR VIGENCIA
========================================= */

if ($membresia["fecha_fin"] < $hoy) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía se encuentra vencida."
        )
    );

    exit();
}


/* =========================================
   CALCULAR TOTAL PAGADO
   NO CONTAR PAGOS ANULADOS
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


$stmtPagado = mysqli_prepare(
    $conexion,
    $sqlPagado
);


if (!$stmtPagado) {

    error_log(
        "Error preparando cálculo de pagos: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo calcular el saldo pendiente."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtPagado,
    "i",
    $id_membresia
);


mysqli_stmt_execute(
    $stmtPagado
);


$resultadoPagado = mysqli_stmt_get_result(
    $stmtPagado
);


$filaPagado = mysqli_fetch_assoc(
    $resultadoPagado
);


mysqli_stmt_close(
    $stmtPagado
);


$totalPagado = (float) (
    $filaPagado["pagado"] ?? 0
);


$valorMembresia = (float)
    $membresia["valor"];


$saldo =
    $valorMembresia -
    $totalPagado;


/* =========================================
   EVITAR PROBLEMAS DE DECIMALES
========================================= */

$saldo = round(
    $saldo,
    2
);

$valor = round(
    (float) $valor,
    2
);


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


$stmtInsertar = mysqli_prepare(
    $conexion,
    $sqlInsertar
);


if (!$stmtInsertar) {

    error_log(
        "Error preparando registro de pago: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el pago."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtInsertar,
    "iidss",
    $id_cliente,
    $id_membresia,
    $valor,
    $metodo_pago,
    $fecha_pago
);


/* =========================================
   EJECUTAR
========================================= */

if (
    mysqli_stmt_execute(
        $stmtInsertar
    )
) {

    mysqli_stmt_close(
        $stmtInsertar
    );

    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode(
            "Pago registrado correctamente."
        )
    );

    exit();

} else {

    error_log(
        "Error registrando pago: " .
        mysqli_stmt_error(
            $stmtInsertar
        )
    );

    mysqli_stmt_close(
        $stmtInsertar
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el pago."
        )
    );

    exit();
}
?>