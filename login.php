<?php

session_start();

require_once("config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$usuario = trim($_POST["usuario"] ?? "");
$password = trim($_POST["password"] ?? "");

if ($usuario === "" || $password === "") {

    header(
        "Location: index.php?tipo=advertencia&mensaje=" .
        urlencode("Ingrese el usuario y la contraseña.")
    );
    
    exit();
}

$sql = "SELECT
            id_usuario,
            usuario,
            password,
            rol
        FROM usuarios
        WHERE usuario = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $usuario
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$datosUsuario = mysqli_fetch_assoc($resultado);

if (
    $datosUsuario &&
    $password === $datosUsuario["password"]
) {

    $_SESSION["id_usuario"] =
        $datosUsuario["id_usuario"];

    $_SESSION["usuario"] =
        $datosUsuario["usuario"];

    $_SESSION["rol"] =
        $datosUsuario["rol"];

        header(
            "Location: dashboard.php?tipo=exito&mensaje=" .
            urlencode(
                "Bienvenido al sistema, " .
                $datosUsuario["usuario"] .
                "."
            )
        );
    
        exit();
} else {

    header(
        "Location: index.php?tipo=error&mensaje=" .
        urlencode("Usuario o contraseña incorrectos.")
    );
    
    exit();
}

?>