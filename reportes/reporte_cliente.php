<?php

require_once("../config/conexion.php");

/* ===============================
   CONSULTAR CLIENTES
================================ */

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
        ORDER BY id_cliente DESC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error al consultar los clientes: " . mysqli_error($conexion));
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

    <title>Reporte de clientes | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body class="reportes-body">

<!-- ===============================
     MENÚ SUPERIOR
================================ -->

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
            <a href="reportes.php" class="menu-activo">
                📊 Reportes
            </a>
        </li>

        <li>
            <a href="../logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<!-- ===============================
     CONTENIDO PRINCIPAL
================================ -->

<main class="reporte-detalle-contenido">

    <section class="encabezado-reporte-detalle">

        <div>

            <h1>REPORTE DE CLIENTES</h1>

            <p>
                Información de los clientes registrados
                en el sistema VICBAMGYM.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_clientes_pdf.php"
                class="btn-pdf">

                📄 Exportar PDF

            </a>

        </div>

    </section>

    <!-- RESUMEN -->

    <section class="resumen-reporte-individual">

        <div class="tarjeta-total-reporte">

            <span>Total de clientes</span>

            <strong>
                <?php echo $totalClientes; ?>
            </strong>

        </div>

    </section>

    <!-- TABLA -->

    <section class="tabla-reporte-container">

        <div class="tabla-responsive">

            <table class="tabla-reporte">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Cédula</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Dirección</th>
                        <th>Fecha de registro</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($totalClientes > 0) { ?>

                    <?php while ($cliente = mysqli_fetch_assoc($resultado)) { ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['id_cliente']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['cedula']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['nombres']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['apellidos']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['telefono']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['correo']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $cliente['direccion']
                                );
                                ?>
                            </td>

                            <td>

                                <?php

                                if (!empty($cliente['fecha_registro'])) {

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $cliente['fecha_registro']
                                        )
                                    );

                                } else {

                                    echo "Sin fecha";

                                }

                                ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td
                            colspan="8"
                            class="sin-resultados">

                            No existen clientes registrados.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>

</html>