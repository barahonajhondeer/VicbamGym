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
        "Location: clientes.php?tipo=error&mensaje=" .
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
        "Location: clientes.php?tipo=advertencia&mensaje=" .
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

$id_cliente =
    filter_input(
        INPUT_POST,
        "id_cliente",
        FILTER_VALIDATE_INT
    );


if (
    !$id_cliente ||
    $id_cliente <= 0
) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no es válido."
        )
    );

    exit();
}


/* =========================================
   COMPROBAR QUE EL CLIENTE EXISTA
========================================= */

$sqlExiste = "
    SELECT
        id_cliente,
        estado
    FROM clientes
    WHERE id_cliente = ?
    LIMIT 1
";


$stmtExiste =
    mysqli_prepare(
        $conexion,
        $sqlExiste
    );


if (!$stmtExiste) {

    error_log(
        "Error preparando consulta de cliente: " .
        mysqli_error($conexion)
    );

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo procesar la solicitud."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtExiste,
    "i",
    $id_cliente
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
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no existe."
        )
    );

    exit();
}


$cliente =
    mysqli_fetch_assoc(
        $resultadoExiste
    );


mysqli_stmt_close(
    $stmtExiste
);


/* =========================================
   COMPROBAR SI YA ESTÁ INACTIVO
========================================= */

if (
    $cliente["estado"] === "Inactivo"
) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente ya se encuentra inactivo."
        )
    );

    exit();
}


/* =========================================
   DESACTIVAR CLIENTE
========================================= */

$sql = "
    UPDATE clientes
    SET estado = 'Inactivo'
    WHERE id_cliente = ?
    AND estado = 'Activo'
";


$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    error_log(
        "Error preparando desactivación de cliente: " .
        mysqli_error($conexion)
    );

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo desactivar el cliente."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
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
        "Location: clientes.php?tipo=exito&mensaje=" .
        urlencode(
            "Cliente desactivado correctamente. Su historial se conserva."
        )
    );

} else {

    error_log(
        "Error desactivando cliente: " .
        mysqli_stmt_error(
            $stmt
        )
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo desactivar el cliente."
        )
    );
}

exit();