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
            "No tiene permisos para realizar esta acción."
        )
    );

    exit();
}


/* =========================================
   VALIDAR ID
========================================= */

$id_usuario = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (
    !$id_usuario ||
    $id_usuario <= 0
) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "Usuario no válido."
        )
    );

    exit();
}


/* =========================================
   BUSCAR USUARIO
========================================= */

$sql = "
    SELECT
        id_usuario,
        usuario,
        rol,
        estado
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando consulta de usuario: " .
        mysqli_error($conexion)
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo consultar el usuario."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_usuario
);


if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Error consultando usuario: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: usuarios.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo consultar el usuario."
        )
    );

    exit();
}


$resultado = mysqli_stmt_get_result(
    $stmt
);


$usuarioDatos = mysqli_fetch_assoc(
    $resultado
);


mysqli_stmt_close(
    $stmt
);


/* =========================================
   VALIDAR EXISTENCIA
========================================= */

if (!$usuarioDatos) {

    header(
        "Location: usuarios.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El usuario seleccionado no existe."
        )
    );

    exit();
}


/* =========================================
   FUNCIÓN ESCAPAR
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
        Editar Usuario | VICBAMGYM
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
     CONTENIDO
========================================= -->

<div class="contenedor-edicion">

    <div class="form-container">

        <h2>
            EDITAR USUARIO
        </h2>


        <form
            action="actualizar_usuario.php"
            method="POST"
            autocomplete="off"
        >

            <?php
            echo csrf_field();
            ?>


            <!-- ID -->

            <input
                type="hidden"
                name="id_usuario"
                value="<?php
                    echo (int)
                    $usuarioDatos["id_usuario"];
                ?>"
            >


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

                    value="<?php
                        echo e(
                            $usuarioDatos["usuario"]
                        );
                    ?>"

                    autocomplete="off"
                    required
                >

            </div>


            <!-- NUEVA CONTRASEÑA -->

            <div class="form-group">

                <label for="password">
                    Nueva contraseña
                </label>


                <input
                    type="password"
                    name="password"
                    id="password"

                    minlength="8"
                    maxlength="100"

                    autocomplete="new-password"

                    placeholder="Deje vacío para conservar la contraseña actual"
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

                    <option
                        value="Administrador"
                        <?php
                        if (
                            $usuarioDatos["rol"] ===
                            "Administrador"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
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
                        ?>
                    >
                        Recepcionista
                    </option>

                </select>

            </div>


            <!-- ESTADO -->

            <div class="form-group">

                <label>
                    Estado
                </label>


                <input
                    type="text"
                    value="<?php
                        echo e(
                            $usuarioDatos["estado"] ??
                            "Activo"
                        );
                    ?>"
                    readonly
                >

            </div>


            <!-- BOTONES -->

            <button
                type="submit"
                class="btn-guardar"
            >
                Actualizar Usuario
            </button>


            <a
                href="usuarios.php"
                class="btn-cancelar"
            >
                Cancelar
            </a>

        </form>

    </div>

</div>

</body>

</html>