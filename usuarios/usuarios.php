<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {

    echo "<script>
        alert('No tiene permisos para ingresar al módulo de usuarios.');
        window.location='../dashboard.php';
    </script>";

    exit();
}

$sql = "SELECT
            id_usuario,
            usuario,
            rol
        FROM usuarios
        ORDER BY id_usuario DESC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error al consultar usuarios: " . mysqli_error($conexion));
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Usuarios | VICBAMGYM</title>

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
            <a href="../dashboard.php">🏠 Dashboard</a>
        </li>

        <li>
            <a href="../clientes/clientes.php">👥 Clientes</a>
        </li>

        <li>
            <a href="../membresias/membresias.php">💳 Membresías</a>
        </li>

        <li>
            <a href="../pagos/pagos.php">💰 Pagos</a>
        </li>

        <li>
            <a href="../reportes/reportes.php">📊 Reportes</a>
        </li>

        <li>
            <a href="usuarios.php" class="menu-activo">👨‍💼 Usuarios</a>
        </li>

        <li>
            <a href="../logout.php">🚪 Salir</a>
        </li>

    </ul>

</nav>

<?php
require_once("../config/notificaciones.php");
?>

<div class="contenedor-principal">

    <div class="form-container">

        <h2>REGISTRO DE USUARIOS</h2>

        <form
            action="guardar_usuario.php"
            method="POST">

            <div class="form-group">

                <label>Usuario</label>

                <input
                    type="text"
                    name="usuario"
                    maxlength="50"
                    required>

            </div>

            <div class="form-group">

                <label>Contraseña</label>

                <input
                    type="password"
                    name="password"
                    minlength="6"
                    required>

            </div>

            <div class="form-group">

                <label>Rol</label>

                <select
                    name="rol"
                    required>

                    <option value="">
                        Seleccione un rol
                    </option>

                    <option value="Administrador">
                        Administrador
                    </option>

                    <option value="Recepcionista">
                        Recepcionista
                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="btn-guardar">

                Guardar Usuario

            </button>

        </form>

    </div>

    <div class="tabla-container">

        <h2>USUARIOS REGISTRADOS</h2>

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

                <tr>

                    <td>
                        <?php echo $fila["id_usuario"]; ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fila["usuario"]
                        );
                        ?>
                    </td>

                    <td>

                        <?php if ($fila["rol"] === "Administrador") { ?>

                            <span class="rol-administrador">
                                Administrador
                            </span>

                        <?php } else { ?>

                            <span class="rol-recepcionista">
                                Recepcionista
                            </span>

                        <?php } ?>

                    </td>

                    <td>

                        <a
                            href="editar_usuario.php?id=<?php
                                echo $fila["id_usuario"];
                            ?>"
                            class="btn-editar">

                            Editar

                        </a>

                        <?php
                        if (
                            (int) $fila["id_usuario"] !==
                            (int) $_SESSION["id_usuario"]
                        ) {
                        ?>

                            <a
                                href="eliminar_usuario.php?id=<?php
                                    echo $fila["id_usuario"];
                                ?>"
                                class="btn-eliminar"
                                onclick="return confirm(
                                    '¿Desea eliminar este usuario?'
                                )">

                                Eliminar

                            </a>

                        <?php } else { ?>

                            <span class="usuario-actual">
                                Sesión actual
                            </span>

                        <?php } ?>

                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>