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
   SOLO ADMINISTRADOR
========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No tiene permisos para anular pagos."
        )
    );

    exit();
}


/* =========================================
   VALIDAR USUARIO EN SESIÓN
========================================= */

$id_usuario = filter_var(
    $_SESSION["id_usuario"] ?? null,
    FILTER_VALIDATE_INT
);


if (
    !$id_usuario ||
    $id_usuario <= 0
) {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo identificar al usuario que realiza la anulación."
        )
    );

    exit();
}


/* =========================================
   RECIBIR DATOS
========================================= */

$id_pago = filter_input(
    INPUT_POST,
    "id_pago",
    FILTER_VALIDATE_INT
);


$motivo = trim(
    $_POST["motivo_anulacion"] ?? ""
);


/* =========================================
   VALIDACIONES
========================================= */

if (
    !$id_pago ||
    $id_pago <= 0
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Pago no válido."
        )
    );

    exit();
}


if (
    $motivo === "" ||
    mb_strlen($motivo) < 3
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Debe ingresar un motivo válido."
        )
    );

    exit();
}


if (
    mb_strlen($motivo) > 255
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El motivo de anulación es demasiado largo."
        )
    );

    exit();
}


/* =========================================
   BUSCAR PAGO
========================================= */

$sqlBuscar = "
    SELECT
        id_pago,
        estado
    FROM pagos
    WHERE id_pago = ?
    LIMIT 1
";


$stmtBuscar = mysqli_prepare(
    $conexion,
    $sqlBuscar
);


if (!$stmtBuscar) {

    error_log(
        "Error preparando consulta de pago para anulación: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el pago."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtBuscar,
    "i",
    $id_pago
);


if (
    !mysqli_stmt_execute(
        $stmtBuscar
    )
) {

    error_log(
        "Error consultando pago para anulación: " .
        mysqli_stmt_error($stmtBuscar)
    );

    mysqli_stmt_close(
        $stmtBuscar
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el pago."
        )
    );

    exit();
}


$resultadoBuscar = mysqli_stmt_get_result(
    $stmtBuscar
);


$pago = mysqli_fetch_assoc(
    $resultadoBuscar
);


mysqli_stmt_close(
    $stmtBuscar
);


if (!$pago) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El pago seleccionado no existe."
        )
    );

    exit();
}


/* =========================================
   EVITAR DOBLE ANULACIÓN
========================================= */

if (
    $pago["estado"] === "Anulado"
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Este pago ya se encuentra anulado."
        )
    );

    exit();
}


if (
    $pago["estado"] !== "Registrado"
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El pago no se encuentra en un estado válido para ser anulado."
        )
    );

    exit();
}


/* =========================================
   ANULAR PAGO
========================================= */

$sqlAnular = "
    UPDATE pagos
    SET
        estado = 'Anulado',
        motivo_anulacion = ?,
        anulado_por = ?,
        fecha_anulacion = NOW()
    WHERE id_pago = ?
    AND estado = 'Registrado'
";


$stmtAnular = mysqli_prepare(
    $conexion,
    $sqlAnular
);


if (!$stmtAnular) {

    error_log(
        "Error preparando anulación de pago: " .
        mysqli_error($conexion)
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo anular el pago."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtAnular,
    "sii",
    $motivo,
    $id_usuario,
    $id_pago
);


/* =========================================
   EJECUTAR
========================================= */

if (
    !mysqli_stmt_execute(
        $stmtAnular
    )
) {

    error_log(
        "Error anulando pago: " .
        mysqli_stmt_error($stmtAnular)
    );

    mysqli_stmt_close(
        $stmtAnular
    );

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo anular el pago."
        )
    );

    exit();
}


/* =========================================
   COMPROBAR QUE REALMENTE CAMBIÓ
========================================= */

$filasAfectadas =
    mysqli_stmt_affected_rows(
        $stmtAnular
    );


mysqli_stmt_close(
    $stmtAnular
);


if ($filasAfectadas !== 1) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El pago no pudo ser anulado porque su estado cambió."
        )
    );

    exit();
}


/* =========================================
   ÉXITO
========================================= */

header(
    "Location: pagos.php?tipo=exito&mensaje=" .
    urlencode(
        "Pago anulado correctamente."
    )
);

exit();
?>