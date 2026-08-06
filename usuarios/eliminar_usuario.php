<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: ../dashboard.php");
    exit();
}

$id_usuario = (int) ($_GET["id"] ?? 0);

if ($id_usuario <= 0) {
    header("Location: usuarios.php");
    exit();
}

/* No permitir eliminar la sesión actual */

if (
    $id_usuario ===
    (int) $_SESSION["id_usuario"]
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("No puede eliminar el usuario que tiene la sesión iniciada.")
        );
        
        exit();

    exit();
}

/* Verificar que exista */

$sqlExiste = "SELECT id_usuario
              FROM usuarios
              WHERE id_usuario = ?
              LIMIT 1";

$stmtExiste = mysqli_prepare(
    $conexion,
    $sqlExiste
);

mysqli_stmt_bind_param(
    $stmtExiste,
    "i",
    $id_usuario
);

mysqli_stmt_execute($stmtExiste);

$resultadoExiste = mysqli_stmt_get_result(
    $stmtExiste
);

if (mysqli_num_rows($resultadoExiste) === 0) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode("El usuario seleccionado no existe.")
        );
        
        exit();
}

/* Eliminar */

$sqlEliminar = "DELETE FROM usuarios
                WHERE id_usuario = ?";

$stmtEliminar = mysqli_prepare(
    $conexion,
    $sqlEliminar
);

mysqli_stmt_bind_param(
    $stmtEliminar,
    "i",
    $id_usuario
);

if (mysqli_stmt_execute($stmtEliminar)) {

    header(
        "Location: usuarios.php?tipo=exito&mensaje=" .
        urlencode("Usuario eliminado correctamente.")
        );
        
        exit();

} else {

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode("No se pudo eliminar el usuario.")
        );
        
        exit();
}

mysqli_stmt_close($stmtEliminar);

?>