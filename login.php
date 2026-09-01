<?php

session_start();

require_once("config/conexion.php");

/* =================================
   SOLO ACEPTAR POST
================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit();
}

/* =================================
   RECIBIR DATOS
================================= */

$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";

/* =================================
   VALIDAR CAMPOS
================================= */

if (
    $usuario === "" ||
    $password === ""
) {

    header(
        "Location: index.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Ingrese el usuario y la contraseña."
        )
    );

    exit();
}

/* =================================
   BUSCAR USUARIO
================================= */

$sql = "SELECT
            id_usuario,
            usuario,
            password,
            rol
        FROM usuarios
        WHERE usuario = ?
        LIMIT 1";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

if (!$stmt) {

    header(
        "Location: index.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo procesar el inicio de sesión."
        )
    );

    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $usuario
);

mysqli_stmt_execute($stmt);

$resultado =
    mysqli_stmt_get_result($stmt);

$datosUsuario =
    mysqli_fetch_assoc($resultado);

/* =================================
   VALIDAR CONTRASEÑA
================================= */

if (
    $datosUsuario &&
    password_verify(
        $password,
        $datosUsuario["password"]
    )
) {

    /* Evitar reutilizar el mismo ID de sesión */

    session_regenerate_id(true);

    /* Guardar datos en sesión */

    $_SESSION["id_usuario"] =
    $fila["id_usuario"];

        $_SESSION["nombre"] =
            $fila["nombre"];

        $_SESSION["usuario"] =
            $fila["usuario"];

        $_SESSION["rol"] =
            $fila["rol"];

    mysqli_stmt_close($stmt);

    /* =================================
       LOGIN CORRECTO
    ================================= */

    header(
        "Location: dashboard.php?tipo=exito&mensaje=" .
        urlencode(
            "Bienvenido al sistema, " .
            $datosUsuario["usuario"] .
            "."
        )
    );

    exit();

}

/* =================================
   LOGIN INCORRECTO
================================= */

mysqli_stmt_close($stmt);

header(
    "Location: index.php?tipo=error&mensaje=" .
    urlencode(
        "Usuario o contraseña incorrectos."
    )
);

exit();

?>