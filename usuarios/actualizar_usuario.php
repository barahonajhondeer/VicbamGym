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

/* VALIDACIONES */

if (
    $id_usuario <= 0 ||
    $usuario === "" ||
    !in_array($rol, $rolesPermitidos, true)
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("Los datos ingresados no son válidos.")
    );

    exit();
}

/* VALIDAR USUARIO REPETIDO */

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

$resultadoDuplicado =
    mysqli_stmt_get_result($stmtDuplicado);

if (mysqli_num_rows($resultadoDuplicado) > 0) {

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

/* SI SE INGRESÓ NUEVA CONTRASEÑA */

if ($password !== "") {

    if (strlen($password) < 6) {

        header(
            "Location: editar_usuario.php?id=" .
            $id_usuario .
            "&tipo=advertencia&mensaje=" .
            urlencode(
                "La contraseña debe tener al menos 6 caracteres."
            )
        );

        exit();
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $sql = "UPDATE usuarios
            SET usuario = ?,
                password = ?,
                rol = ?
            WHERE id_usuario = ?";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $usuario,
        $passwordHash,
        $rol,
        $id_usuario
    );

} else {

    /* CONSERVAR CONTRASEÑA ACTUAL */

    $sql = "UPDATE usuarios
            SET usuario = ?,
                rol = ?
            WHERE id_usuario = ?";

    $stmt = mysqli_prepare(
        $conexion,
        $sql
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $usuario,
        $rol,
        $id_usuario
    );
}

/* EJECUTAR */

if (mysqli_stmt_execute($stmt)) {

    if (
        $id_usuario ===
        (int) $_SESSION["id_usuario"]
    ) {

        $_SESSION["usuario"] = $usuario;
        $_SESSION["rol"] = $rol;
    }

    header(
        "Location: usuarios.php?tipo=exito&mensaje=" .
        urlencode(
            "Usuario actualizado correctamente."
        )
    );

    exit();

} else {

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo actualizar el usuario."
        )
    );

    exit();
}

?>