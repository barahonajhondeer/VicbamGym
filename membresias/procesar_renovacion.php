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
    !$id_membresia ||
    $id_membresia <= 0 ||
    $tipo === "" ||
    $fecha_inicio === ""
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Los datos de renovación no son válidos."
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
   DEFINIR PRECIO Y DURACIÓN
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
                "El tipo de membresía seleccionado no es válido."
            )
        );

        exit();
}


/* =========================================
   FECHA LÍMITE DE PAGO
========================================= */

$fecha_limite_pago = $fecha_fin;


/* =========================================
   CONSULTAR MEMBRESÍA
========================================= */

$sqlMembresia = "
    SELECT
        m.id_membresia,
        m.id_cliente,
        m.estado,
        c.estado AS estado_cliente
    FROM membresias m
    INNER JOIN clientes c
        ON c.id_cliente = m.id_cliente
    WHERE m.id_membresia = ?
    LIMIT 1
";


$stmtMembresia = mysqli_prepare(
    $conexion,
    $sqlMembresia
);


if (!$stmtMembresia) {

    error_log(
        "Error preparando consulta de membresía para renovación: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo procesar la renovación."
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


$membresia =
    mysqli_fetch_assoc(
        $resultadoMembresia
    );


mysqli_stmt_close(
    $stmtMembresia
);


/* =========================================
   VALIDAR CLIENTE
========================================= */

if (
    $membresia["estado_cliente"] !== "Activo"
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede renovar la membresía porque el cliente está inactivo."
        )
    );

    exit();
}


$id_cliente =
    (int)
    $membresia["id_cliente"];


/* =========================================
   ACTUALIZAR MEMBRESÍAS VENCIDAS
========================================= */

$sqlVencidas = "
    UPDATE membresias
    SET estado = 'Vencida'
    WHERE id_cliente = ?
    AND id_membresia <> ?
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
        "ii",
        $id_cliente,
        $id_membresia
    );

    mysqli_stmt_execute(
        $stmtVencidas
    );

    mysqli_stmt_close(
        $stmtVencidas
    );
}


/* =========================================
   EVITAR OTRA MEMBRESÍA ACTIVA
========================================= */

$sqlActiva = "
    SELECT id_membresia
    FROM membresias
    WHERE id_cliente = ?
    AND id_membresia <> ?
    AND estado = 'Activa'
    AND fecha_fin >= CURDATE()
    LIMIT 1
";


$stmtActiva = mysqli_prepare(
    $conexion,
    $sqlActiva
);


if (!$stmtActiva) {

    error_log(
        "Error preparando validación de membresía activa: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar la renovación."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtActiva,
    "ii",
    $id_cliente,
    $id_membresia
);


mysqli_stmt_execute(
    $stmtActiva
);


$resultadoActiva =
    mysqli_stmt_get_result(
        $stmtActiva
    );


if (
    mysqli_num_rows(
        $resultadoActiva
    ) > 0
) {

    mysqli_stmt_close(
        $stmtActiva
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
    $stmtActiva
);


/* =========================================
   RENOVAR MEMBRESÍA
========================================= */

$sql = "
    UPDATE membresias
    SET
        tipo = ?,
        valor = ?,
        fecha_inicio = ?,
        fecha_fin = ?,
        fecha_limite_pago = ?,
        estado = 'Activa'
    WHERE id_membresia = ?
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando renovación de membresía: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo renovar la membresía."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "sdsssi",
    $tipo,
    $valor,
    $fecha_inicio,
    $fecha_fin,
    $fecha_limite_pago,
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
            "Membresía renovada correctamente."
        )
    );

    exit();

} else {

    error_log(
        "Error renovando membresía: " .
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
            "No se pudo renovar la membresía."
        )
    );

    exit();
}
?>