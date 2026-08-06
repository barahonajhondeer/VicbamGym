<?php

require_once("config/conexion.php");

/* Actualizar membresías vencidas */
$sqlActualizar = "UPDATE membresias
                  SET estado = 'Vencida'
                  WHERE fecha_fin < CURDATE()
                  AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizar);

/* Total de clientes */
$sqlClientes = "SELECT COUNT(*) AS total FROM clientes";
$resultadoClientes = mysqli_query($conexion, $sqlClientes);
$totalClientes = mysqli_fetch_assoc($resultadoClientes)['total'] ?? 0;

/* Membresías activas */
$sqlActivas = "SELECT COUNT(*) AS total
               FROM membresias
               WHERE estado = 'Activa'";

$resultadoActivas = mysqli_query($conexion, $sqlActivas);
$totalActivas = mysqli_fetch_assoc($resultadoActivas)['total'] ?? 0;

/* Membresías vencidas */
$sqlVencidas = "SELECT COUNT(*) AS total
                FROM membresias
                WHERE estado = 'Vencida'";

$resultadoVencidas = mysqli_query($conexion, $sqlVencidas);
$totalVencidas = mysqli_fetch_assoc($resultadoVencidas)['total'] ?? 0;

/* Ingresos del mes actual */
$sqlIngresosMes = "SELECT COALESCE(SUM(valor), 0) AS total
                   FROM pagos
                   WHERE YEAR(fecha_pago) = YEAR(CURDATE())
                   AND MONTH(fecha_pago) = MONTH(CURDATE())";

$resultadoIngresosMes = mysqli_query($conexion, $sqlIngresosMes);
$ingresosMes = mysqli_fetch_assoc($resultadoIngresosMes)['total'] ?? 0;

/* Pagos realizados hoy */
$sqlPagosHoy = "SELECT COUNT(*) AS total
                FROM pagos
                WHERE fecha_pago = CURDATE()";

$resultadoPagosHoy = mysqli_query($conexion, $sqlPagosHoy);
$pagosHoy = mysqli_fetch_assoc($resultadoPagosHoy)['total'] ?? 0;

/* Membresías que vencen en los próximos 5 días */
$sqlProximas = "SELECT
                    c.nombres,
                    c.apellidos,
                    m.tipo,
                    m.fecha_fin,
                    DATEDIFF(m.fecha_fin, CURDATE()) AS dias_restantes
                FROM membresias m
                INNER JOIN clientes c
                    ON m.id_cliente = c.id_cliente
                WHERE m.estado = 'Activa'
                AND m.fecha_fin BETWEEN CURDATE()
                                    AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
                ORDER BY m.fecha_fin ASC";

$resultadoProximas = mysqli_query($conexion, $sqlProximas);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Dashboard | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="assets/css/styles.css">

</head>

<body class="dashboard-body">

<nav class="navbar">

    <div class="logo-menu">
        <h2>VICBAMGYM</h2>
    </div>

    <ul class="menu">

        <li>
            <a href="dashboard.php" class="menu-activo">
                🏠 Dashboard
            </a>
        </li>

        <li>
            <a href="clientes/clientes.php">
                👥 Clientes
            </a>
        </li>

        <li>
            <a href="membresias/membresias.php">
                💳 Membresías
            </a>
        </li>

        <li>
            <a href="pagos/pagos.php">
                💰 Pagos
            </a>
        </li>

        <li>
            <a href="reportes/reportes.php">
                📊 Reportes
            </a>
        </li>

        <li>
            <a href="logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<main class="dashboard-contenido">

    <section class="dashboard-encabezado">

        <h1>PANEL DE ADMINISTRACIÓN</h1>

        <p>
            Resumen general y accesos principales del sistema VICBAMGYM.
        </p>

    </section>

    <section class="estadisticas-dashboard">

        <div class="estadistica-card">

            <div class="estadistica-icono">
                👥
            </div>

            <div>

                <span>Clientes registrados</span>

                <strong>
                    <?php echo $totalClientes; ?>
                </strong>

            </div>

        </div>

        <div class="estadistica-card estadistica-verde">

            <div class="estadistica-icono">
                ✅
            </div>

            <div>

                <span>Membresías activas</span>

                <strong>
                    <?php echo $totalActivas; ?>
                </strong>

            </div>

        </div>

        <div class="estadistica-card estadistica-roja">

            <div class="estadistica-icono">
                ⚠️
            </div>

            <div>

                <span>Membresías vencidas</span>

                <strong>
                    <?php echo $totalVencidas; ?>
                </strong>

            </div>

        </div>

        <div class="estadistica-card estadistica-verde">

            <div class="estadistica-icono">
                💰
            </div>

            <div>

                <span>Ingresos del mes</span>

                <strong>
                    $<?php
                    echo number_format(
                        (float) $ingresosMes,
                        2
                    );
                    ?>
                </strong>

            </div>

        </div>

        <div class="estadistica-card">

            <div class="estadistica-icono">
                📅
            </div>

            <div>

                <span>Pagos realizados hoy</span>

                <strong>
                    <?php echo $pagosHoy; ?>
                </strong>

            </div>

        </div>

    </section>

    <section class="dashboard-secciones">

        <div class="accesos-dashboard">

            <h2>Accesos rápidos</h2>

            <div class="dashboard-modulos">

                <a href="clientes/clientes.php">

                    <article class="modulo-dashboard">

                        <span>👥</span>

                        <h3>Clientes</h3>

                        <p>
                            Registrar y administrar clientes.
                        </p>

                    </article>

                </a>

                <a href="membresias/membresias.php">

                    <article class="modulo-dashboard">

                        <span>💳</span>

                        <h3>Membresías</h3>

                        <p>
                            Gestionar planes y vencimientos.
                        </p>

                    </article>

                </a>

                <a href="pagos/pagos.php">

                    <article class="modulo-dashboard">

                        <span>💰</span>

                        <h3>Pagos</h3>

                        <p>
                            Registrar y consultar pagos.
                        </p>

                    </article>

                </a>

                <a href="reportes/reportes.php">

                    <article class="modulo-dashboard">

                        <span>📊</span>

                        <h3>Reportes</h3>

                        <p>
                            Consultar y exportar información.
                        </p>

                    </article>

                </a>

            </div>

        </div>

        <aside class="alertas-dashboard">

            <h2>Próximas a vencer</h2>

            <?php
            if (
                $resultadoProximas &&
                mysqli_num_rows($resultadoProximas) > 0
            ) {
            ?>

                <?php
                while (
                    $proxima =
                    mysqli_fetch_assoc($resultadoProximas)
                ) {
                ?>

                    <div class="alerta-vencimiento">

                        <div>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $proxima['nombres'] .
                                    " " .
                                    $proxima['apellidos']
                                );
                                ?>
                            </strong>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $proxima['tipo']
                                );
                                ?>
                            </span>

                        </div>

                        <div class="dias-restantes">

                            <?php
                            if (
                                (int) $proxima['dias_restantes']
                                === 0
                            ) {
                                echo "Vence hoy";
                            } else {
                                echo "Faltan " .
                                     $proxima['dias_restantes'] .
                                     " días";
                            }
                            ?>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="sin-alertas">

                    No existen membresías próximas a vencer.

                </div>

            <?php } ?>

        </aside>

    </section>

</main>

</body>

</html>