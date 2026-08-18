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

$sql = "SELECT
            id_usuario,
            usuario,
            rol
        FROM usuarios
        WHERE id_usuario = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_usuario
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$usuarioDatos = mysqli_fetch_assoc($resultado);

if (!$usuarioDatos) {

    echo "<script>
        alert('El usuario no existe.');
        window.location='usuarios.php';
    </script>";

    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Editar Usuario | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body>

<nav class="navbar">

    <div class="logo-menu">
        <h2>VICBAMGYM</h2>
    </div>

    <ul class="menu">

        <li>
            <a href="../dashboard.php">
                🏠 Dashboard
            </a>
        </li>

        <li>
            <a href="../clientes/clientes.php">
                👥 Clientes
            </a>
        </li>

        <li>
            <a href="../membresias/membresias.php">
                💳 Membresías
            </a>
        </li>

        <li>
            <a href="../pagos/pagos.php">
                💰 Pagos
            </a>
        </li>

        <li>
            <a href="../reportes/reportes.php">
                📊 Reportes
            </a>
        </li>

        <li>
            <a
                href="usuarios.php"
                class="menu-activo">

                👨‍💼 Usuarios

            </a>
        </li>

        <li>
            <a href="../logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<div class="contenedor-edicion">

    <div class="form-container">

        <h2>EDITAR USUARIO</h2>

        <form
            action="actualizar_usuario.php"
            method="POST">

            <input
                type="hidden"
                name="id_usuario"
                value="<?php
                    echo $usuarioDatos["id_usuario"];
                ?>">

            <div class="form-group">

                <label>Usuario</label>

                <input
                    type="text"
                    name="usuario"
                    maxlength="50"
                    value="<?php
                        echo htmlspecialchars(
                            $usuarioDatos["usuario"]
                        );
                    ?>"
                    required>

            </div>

            <div class="form-group">

                <label>Nueva contraseña</label>

                <input
                    type="password"
                    name="password"
                    minlength="6"
                    placeholder="Deje vacío para conservar la contraseña actual">

            </div>

            <div class="form-group">

                <label>Rol</label>

                <select
                    name="rol"
                    required>

                    <option
                        value="Administrador"
                        <?php
                        if (
                            $usuarioDatos["rol"] ===
                            "Administrador"
                        ) {
                            echo "selected";
                        }
                        ?>>

                        Administrador

                    </option>

                    <option
                        value="Recepcionista"
                        <?php
                        if (
                            $usuarioDatos["rol"] ===
                            "Recepcionista"
                        ) {
                            echo "selected";
                        }
                        ?>>

                        Recepcionista

                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="btn-guardar">

                Actualizar Usuario

            </button>

            <a
                href="usuarios.php"
                class="btn-cancelar">

                Cancelar

            </a>

        </form>

    </div>

</div>

</body>

</html>