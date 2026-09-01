<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


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
            "No tiene permisos para ingresar al módulo de usuarios."
        )
    );

    exit();
}


/* =========================================
   CONSULTAR USUARIOS
========================================= */

$sql = "
    SELECT
        id_usuario,
        usuario,
        rol,
        estado
    FROM usuarios
    ORDER BY id_usuario DESC
";


$resultado = mysqli_query(
    $conexion,
    $sql
);


if (!$resultado) {

    error_log(
        "Error consultando usuarios: " .
        mysqli_error($conexion)
    );

    $resultado = false;
}


/* =========================================
   FUNCIÓN ESCAPAR TEXTO
========================================= */

function e($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Usuarios | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

</head>

<body>


<!-- =========================================
     MENÚ
========================================= -->

<nav class="navbar">

    <div class="logo-menu">

        <h2>
            VICBAMGYM
        </h2>

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
                class="menu-activo"
            >
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


<!-- =========================================
     NOTIFICACIONES
========================================= -->

<?php

if (
    file_exists(
        "../config/notificaciones.php"
    )
) {

    require_once(
        "../config/notificaciones.php"
    );
}

?>


<!-- =========================================
     CONTENIDO
========================================= -->

<div class="contenedor-principal">


    <!-- =====================================
         REGISTRO DE USUARIO
    ====================================== -->

    <div class="form-container">

        <h2>
            REGISTRO DE USUARIOS
        </h2>


        <form
            action="guardar_usuario.php"
            method="POST"
            autocomplete="off"
        >

            <?php
            echo csrf_field();
            ?>


            <!-- USUARIO -->

            <div class="form-group">

                <label for="usuario">
                    Usuario
                </label>


                <input
                    type="text"
                    name="usuario"
                    id="usuario"

                    minlength="4"
                    maxlength="50"

                    autocomplete="off"
                    required
                >

            </div>


            <!-- CONTRASEÑA -->

            <div class="form-group">

                <label for="password">
                    Contraseña
                </label>


                <input
                    type="password"
                    name="password"
                    id="password"

                    minlength="8"
                    maxlength="100"

                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- ROL -->

            <div class="form-group">

                <label for="rol">
                    Rol
                </label>


                <select
                    name="rol"
                    id="rol"
                    required
                >

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


            <!-- BOTÓN -->

            <button
                type="submit"
                class="btn-guardar"
            >

                Guardar Usuario

            </button>

        </form>

    </div>


    <!-- =====================================
         TABLA DE USUARIOS
    ====================================== -->

    <div class="tabla-container">

        <h2>
            USUARIOS REGISTRADOS
        </h2>


        <div class="tabla-responsive">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>


                <?php

                if ($resultado) {

                    while (
                        $fila =
                        mysqli_fetch_assoc(
                            $resultado
                        )
                    ) {

                        $idUsuario =
                            (int)
                            $fila["id_usuario"];


                        $esUsuarioActual =
                            $idUsuario ===
                            (int)
                            $_SESSION["id_usuario"];


                        $estadoUsuario =
                            $fila["estado"] ??
                            "Activo";

                ?>


                    <tr>


                        <!-- ID -->

                        <td>
                            <?php
                            echo $idUsuario;
                            ?>
                        </td>


                        <!-- USUARIO -->

                        <td>

                            <?php

                            echo e(
                                $fila["usuario"]
                            );

                            ?>

                        </td>


                        <!-- ROL -->

                        <td>

                            <?php

                            if (
                                $fila["rol"] ===
                                "Administrador"
                            ) {

                            ?>

                                <span class="rol-administrador">
                                    Administrador
                                </span>

                            <?php

                            } else {

                            ?>

                                <span class="rol-recepcionista">
                                    Recepcionista
                                </span>

                            <?php

                            }

                            ?>

                        </td>


                        <!-- ESTADO -->

                        <td>

                            <?php

                            if (
                                $estadoUsuario ===
                                "Activo"
                            ) {

                            ?>

                                <span class="estado-activa">
                                    Activo
                                </span>

                            <?php

                            } else {

                            ?>

                                <span class="estado-vencida">
                                    Inactivo
                                </span>

                            <?php

                            }

                            ?>

                        </td>


                        <!-- ACCIONES -->

                        <td>


                            <!-- EDITAR -->

                            <a
                                href="editar_usuario.php?id=<?php
                                    echo $idUsuario;
                                ?>"
                                class="btn-editar"
                            >

                                Editar

                            </a>


                            <?php

                            if (!$esUsuarioActual) {

                                if (
                                    $estadoUsuario ===
                                    "Activo"
                                ) {

                            ?>

                                    <!-- DESACTIVAR -->

                                    <form
                                        action="eliminar_usuario.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm(
                                            '¿Desea desactivar este usuario?'
                                        );"
                                    >

                                        <?php
                                        echo csrf_field();
                                        ?>


                                        <input
                                            type="hidden"
                                            name="id_usuario"
                                            value="<?php
                                                echo $idUsuario;
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="btn-eliminar"
                                        >

                                            Desactivar

                                        </button>

                                    </form>


                            <?php

                                } else {

                            ?>


                                    <!-- REACTIVAR -->

                                    <form
                                        action="reactivar_usuario.php"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <?php
                                        echo csrf_field();
                                        ?>


                                        <input
                                            type="hidden"
                                            name="id_usuario"
                                            value="<?php
                                                echo $idUsuario;
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="btn-editar"
                                        >

                                            Reactivar

                                        </button>

                                    </form>


                            <?php

                                }

                            } else {

                            ?>

                                <span class="usuario-actual">
                                    Sesión actual
                                </span>

                            <?php

                            }

                            ?>

                        </td>

                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="5"
                            style="text-align:center;"
                        >

                            No se pudo cargar la lista de usuarios.

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>