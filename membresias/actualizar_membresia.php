<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   SOLO POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
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

$id_membresia = filter_input(
    INPUT_POST,
    "id_membresia",
    FILTER_VALIDATE_INT
);

$id_cliente = filter_input(
    INPUT_POST,
    "id_cliente",
    FILTER_VALIDATE_INT
);

$tipo = trim(
    $_POST["tipo"] ?? ""
);

$fecha_inicio = trim(
    $_POST["fecha_inicio"] ?? ""
);


/* =========================================
   VALIDAR CAMPOS
========================================= */

if (
    !$id_membresia ||
    $id_membresia <= 0 ||
    !$id_cliente ||
    $id_cliente <= 0 ||
    $tipo === "" ||
    $fecha_inicio === ""
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Todos los campos son obligatorios."
        )
    );

    exit();
}


/* =========================================
   VALIDAR FECHA
========================================= */

$fechaObjeto = DateTime::createFromFormat(
    "Y-m-d",
    $fecha_inicio
);


if (
    !$fechaObjeto ||
    $fechaObjeto->format("Y-m-d") !== $fecha_inicio
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La fecha de inicio no es válida."
        )
    );

    exit();
}


/* =========================================
   NO PERMITIR FECHA FUTURA
========================================= */

$hoy = date("Y-m-d");


if ($fecha_inicio > $hoy) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La fecha de inicio no puede ser futura."
        )
    );

    exit();
}


/* =========================================
   DEFINIR VALOR Y DURACIÓN
========================================= */

switch ($tipo) {

    case "Mensual":

        $valor = 25.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime(
                $fecha_inicio . " +1 month"
            )
        );

        break;


    case "Trimestral":

        $valor = 65.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime(
                $fecha_inicio . " +3 months"
            )
        );

        break;


    case "Semestral":

        $valor = 120.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime(
                $fecha_inicio . " +6 months"
            )
        );

        break;


    case "Anual":

        $valor = 220.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime(
                $fecha_inicio . " +1 year"
            )
        );

        break;


    default:

        header(
            "Location: membresias.php?tipo=advertencia&mensaje=" .
            urlencode(
                "Tipo de membresía incorrecto."
            )
        );

        exit();
}


/* =========================================
   FECHA LÍMITE DE PAGO
========================================= */

$fecha_limite_pago = $fecha_fin;


/* =========================================
   ESTADO
========================================= */

if ($fecha_fin >= $hoy) {

    $estado = "Activa";

} else {

    $estado = "Vencida";
}


/* =========================================
   VERIFICAR QUE LA MEMBRESÍA EXISTA
========================================= */

$sqlMembresia = "
    SELECT
        id_membresia,
        id_cliente
    FROM membresias
    WHERE id_membresia = ?
    LIMIT 1
";


$stmtMembresia = mysqli_prepare(
    $conexion,
    $sqlMembresia
);


if (!$stmtMembresia) {

    error_log(
        "Error preparando consulta de membresía: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo procesar la membresía."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtMembresia,
    "i",
    $id_membresia
);


mysqli_stmt_execute(
    $stmtMembresia
);


$resultadoMembresia =
    mysqli_stmt_get_result(
        $stmtMembresia
    );


if (
    mysqli_num_rows(
        $resultadoMembresia
    ) === 0
) {

    mysqli_stmt_close(
        $stmtMembresia
    );

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía seleccionada no existe."
        )
    );

    exit();
}


$membresiaActual =
    mysqli_fetch_assoc(
        $resultadoMembresia
    );


mysqli_stmt_close(
    $stmtMembresia
);


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
        "Error preparando consulta de cliente: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
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


$resultadoCliente =
    mysqli_stmt_get_result(
        $stmtCliente
    );


if (
    mysqli_num_rows(
        $resultadoCliente
    ) === 0
) {

    mysqli_stmt_close(
        $stmtCliente
    );

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no existe."
        )
    );

    exit();
}


$cliente =
    mysqli_fetch_assoc(
        $resultadoCliente
    );


mysqli_stmt_close(
    $stmtCliente
);


/* =========================================
   CLIENTE INACTIVO
========================================= */

if (
    $cliente["estado"] !== "Activo" &&
    (int)$membresiaActual["id_cliente"] !== $id_cliente
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede asignar la membresía a un cliente inactivo."
        )
    );

    exit();
}


/* =========================================
   ACTUALIZAR MEMBRESÍAS VENCIDAS DEL CLIENTE
========================================= */

$sqlVencidas = "
    UPDATE membresias
    SET estado = 'Vencida'
    WHERE id_cliente = ?
    AND estado = 'Activa'
    AND fecha_fin < CURDATE()
";


$stmtVencidas = mysqli_prepare(
    $conexion,
    $sqlVencidas
);


if ($stmtVencidas) {

    mysqli_stmt_bind_param(
        $stmtVencidas,
        "i",
        $id_cliente
    );

    mysqli_stmt_execute(
        $stmtVencidas
    );

    mysqli_stmt_close(
        $stmtVencidas
    );
}


/* =========================================
   EVITAR DOS MEMBRESÍAS ACTIVAS
========================================= */

if ($estado === "Activa") {

    $sqlDuplicada = "
        SELECT id_membresia
        FROM membresias
        WHERE id_cliente = ?
        AND estado = 'Activa'
        AND fecha_fin >= CURDATE()
        AND id_membresia <> ?
        LIMIT 1
    ";


    $stmtDuplicada = mysqli_prepare(
        $conexion,
        $sqlDuplicada
    );


    if (!$stmtDuplicada) {

        error_log(
            "Error preparando validación de membresía activa: " .
            mysqli_error($conexion)
        );

        header(
            "Location: membresias.php?tipo=error&mensaje=" .
            urlencode(
                "No se pudo validar la membresía."
            )
        );

        exit();
    }


    mysqli_stmt_bind_param(
        $stmtDuplicada,
        "ii",
        $id_cliente,
        $id_membresia
    );


    mysqli_stmt_execute(
        $stmtDuplicada
    );


    $resultadoDuplicada =
        mysqli_stmt_get_result(
            $stmtDuplicada
        );


    if (
        mysqli_num_rows(
            $resultadoDuplicada
        ) > 0
    ) {

        mysqli_stmt_close(
            $stmtDuplicada
        );

        header(
            "Location: membresias.php?tipo=advertencia&mensaje=" .
            urlencode(
                "El cliente ya posee otra membresía activa."
            )
        );

        exit();
    }


    mysqli_stmt_close(
        $stmtDuplicada
    );
}


/* =========================================
   ACTUALIZAR MEMBRESÍA
========================================= */

$sql = "
    UPDATE membresias

    SET
        id_cliente = ?,
        tipo = ?,
        fecha_inicio = ?,
        fecha_fin = ?,
        fecha_limite_pago = ?,
        estado = ?,
        valor = ?

    WHERE id_membresia = ?
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando actualización de membresía: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo actualizar la membresía."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "isssssdi",
    $id_cliente,
    $tipo,
    $fecha_inicio,
    $fecha_fin,
    $fecha_limite_pago,
    $estado,
    $valor,
    $id_membresia
);


/* =========================================
   EJECUTAR
========================================= */

if (
    mysqli_stmt_execute(
        $stmt
    )
) {

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: membresias.php?tipo=exito&mensaje=" .
        urlencode(
            "Membresía actualizada correctamente."
        )
    );

    exit();

} else {

    error_log(
        "Error actualizando membresía: " .
        mysqli_stmt_error(
            $stmt
        )
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo actualizar la membresía."
        )
    );

    exit();
}
?>