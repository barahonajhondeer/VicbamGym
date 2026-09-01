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
   RECIBIR DATOS
========================================= */

$id_usuario = filter_input(
    INPUT_POST,
    "id_usuario",
    FILTER_VALIDATE_INT
);


$usuario = trim(
    $_POST["usuario"] ?? ""
);


$password =
    $_POST["password"] ?? "";


$rol = trim(
    $_POST["rol"] ?? ""
);


/* =========================================
   ROLES PERMITIDOS
========================================= */

$rolesPermitidos = [
    "Administrador",
    "Recepcionista"
];


/* =========================================
   VALIDACIONES GENERALES
========================================= */

if (
    !$id_usuario ||
    $id_usuario <= 0 ||
    $usuario === "" ||
    $rol === ""
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Los datos ingresados no son válidos."
        )
    );

    exit();
}


/* =========================================
   VALIDAR ROL
========================================= */

if (
    !in_array(
        $rol,
        $rolesPermitidos,
        true
    )
) {

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=advertencia&mensaje=" .
        urlencode(
            "El rol seleccionado no es válido."
        )
    );

    exit();
}


/* =========================================
   VALIDAR USUARIO
========================================= */

$longitudUsuario =
    mb_strlen(
        $usuario
    );


if (
    $longitudUsuario < 4 ||
    $longitudUsuario > 50
) {

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=advertencia&mensaje=" .
        urlencode(
            "El nombre de usuario debe tener entre 4 y 50 caracteres."
        )
    );

    exit();
}


/* =========================================
   FORMATO DEL USUARIO
========================================= */

if (
    !preg_match(
        '/^[A-Za-z0-9._-]+$/',
        $usuario
    )
) {

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario solo puede contener letras, números, punto, guion y guion bajo."
        )
    );

    exit();
}


/* =========================================
   VERIFICAR QUE EL USUARIO EXISTA
========================================= */

$sqlExiste = "
    SELECT
        id_usuario,
        usuario,
        rol,
        estado
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
";


$stmtExiste = mysqli_prepare(
    $conexion,
    $sqlExiste
);


if (!$stmtExiste) {

    error_log(
        "Error preparando validación de usuario: " .
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
    $stmtExiste,
    "i",
    $id_usuario
);


if (
    !mysqli_stmt_execute(
        $stmtExiste
    )
) {

    error_log(
        "Error ejecutando validación de usuario: " .
        mysqli_stmt_error(
            $stmtExiste
        )
    );

    mysqli_stmt_close(
        $stmtExiste
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el usuario."
        )
    );

    exit();
}


$resultadoExiste =
    mysqli_stmt_get_result(
        $stmtExiste
    );


$usuarioActual =
    mysqli_fetch_assoc(
        $resultadoExiste
    );


mysqli_stmt_close(
    $stmtExiste
);


if (!$usuarioActual) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario seleccionado no existe."
        )
    );

    exit();
}


/* =========================================
   EVITAR CAMBIAR SU PROPIO ROL
========================================= */

if (
    $id_usuario ===
    (int) $_SESSION["id_usuario"] &&
    $rol !== $usuarioActual["rol"]
) {

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=advertencia&mensaje=" .
        urlencode(
            "No puede modificar el rol de su propia sesión."
        )
    );

    exit();
}


/* =========================================
   VALIDAR USUARIO DUPLICADO
========================================= */

$sqlDuplicado = "
    SELECT
        id_usuario
    FROM usuarios
    WHERE usuario = ?
    AND id_usuario <> ?
    LIMIT 1
";


$stmtDuplicado = mysqli_prepare(
    $conexion,
    $sqlDuplicado
);


if (!$stmtDuplicado) {

    error_log(
        "Error preparando validación de usuario duplicado: " .
        mysqli_error($conexion)
    );

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el nombre de usuario."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtDuplicado,
    "si",
    $usuario,
    $id_usuario
);


if (
    !mysqli_stmt_execute(
        $stmtDuplicado
    )
) {

    error_log(
        "Error validando usuario duplicado: " .
        mysqli_stmt_error(
            $stmtDuplicado
        )
    );

    mysqli_stmt_close(
        $stmtDuplicado
    );

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el nombre de usuario."
        )
    );

    exit();
}


$resultadoDuplicado =
    mysqli_stmt_get_result(
        $stmtDuplicado
    );


$duplicado =
    mysqli_fetch_assoc(
        $resultadoDuplicado
    );


mysqli_stmt_close(
    $stmtDuplicado
);


if ($duplicado) {

    header(
        "Location: editar_usuario.php?id=" .
        $id_usuario .
        "&tipo=advertencia&mensaje=" .
        urlencode(
            "El nombre de usuario ya se encuentra registrado."
        )
    );

    exit();
}


/* =========================================
   SI SE INGRESÓ NUEVA CONTRASEÑA
========================================= */

if ($password !== "") {


    /* =====================================
       VALIDAR LONGITUD
    ====================================== */

    $longitudPassword =
        strlen(
            $password
        );


    if (
        $longitudPassword < 8 ||
        $longitudPassword > 100
    ) {

        header(
            "Location: editar_usuario.php?id=" .
            $id_usuario .
            "&tipo=advertencia&mensaje=" .
            urlencode(
                "La contraseña debe tener entre 8 y 100 caracteres."
            )
        );

        exit();
    }


    /* =====================================
       GENERAR HASH
    ====================================== */

    $passwordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    if ($passwordHash === false) {

        error_log(
            "No se pudo generar el hash para el usuario ID: " .
            $id_usuario
        );

        header(
            "Location: editar_usuario.php?id=" .
            $id_usuario .
            "&tipo=error&mensaje=" .
            urlencode(
                "No se pudo procesar la contraseña."
            )
        );

        exit();
    }


    /* =====================================
       UPDATE CON CONTRASEÑA
    ====================================== */

    $sqlActualizar = "
        UPDATE usuarios
        SET
            usuario = ?,
            password = ?,
            rol = ?
        WHERE id_usuario = ?
    ";


    $stmtActualizar = mysqli_prepare(
        $conexion,
        $sqlActualizar
    );


    if (!$stmtActualizar) {

        error_log(
            "Error preparando actualización de usuario: " .
            mysqli_error($conexion)
        );

        header(
            "Location: usuarios.php?tipo=error&mensaje=" .
            urlencode(
                "No se pudo actualizar el usuario."
            )
        );

        exit();
    }


    mysqli_stmt_bind_param(
        $stmtActualizar,
        "sssi",
        $usuario,
        $passwordHash,
        $rol,
        $id_usuario
    );

} else {


    /* =====================================
       CONSERVAR CONTRASEÑA ACTUAL
    ====================================== */

    $sqlActualizar = "
        UPDATE usuarios
        SET
            usuario = ?,
            rol = ?
        WHERE id_usuario = ?
    ";


    $stmtActualizar = mysqli_prepare(
        $conexion,
        $sqlActualizar
    );


    if (!$stmtActualizar) {

        error_log(
            "Error preparando actualización de usuario: " .
            mysqli_error($conexion)
        );

        header(
            "Location: usuarios.php?tipo=error&mensaje=" .
            urlencode(
                "No se pudo actualizar el usuario."
            )
        );

        exit();
    }


    mysqli_stmt_bind_param(
        $stmtActualizar,
        "ssi",
        $usuario,
        $rol,
        $id_usuario
    );
}


/* =========================================
   EJECUTAR ACTUALIZACIÓN
========================================= */

if (
    !mysqli_stmt_execute(
        $stmtActualizar
    )
) {

    error_log(
        "Error actualizando usuario: " .
        mysqli_stmt_error(
            $stmtActualizar
        )
    );

    mysqli_stmt_close(
        $stmtActualizar
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo actualizar el usuario."
        )
    );

    exit();
}


/* =========================================
   CERRAR CONSULTA
========================================= */

mysqli_stmt_close(
    $stmtActualizar
);


/* =========================================
   ACTUALIZAR DATOS DE SESIÓN
========================================= */

if (
    $id_usuario ===
    (int) $_SESSION["id_usuario"]
) {

    $_SESSION["usuario"] =
        $usuario;
}


/* =========================================
   ÉXITO
========================================= */

header(
    "Location: usuarios.php?tipo=exito&mensaje=" .
    urlencode(
        "Usuario actualizado correctamente."
    )
);

exit();
?>