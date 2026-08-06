<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: ../dashboard.php");
    exit();
}

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$rol = trim($_POST["rol"] ?? "");

$rolesPermitidos = [
    "Administrador",
    "Recepcionista"
];

if (
    $usuario === "" ||
    strlen($password) < 6 ||
    !in_array($rol, $rolesPermitidos, true)
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("Complete correctamente todos los campos.")
        );
        
        exit();
}

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

$resultadoValidar = mysqli_stmt_get_result(
    $stmtValidar
);

if (mysqli_num_rows($resultadoValidar) > 0) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("El usuario ya existe.")
        );
        
        exit();
}

/*
Por ahora se guarda en texto simple para mantener
compatibilidad con el login actual.
Después lo cambiaremos a password_hash().
*/

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

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $usuario,
    $password,
    $rol
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: usuarios.php?tipo=exito&mensaje=" .
        urlencode("Usuario registrado correctamente.")
        );
        
        exit();
} else {

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode("No se pudo registrar el usuario.")
        );
        
        exit();
}

?>