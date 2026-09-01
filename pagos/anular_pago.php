<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");

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

if (!isset($_SESSION["id_usuario"])) {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo identificar al usuario que realiza la anulación."
        )
    );

    exit();
}


$id_usuario =
    (int) $_SESSION["id_usuario"];


/* =========================================
   RECIBIR DATOS
========================================= */

$id_pago =
    (int) ($_POST["id_pago"] ?? 0);

$motivo =
    trim(
        $_POST["motivo_anulacion"] ?? ""
    );


/* =========================================
   VALIDACIONES
========================================= */

if ($id_pago <= 0) {

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
    strlen($motivo) < 3
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Debe ingresar un motivo válido."
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


$stmtBuscar =
    mysqli_prepare(
        $conexion,
        $sqlBuscar
    );


mysqli_stmt_bind_param(
    $stmtBuscar,
    "i",
    $id_pago
);


mysqli_stmt_execute(
    $stmtBuscar
);


$resultadoBuscar =
    mysqli_stmt_get_result(
        $stmtBuscar
    );


$pago =
    mysqli_fetch_assoc(
        $resultadoBuscar
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


/* =========================================
   ANULAR PAGO Y REGISTRAR AUDITORÍA
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


$stmtAnular =
    mysqli_prepare(
        $conexion,
        $sqlAnular
    );


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
    mysqli_stmt_execute(
        $stmtAnular
    )
) {

    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode(
            "Pago anulado correctamente."
        )
    );

} else {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo anular el pago."
        )
    );
}

exit();