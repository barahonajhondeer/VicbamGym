<?php

require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");

/* =================================
   RECIBIR FILTRO
================================= */

$buscar = trim($_GET["buscar"] ?? "");

/* =================================
   CONSULTAR CLIENTES
================================= */

$sql = "SELECT
            id_cliente,
            cedula,
            nombres,
            apellidos,
            telefono,
            correo,
            direccion,
            fecha_registro
        FROM clientes
        WHERE
            cedula LIKE ?
            OR nombres LIKE ?
            OR apellidos LIKE ?
            OR telefono LIKE ?
            OR correo LIKE ?
            OR direccion LIKE ?
        ORDER BY id_cliente DESC";

$textoBuscar = "%" . $buscar . "%";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {

    die(
        "Error al consultar los clientes: " .
        mysqli_error($conexion)
    );
}

$totalClientes = mysqli_num_rows($resultado);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Reporte de Clientes | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body class="reportes-body">

<!-- =================================
     MENÚ SUPERIOR
================================= -->

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

            <a
                href="reportes.php"
                class="menu-activo">

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

<!-- =================================
     NOTIFICACIONES
================================= -->

<?php

require_once("../config/notificaciones.php");

?>

<!-- =================================
     CONTENIDO PRINCIPAL
================================= -->

<main class="reporte-detalle-contenido">

    <!-- =================================
         ENCABEZADO
    ================================= -->

    <section class="encabezado-reporte-detalle">

        <div>

            <h1>
                REPORTE DE CLIENTES
            </h1>

            <p>
                Consulte la información de los clientes
                registrados en VICBAMGYM.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_clientes_pdf.php?buscar=<?php
                    echo urlencode($buscar);
                ?>"
                class="btn-pdf">

                📄 Exportar PDF

            </a>

        </div>

    </section>

    <!-- =================================
         FILTRO
    ================================= -->

    <section class="filtro-pagos-reporte">

        <form
            method="GET"
            action="reporte_cliente.php">

            <div class="campo-filtro">

                <label for="buscar">

                    Buscar cliente

                </label>

                <input
                    type="text"
                    name="buscar"
                    id="buscar"
                    value="<?php
                        echo htmlspecialchars(
                            $buscar
                        );
                    ?>"
                    placeholder="Cédula, nombre, correo o teléfono"
                    autocomplete="off">

            </div>

            <div class="acciones-filtro">

                <button
                    type="submit"
                    class="btn-buscar-reporte">

                    🔍 Buscar

                </button>

                <a
                    href="reporte_cliente.php"
                    class="btn-limpiar-reporte">

                    Limpiar

                </a>

            </div>

        </form>

    </section>

    <!-- =================================
         RESUMEN
    ================================= -->

    <section class="resumen-reporte-individual">

        <div class="tarjeta-total-reporte">

            <span>

                Clientes encontrados

            </span>

            <strong>

                <?php
                echo $totalClientes;
                ?>

            </strong>

        </div>

    </section>

    <!-- =================================
         MENSAJE DEL FILTRO
    ================================= -->

    <?php

    if ($buscar !== "") {

    ?>

        <div class="resultado-filtro-reporte">

            Mostrando resultados para:

            <strong>

                <?php
                echo htmlspecialchars(
                    $buscar
                );
                ?>

            </strong>

        </div>

    <?php

    }

    ?>

    <!-- =================================
         TABLA
    ================================= -->

    <section class="tabla-reporte-container">

        <div class="tabla-responsive">

            <table class="tabla-reporte">

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Cédula
                        </th>

                        <th>
                            Nombres
                        </th>

                        <th>
                            Apellidos
                        </th>

                        <th>
                            Teléfono
                        </th>

                        <th>
                            Correo
                        </th>

                        <th>
                            Dirección
                        </th>

                        <th>
                            Fecha de registro
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if ($totalClientes > 0) {

                    while (
                        $cliente =
                        mysqli_fetch_assoc(
                            $resultado
                        )
                    ) {

                ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "id_cliente"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- CÉDULA -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "cedula"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- NOMBRES -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "nombres"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- APELLIDOS -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "apellidos"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- TELÉFONO -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "telefono"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- CORREO -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "correo"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- DIRECCIÓN -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $cliente[
                                        "direccion"
                                    ]
                                );

                                ?>

                            </td>

                            <!-- FECHA -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $cliente[
                                            "fecha_registro"
                                        ]
                                    )
                                ) {

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $cliente[
                                                "fecha_registro"
                                            ]
                                        )
                                    );

                                } else {

                                    echo "Sin fecha";
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
                            colspan="8"
                            class="sin-resultados">

                            No se encontraron clientes
                            con el filtro seleccionado.

                        </td>

                    </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>

</html>