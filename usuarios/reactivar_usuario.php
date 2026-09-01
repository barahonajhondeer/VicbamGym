<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   SOLO POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
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
   SOLO ADMINISTRADOR
========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header(
        "Location: ../dashboard.php?tipo=error&mensaje=" .
        urlencode(
            "No tiene permisos para realizar esta acción."
        )
    );

    exit();
}


/* =========================================
   VALIDAR ID
========================================= */

$id_usuario = filter_input(
    INPUT_POST,
    "id_usuario",
    FILTER_VALIDATE_INT
);


if (
    !$id_usuario ||
    $id_usuario <= 0
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Usuario no válido."
        )
    );

    exit();
}


/* =========================================
   BUSCAR USUARIO
========================================= */

$sqlUsuario = "
    SELECT
        id_usuario,
        usuario,
        estado
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
";


$stmtUsuario = mysqli_prepare(
    $conexion,
    $sqlUsuario
);


if (!$stmtUsuario) {

    error_log(
        "Error preparando consulta para reactivar usuario: " .
        mysqli_error($conexion)
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el usuario."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtUsuario,
    "i",
    $id_usuario
);


if (
    !mysqli_stmt_execute(
        $stmtUsuario
    )
) {

    error_log(
        "Error consultando usuario para reactivación: " .
        mysqli_stmt_error($stmtUsuario)
    );

    mysqli_stmt_close(
        $stmtUsuario
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el usuario."
        )
    );

    exit();
}


$resultadoUsuario =
    mysqli_stmt_get_result(
        $stmtUsuario
    );


$usuarioDatos =
    mysqli_fetch_assoc(
        $resultadoUsuario
    );


mysqli_stmt_close(
    $stmtUsuario
);


/* =========================================
   VALIDAR EXISTENCIA
========================================= */

if (!$usuarioDatos) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario seleccionado no existe."
        )
    );

    exit();
}


/* =========================================
   VERIFICAR SI YA ESTÁ ACTIVO
========================================= */

if (
    $usuarioDatos["estado"] === "Activo"
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario ya se encuentra activo."
        )
    );

    exit();
}


/* =========================================
   REACTIVAR USUARIO
========================================= */

$sqlReactivar = "
    UPDATE usuarios
    SET estado = 'Activo'
    WHERE id_usuario = ?
    AND estado = 'Inactivo'
";


$stmtReactivar = mysqli_prepare(
    $conexion,
    $sqlReactivar
);


if (!$stmtReactivar) {

    error_log(
        "Error preparando reactivación de usuario: " .
        mysqli_error($conexion)
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo reactivar el usuario."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtReactivar,
    "i",
    $id_usuario
);


if (
    !mysqli_stmt_execute(
        $stmtReactivar
    )
) {

    error_log(
        "Error reactivando usuario: " .
        mysqli_stmt_error($stmtReactivar)
    );

    mysqli_stmt_close(
        $stmtReactivar
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo reactivar el usuario."
        )
    );

    exit();
}


/* =========================================
   VALIDAR CAMBIO
========================================= */

if (
    mysqli_stmt_affected_rows(
        $stmtReactivar
    ) !== 1
) {

    mysqli_stmt_close(
        $stmtReactivar
    );

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario no pudo ser reactivado."
        )
    );

    exit();
}


mysqli_stmt_close(
    $stmtReactivar
);


/* =========================================
   ÉXITO
========================================= */

header(
    "Location: usuarios.php?tipo=exito&mensaje=" .
    urlencode(
        "Usuario reactivado correctamente."
    )
);

exit();
?>