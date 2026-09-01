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
   VALIDAR CAMPOS
========================================= */

if (
    $usuario === "" ||
    $password === "" ||
    $rol === ""
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Complete todos los campos obligatorios."
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
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
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
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El nombre de usuario debe tener entre 4 y 50 caracteres."
        )
    );

    exit();
}


/* =========================================
   VALIDAR FORMATO DEL USUARIO

   Permite:
   letras
   números
   punto
   guion
   guion bajo
========================================= */

if (
    !preg_match(
        '/^[A-Za-z0-9._-]+$/',
        $usuario
    )
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario solo puede contener letras, números, punto, guion y guion bajo."
        )
    );

    exit();
}


/* =========================================
   VALIDAR CONTRASEÑA
========================================= */

$longitudPassword =
    strlen(
        $password
    );


if (
    $longitudPassword < 8 ||
    $longitudPassword > 100
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La contraseña debe tener entre 8 y 100 caracteres."
        )
    );

    exit();
}


/* =========================================
   VALIDAR USUARIO REPETIDO
========================================= */

$sqlValidar = "
    SELECT
        id_usuario
    FROM usuarios
    WHERE usuario = ?
    LIMIT 1
";


$stmtValidar = mysqli_prepare(
    $conexion,
    $sqlValidar
);


if (!$stmtValidar) {

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
    $stmtValidar,
    "s",
    $usuario
);


if (
    !mysqli_stmt_execute(
        $stmtValidar
    )
) {

    error_log(
        "Error ejecutando validación de usuario: " .
        mysqli_stmt_error($stmtValidar)
    );

    mysqli_stmt_close(
        $stmtValidar
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo validar el usuario."
        )
    );

    exit();
}


$resultadoValidar =
    mysqli_stmt_get_result(
        $stmtValidar
    );


$usuarioExistente =
    mysqli_fetch_assoc(
        $resultadoValidar
    );


mysqli_stmt_close(
    $stmtValidar
);


if ($usuarioExistente) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El nombre de usuario ya se encuentra registrado."
        )
    );

    exit();
}


/* =========================================
   GENERAR HASH DE CONTRASEÑA
========================================= */

$passwordHash =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );


if ($passwordHash === false) {

    error_log(
        "No se pudo generar el hash de contraseña para el usuario: " .
        $usuario
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo procesar la contraseña."
        )
    );

    exit();
}


/* =========================================
   INSERTAR USUARIO
========================================= */

$sqlInsertar = "
    INSERT INTO usuarios
    (
        usuario,
        password,
        rol,
        estado
    )
    VALUES
    (
        ?,
        ?,
        ?,
        'Activo'
    )
";


$stmtInsertar = mysqli_prepare(
    $conexion,
    $sqlInsertar
);


if (!$stmtInsertar) {

    error_log(
        "Error preparando registro de usuario: " .
        mysqli_error($conexion)
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el usuario."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtInsertar,
    "sss",
    $usuario,
    $passwordHash,
    $rol
);


/* =========================================
   EJECUTAR REGISTRO
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
        "Location: usuarios.php?tipo=exito&mensaje=" .
        urlencode(
            "Usuario registrado correctamente."
        )
    );

    exit();

} else {

    error_log(
        "Error registrando usuario: " .
        mysqli_stmt_error(
            $stmtInsertar
        )
    );

    mysqli_stmt_close(
        $stmtInsertar
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el usuario."
        )
    );

    exit();
}
?>