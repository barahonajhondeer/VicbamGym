<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: usuarios.php");
    exit();
}

$id_usuario = (int) ($_POST["id_usuario"] ?? 0);
$usuario = trim($_POST["usuario"] ?? "");
$password = $_POST["password"] ?? "";
$rol = trim($_POST["rol"] ?? "");

$rolesPermitidos = [
    "Administrador",
    "Recepcionista"
];

if (
    $id_usuario <= 0 ||
    $usuario === "" ||
    !in_array($rol, $rolesPermitidos, true)
) {

    echo "<script>
        alert('Los datos ingresados no son válidos.');
        window.location='usuarios.php';
    </script>";

    exit();
}

/* Verificar usuario repetido */

$sqlDuplicado = "SELECT id_usuario
                 FROM usuarios
                 WHERE usuario = ?
                 AND id_usuario <> ?
                 LIMIT 1";

$stmtDuplicado = mysqli_prepare(
    $conexion,
    $sqlDuplicado
);

mysqli_stmt_bind_param(
    $stmtDuplicado,
    "si",
    $usuario,
    $id_usuario
);

mysqli_stmt_execute($stmtDuplicado);

$resultadoDuplicado = mysqli_stmt_get_result(
    $stmtDuplicado
);

if (mysqli_num_rows($resultadoDuplicado) > 0) {

    echo "<script>
        alert('El nombre de usuario ya está registrado.');
        window.location='editar_usuario.php?id=$id_usuario';
    </script>";

    exit();
}

/* Actualizar con o sin contraseña */

if ($password !== "") {

    if (strlen($password) < 6) {

        echo "<script>
            alert('La contraseña debe tener al menos 6 caracteres.');
            window.location='editar_usuario.php?id=$id_usuario';
        </script>";

        exit();
    }

    $sql = "UPDATE usuarios
            SET usuario = ?,
                password = ?,
                rol = ?
            WHERE id_usuario = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $usuario,
        $password,
        $rol,
        $id_usuario
    );

} else {

    $sql = "UPDATE usuarios
            SET usuario = ?,
                rol = ?
            WHERE id_usuario = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $usuario,
        $rol,
        $id_usuario
    );
}

if (mysqli_stmt_execute($stmt)) {

    /*
    Si el administrador editó su propio usuario,
    actualizamos también la sesión.
    */

    if (
        $id_usuario ===
        (int) $_SESSION["id_usuario"]
    ) {

        $_SESSION["usuario"] = $usuario;
        $_SESSION["rol"] = $rol;
    }

    echo "<script>
        alert('Usuario actualizado correctamente.');
        window.location='usuarios.php';
    </script>";

} else {

    echo "<script>
        alert('No se pudo actualizar el usuario.');
        window.location='usuarios.php';
    </script>";
}

mysqli_stmt_close($stmt);

?>