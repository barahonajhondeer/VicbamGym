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
   VALIDACIONES BÁSICAS
========================================= */

if (
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
   EVITAR FECHAS FUTURAS
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
   DEFINIR ESTADO
========================================= */

if ($fecha_fin >= $hoy) {

    $estado = "Activa";

} else {

    $estado = "Vencida";
}


/* =========================================
   COMPROBAR QUE EL CLIENTE EXISTA
   Y ESTÉ ACTIVO
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
            "No se pudo procesar la solicitud."
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
   CLIENTE DEBE ESTAR ACTIVO
========================================= */

if (
    $cliente["estado"] !== "Activo"
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede registrar una membresía a un cliente inactivo."
        )
    );

    exit();
}


/* =========================================
   ACTUALIZAR MEMBRESÍAS VENCIDAS
========================================= */

$sqlActualizar = "
    UPDATE membresias
    SET estado = 'Vencida'
    WHERE id_cliente = ?
    AND estado = 'Activa'
    AND fecha_fin < CURDATE()
";


$stmtActualizar = mysqli_prepare(
    $conexion,
    $sqlActualizar
);


if (!$stmtActualizar) {

    error_log(
        "Error preparando actualización de membresías: " .
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
    $stmtActualizar,
    "i",
    $id_cliente
);


mysqli_stmt_execute(
    $stmtActualizar
);


mysqli_stmt_close(
    $stmtActualizar
);


/* =========================================
   VALIDAR QUE NO TENGA
   OTRA MEMBRESÍA ACTIVA
========================================= */

$sqlValidar = "
    SELECT id_membresia
    FROM membresias
    WHERE id_cliente = ?
    AND estado = 'Activa'
    AND fecha_fin >= CURDATE()
    LIMIT 1
";


$stmtValidar = mysqli_prepare(
    $conexion,
    $sqlValidar
);


if (!$stmtValidar) {

    error_log(
        "Error preparando validación de membresía: " .
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
    $stmtValidar,
    "i",
    $id_cliente
);


mysqli_stmt_execute(
    $stmtValidar
);


$resultadoValidar =
    mysqli_stmt_get_result(
        $stmtValidar
    );


if (
    mysqli_num_rows(
        $resultadoValidar
    ) > 0
) {

    mysqli_stmt_close(
        $stmtValidar
    );

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Este cliente ya posee una membresía activa."
        )
    );

    exit();
}


mysqli_stmt_close(
    $stmtValidar
);


/* =========================================
   INSERTAR MEMBRESÍA
========================================= */

$sql = "
    INSERT INTO membresias
    (
        id_cliente,
        tipo,
        fecha_inicio,
        fecha_fin,
        fecha_limite_pago,
        estado,
        valor
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando registro de membresía: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar la membresía."
        )
    );

    exit();
}


/* =========================================
   ASIGNAR PARÁMETROS
========================================= */

mysqli_stmt_bind_param(
    $stmt,
    "isssssd",
    $id_cliente,
    $tipo,
    $fecha_inicio,
    $fecha_fin,
    $fecha_limite_pago,
    $estado,
    $valor
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
            "Membresía registrada correctamente."
        )
    );

    exit();

} else {

    error_log(
        "Error registrando membresía: " .
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
            "No se pudo registrar la membresía."
        )
    );

    exit();
}
?>