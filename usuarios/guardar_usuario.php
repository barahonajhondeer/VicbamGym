<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {

    header(
        "Location: ../dashboard.php?tipo=error&mensaje=" .
        urlencode("No tiene permisos para realizar esta acción.")
    );

    exit();
}

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$rol = trim($_POST["rol"] ?? "");

$rolesPermitidos = [
    "Administrador",
    "Recepcionista"
];

/* VALIDAR CAMPOS */

if (
    $usuario === "" ||
    $password === "" ||
    !in_array($rol, $rolesPermitidos, true)
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("Complete correctamente todos los campos.")
    );

    exit();
}

/* VALIDAR LONGITUD DE CONTRASEÑA */

if (strlen($password) < 6) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La contraseña debe tener al menos 6 caracteres."
        )
    );

    exit();
}

/* VALIDAR USUARIO REPETIDO */

$sqlValidar = "SELECT id_usuario
               FROM usuarios
               WHERE usuario = ?
               LIMIT 1";

$stmtValidar = mysqli_prepare(
    $conexion,
    $sqlValidar
);

mysqli_stmt_bind_param(
    $stmtValidar,
    "s",
    $usuario
);

mysqli_stmt_execute($stmtValidar);

$resultadoValidar =
    mysqli_stmt_get_result($stmtValidar);

if (mysqli_num_rows($resultadoValidar) > 0) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El nombre de usuario ya se encuentra registrado."
        )
    );

    exit();
}

/* GENERAR HASH */

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

/* INSERTAR USUARIO */

$sql = "INSERT INTO usuarios
        (
            usuario,
            password,
            rol
        )
        VALUES
        (
            ?,
            ?,
            ?
        )";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $usuario,
    $passwordHash,
    $rol
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: usuarios.php?tipo=exito&mensaje=" .
        urlencode(
            "Usuario registrado correctamente."
        )
    );

    exit();

} else {

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo registrar el usuario."
        )
    );

    exit();
}

?>