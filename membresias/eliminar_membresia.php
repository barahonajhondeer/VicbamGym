<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   SOLO ADMINISTRADOR
========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No tiene permisos para realizar esta acción."
        )
    );

    exit();
}


/* =========================================
   SOLO POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Solicitud no válida."
        )
    );

    exit();
}


/* =========================================
   VALIDAR CSRF
========================================= */

verificar_csrf();


/* =========================================
   RECIBIR ID
========================================= */

$id_membresia = filter_input(
    INPUT_POST,
    "id_membresia",
    FILTER_VALIDATE_INT
);


if (
    !$id_membresia ||
    $id_membresia <= 0
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Membresía no válida."
        )
    );

    exit();
}


/* =========================================
   COMPROBAR QUE EXISTA
========================================= */

$sqlExiste = "
    SELECT
        id_membresia,
        estado
    FROM membresias
    WHERE id_membresia = ?
    LIMIT 1
";


$stmtExiste = mysqli_prepare(
    $conexion,
    $sqlExiste
);


if (!$stmtExiste) {

    error_log(
        "Error preparando consulta de membresía: " .
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
    $stmtExiste,
    "i",
    $id_membresia
);


mysqli_stmt_execute(
    $stmtExiste
);


$resultadoExiste =
    mysqli_stmt_get_result(
        $stmtExiste
    );


if (
    mysqli_num_rows(
        $resultadoExiste
    ) === 0
) {

    mysqli_stmt_close(
        $stmtExiste
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
        $resultadoExiste
    );


mysqli_stmt_close(
    $stmtExiste
);


/* =========================================
   SI YA ESTÁ VENCIDA
========================================= */

if (
    $membresia["estado"] === "Vencida"
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía ya se encuentra vencida."
        )
    );

    exit();
}


/* =========================================
   VERIFICAR PAGOS REGISTRADOS
========================================= */

$sqlPagos = "
    SELECT COUNT(*) AS total
    FROM pagos
    WHERE id_membresia = ?
";


$stmtPagos = mysqli_prepare(
    $conexion,
    $sqlPagos
);


if (!$stmtPagos) {

    error_log(
        "Error preparando consulta de pagos: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo comprobar el historial de pagos."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtPagos,
    "i",
    $id_membresia
);


mysqli_stmt_execute(
    $stmtPagos
);


$resultadoPagos =
    mysqli_stmt_get_result(
        $stmtPagos
    );


$datosPagos =
    mysqli_fetch_assoc(
        $resultadoPagos
    );


mysqli_stmt_close(
    $stmtPagos
);


/* =========================================
   MARCAR COMO VENCIDA
========================================= */

$sql = "
    UPDATE membresias
    SET estado = 'Vencida'
    WHERE id_membresia = ?
    AND estado = 'Activa'
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando cambio de estado de membresía: " .
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
    "i",
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

    if (
        (int)
        $datosPagos["total"] > 0
    ) {

        $mensaje =
            "Membresía desactivada correctamente. " .
            "Su historial de pagos se conserva.";

    } else {

        $mensaje =
            "Membresía desactivada correctamente.";
    }


    header(
        "Location: membresias.php?tipo=exito&mensaje=" .
        urlencode(
            $mensaje
        )
    );

    exit();

} else {

    error_log(
        "Error desactivando membresía: " .
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
            "No se pudo desactivar la membresía."
        )
    );

    exit();
}
?>