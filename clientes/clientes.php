<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

/* =========================================
   CONSULTAR CLIENTES
========================================= */

$sql = "SELECT
            id_cliente,
            cedula,
            nombres,
            apellidos,
            telefono,
            correo,
            direccion,
            fecha_registro,
            estado
        FROM clientes
        ORDER BY id_cliente DESC";

$resultado = mysqli_query(
    $conexion,
    $sql
);

if (!$resultado) {

    die(
        "Error al consultar los clientes: " .
        mysqli_error($conexion)
    );
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Clientes | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

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

            <a
                href="clientes.php"
                class="menu-activo">

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

        <?php

        if (
            isset($_SESSION["rol"]) &&
            $_SESSION["rol"] === "Administrador"
        ) {

        ?>

            <li>

                <a href="../usuarios/usuarios.php">

                    👨‍💼 Usuarios

                </a>

            </li>

        <?php

        }

        ?>

        <li>

            <a href="../logout.php">

                🚪 Salir

            </a>

        </li>

    </ul>

</nav>

<!-- =========================================
     NOTIFICACIONES TOAST
========================================= -->

<?php

require_once("../config/notificaciones.php");

?>

<!-- =========================================
     CONTENIDO PRINCIPAL
========================================= -->

<div class="contenedor-principal">

    <!-- =====================================
         FORMULARIO REGISTRO
    ====================================== -->

    <div class="form-container">

        <h2>
            REGISTRO DE CLIENTES
        </h2>

        <form
            action="guardar_cliente.php"
            method="POST">

            <!-- CÉDULA -->

            <div class="form-group">

                <label for="cedula">

                    Cédula

                </label>

                <input
                    type="text"
                    name="cedula"
                    id="cedula"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    placeholder="10 dígitos"
                    required>

            </div>

            <!-- NOMBRES -->

            <div class="form-group">

                <label for="nombres">

                    Nombres

                </label>

                <input
                    type="text"
                    name="nombres"
                    id="nombres"
                    maxlength="100"
                    required>

            </div>

            <!-- APELLIDOS -->

            <div class="form-group">

                <label for="apellidos">

                    Apellidos

                </label>

                <input
                    type="text"
                    name="apellidos"
                    id="apellidos"
                    maxlength="100"
                    required>

            </div>

            <!-- TELÉFONO -->

            <div class="form-group">

                <label for="telefono">

                    Teléfono

                </label>

                <input
                    type="text"
                    name="telefono"
                    id="telefono"
                    maxlength="10"
                    pattern="[0-9]{10}"
                    placeholder="10 dígitos"
                    required>

            </div>

            <!-- CORREO -->

            <div class="form-group">

                <label for="correo">

                    Correo

                </label>

                <input
                    type="email"
                    name="correo"
                    id="correo"
                    maxlength="120"
                    placeholder="ejemplo@correo.com"
                    required>

            </div>

            <!-- DIRECCIÓN -->

            <div class="form-group">

                <label for="direccion">

                    Dirección

                </label>

                <input
                    type="text"
                    name="direccion"
                    id="direccion"
                    maxlength="150"
                    required>

            </div>

            <!-- GUARDAR -->

            <button
                type="submit"
                class="btn-guardar">

                Guardar Cliente

            </button>

        </form>

    </div>

    <!-- =====================================
         LISTADO DE CLIENTES
    ====================================== -->

    <div
        class="tabla-container"
        data-tabla-buscable>

        <h2>
            CLIENTES REGISTRADOS
        </h2>

        <!-- =================================
             BUSCADOR
        ================================== -->

        <div class="herramientas-tabla">

            <div class="buscador-tabla">

                <label for="buscarClientes">

                    Buscar cliente

                </label>

                <input
                    type="search"
                    id="buscarClientes"
                    data-buscador
                    placeholder="Cédula, nombre, correo, teléfono o estado"
                    autocomplete="off">

            </div>

            <span
                class="contador-resultados"
                data-contador-resultados>
            </span>

        </div>

        <!-- =================================
             TABLA CON SCROLL
        ================================== -->

        <div class="tabla-responsive">

            <table id="tablaClientes">

                <thead>

                    <tr>

                        <th
                            data-ordenable
                            data-tipo="numero">

                            ID

                        </th>

                        <th data-ordenable>

                            Cédula

                        </th>

                        <th data-ordenable>

                            Nombres

                        </th>

                        <th data-ordenable>

                            Apellidos

                        </th>

                        <th data-ordenable>

                            Teléfono

                        </th>

                        <th data-ordenable>

                            Correo

                        </th>

                        <th data-ordenable>

                            Dirección

                        </th>

                        <th
                            data-ordenable
                            data-tipo="fecha">

                            Registro

                        </th>

                        <th data-ordenable>

                            Estado

                        </th>

                        <th>

                            Acciones

                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php

                while (
                    $fila =
                    mysqli_fetch_assoc($resultado)
                ) {

                    $idCliente =
                        (int) $fila["id_cliente"];

                    $estado =
                        $fila["estado"] ?? "Activo";

                ?>

                    <tr
                        class="<?php
                            echo $estado === "Inactivo"
                                ? "fila-cliente-inactivo"
                                : "";
                        ?>">

                        <!-- ID -->

                        <td
                            data-orden="<?php
                                echo $idCliente;
                            ?>">

                            <?php
                            echo $idCliente;
                            ?>

                        </td>

                        <!-- CÉDULA -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["cedula"]
                            );

                            ?>

                        </td>

                        <!-- NOMBRES -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["nombres"]
                            );

                            ?>

                        </td>

                        <!-- APELLIDOS -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["apellidos"]
                            );

                            ?>

                        </td>

                        <!-- TELÉFONO -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["telefono"]
                            );

                            ?>

                        </td>

                        <!-- CORREO -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["correo"]
                            );

                            ?>

                        </td>

                        <!-- DIRECCIÓN -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["direccion"]
                            );

                            ?>

                        </td>

                        <!-- FECHA REGISTRO -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $fila["fecha_registro"]
                                );
                            ?>">

                            <?php

                            if (
                                !empty(
                                    $fila["fecha_registro"]
                                )
                            ) {

                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $fila[
                                            "fecha_registro"
                                        ]
                                    )
                                );

                            } else {

                                echo "-";
                            }

                            ?>

                        </td>

                        <!-- ESTADO -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $estado
                                );
                            ?>">

                            <?php

                            if (
                                $estado === "Activo"
                            ) {

                            ?>

                                <span
                                    class="estado-activa">

                                    Activo

                                </span>

                            <?php

                            } else {

                            ?>

                                <span
                                    class="estado-vencida">

                                    Inactivo

                                </span>

                            <?php

                            }

                            ?>

                        </td>

                        <!-- =================================
                             ACCIONES
                        ================================== -->

                        <td class="acciones-cliente">

                            <!-- EDITAR -->

                            <a
                                href="editar_cliente.php?id=<?php
                                    echo $idCliente;
                                ?>"
                                class="btn-editar">

                                Editar

                            </a>

                            <!-- =================================
                                 SOLO ADMINISTRADOR
                            ================================== -->

                            <?php

                            if (
                                isset($_SESSION["rol"]) &&
                                $_SESSION["rol"] ===
                                "Administrador"
                            ) {

                                if (
                                    $estado === "Activo"
                                ) {

                            ?>

                                    <!-- DESACTIVAR -->

                                    <a
                                        href="eliminar_cliente.php?id=<?php
                                            echo $idCliente;
                                        ?>"
                                        class="btn-eliminar"
                                        onclick="
                                            return confirm(
                                                '¿Desea desactivar este cliente? Sus membresías y pagos se conservarán.'
                                            );
                                        ">

                                        Desactivar

                                    </a>

                            <?php

                                } else {

                            ?>

                                    <!-- REACTIVAR -->

                                    <a
                                        href="reactivar_cliente.php?id=<?php
                                            echo $idCliente;
                                        ?>"
                                        class="btn-renovar"
                                        onclick="
                                            return confirm(
                                                '¿Desea reactivar este cliente?'
                                            );
                                        ">

                                        Reactivar

                                    </a>

                            <?php

                                }

                            }

                            ?>

                        </td>

                    </tr>

                <?php

                }

                ?>

                    <!-- =================================
                         SIN RESULTADOS DEL BUSCADOR
                    ================================== -->

                    <tr
                        data-sin-resultados
                        class="fila-busqueda-vacia"
                        style="display:none;">

                        <td colspan="10">

                            No se encontraron clientes.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================
     JAVASCRIPT DE BÚSQUEDA Y ORDENAMIENTO
========================================= -->

<script src="../assets/js/tablas.js"></script>

</body>

</html>