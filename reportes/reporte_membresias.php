<?php

require_once("../config/conexion.php");

/* =================================
   ACTUALIZAR ESTADOS AUTOMÁTICAMENTE
================================= */

$sqlActualizar = "UPDATE membresias
                  SET estado = 'Vencida'
                  WHERE fecha_fin < CURDATE()
                  AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizar);

/* =================================
   CONSULTAR MEMBRESÍAS
================================= */

$sql = "SELECT
            m.id_membresia,
            c.cedula,
            c.nombres,
            c.apellidos,
            m.tipo,
            m.valor,
            m.fecha_inicio,
            m.fecha_fin,
            m.estado
        FROM membresias m
        INNER JOIN clientes c
            ON m.id_cliente = c.id_cliente
        ORDER BY m.id_membresia DESC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die(
        "Error al consultar las membresías: " .
        mysqli_error($conexion)
    );
}

$totalMembresias = mysqli_num_rows($resultado);

/* =================================
   CONTADORES
================================= */

$sqlActivas = "SELECT COUNT(*) AS total
               FROM membresias
               WHERE estado = 'Activa'";

$resultadoActivas = mysqli_query($conexion, $sqlActivas);
$datosActivas = mysqli_fetch_assoc($resultadoActivas);
$totalActivas = $datosActivas['total'] ?? 0;

$sqlVencidas = "SELECT COUNT(*) AS total
                FROM membresias
                WHERE estado = 'Vencida'";

$resultadoVencidas = mysqli_query($conexion, $sqlVencidas);
$datosVencidas = mysqli_fetch_assoc($resultadoVencidas);
$totalVencidas = $datosVencidas['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reporte de membresías | VICBAMGYM</title>

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
            <a
                href="reportes.php"
                class="menu-activo">

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

            <h1>REPORTE DE MEMBRESÍAS</h1>

            <p>
                Estado, fechas y valores de las membresías
                registradas en VICBAMGYM.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_membresias_pdf.php"
                class="btn-pdf">

                📄 Exportar PDF

            </a>

        </div>

    </section>

    <!-- ===============================
         RESUMEN
    ================================ -->

    <section class="resumen-membresias-reporte">

        <div class="tarjeta-total-reporte">

            <span>Total de membresías</span>

            <strong>
                <?php echo $totalMembresias; ?>
            </strong>

        </div>

        <div class="tarjeta-estado-reporte activa">

            <span>Membresías activas</span>

            <strong>
                <?php echo $totalActivas; ?>
            </strong>

        </div>

        <div class="tarjeta-estado-reporte vencida">

            <span>Membresías vencidas</span>

            <strong>
                <?php echo $totalVencidas; ?>
            </strong>

        </div>

    </section>

    <!-- ===============================
         TABLA
    ================================ -->

    <section class="tabla-reporte-container">

        <div class="tabla-responsive">

            <table class="tabla-reporte">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Cédula</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Fecha de inicio</th>
                        <th>Fecha de fin</th>
                        <th>Estado</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($totalMembresias > 0) { ?>

                    <?php
                    while (
                        $membresia =
                        mysqli_fetch_assoc($resultado)
                    ) {
                    ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $membresia['id_membresia']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $membresia['cedula']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $membresia['nombres'] .
                                    " " .
                                    $membresia['apellidos']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $membresia['tipo']
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    (float) $membresia['valor'],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia['fecha_inicio']
                                    )
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia['fecha_fin']
                                    )
                                );
                                ?>
                            </td>

                            <td>

                                <?php
                                if (
                                    $membresia['estado'] ===
                                    'Activa'
                                ) {
                                ?>

                                    <span class="estado-activa">
                                        🟢 Activa
                                    </span>

                                <?php } else { ?>

                                    <span class="estado-vencida">
                                        🔴 Vencida
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td
                            colspan="8"
                            class="sin-resultados">

                            No existen membresías registradas.

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