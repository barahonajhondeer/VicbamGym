<?php

require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");

/* ===============================
   OBTENER DATOS PARA EL RESUMEN
================================ */

// Total de clientes
$sqlClientes = "SELECT COUNT(*) AS total FROM clientes";
$resultadoClientes = mysqli_query($conexion, $sqlClientes);
$datosClientes = mysqli_fetch_assoc($resultadoClientes);
$totalClientes = $datosClientes['total'] ?? 0;

// Membresías activas
$sqlActivas = "SELECT COUNT(*) AS total
               FROM membresias
               WHERE estado = 'Activa'";

$resultadoActivas = mysqli_query($conexion, $sqlActivas);
$datosActivas = mysqli_fetch_assoc($resultadoActivas);
$totalActivas = $datosActivas['total'] ?? 0;

// Membresías vencidas
$sqlVencidas = "SELECT COUNT(*) AS total
                FROM membresias
                WHERE estado = 'Vencida'";

$resultadoVencidas = mysqli_query($conexion, $sqlVencidas);
$datosVencidas = mysqli_fetch_assoc($resultadoVencidas);
$totalVencidas = $datosVencidas['total'] ?? 0;

// Total de ingresos
$sqlIngresos = "SELECT COALESCE(SUM(valor), 0) AS total
                FROM pagos";

$resultadoIngresos = mysqli_query($conexion, $sqlIngresos);
$datosIngresos = mysqli_fetch_assoc($resultadoIngresos);
$totalIngresos = $datosIngresos['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reportes | VICBAMGYM</title>

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
            <a href="../usuarios/usuarios.php">
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

<!-- ===============================
     CONTENIDO PRINCIPAL
================================ -->

<main class="reportes-contenido">

    <section class="encabezado-reportes">

        <h1>GESTIÓN DE REPORTES</h1>

        <p>
            Consulte la información general de clientes,
            membresías y pagos registrados en el sistema.
        </p>

    </section>

    <!-- RESUMEN GENERAL -->

    <section class="resumen-reportes">

        <div class="tarjeta-resumen">

            <h3>Clientes registrados</h3>

            <span>
                <?php echo $totalClientes; ?>
            </span>

        </div>

        <div class="tarjeta-resumen">

            <h3>Membresías activas</h3>

            <span>
                <?php echo $totalActivas; ?>
            </span>

        </div>

        <div class="tarjeta-resumen">

            <h3>Membresías vencidas</h3>

            <span>
                <?php echo $totalVencidas; ?>
            </span>

        </div>

        <div class="tarjeta-resumen">

            <h3>Ingresos registrados</h3>

            <span>
                $<?php echo number_format($totalIngresos, 2); ?>
            </span>

        </div>

    </section>

    <!-- TIPOS DE REPORTES -->

    <section class="opciones-reportes">

        <a
            href="reporte_cliente.php"
            class="enlace-reporte">

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    👥
                </div>

                <h2>Reporte de clientes</h2>

                <p>
                    Consulte la información de todos los
                    clientes registrados en el gimnasio.
                </p>

                <span class="btn-reporte">
                    Ver reporte
                </span>

            </article>

        </a>

        <a
            href="reporte_membresias.php"
            class="enlace-reporte">

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    💳
                </div>

                <h2>Reporte de membresías</h2>

                <p>
                    Consulte membresías activas, vencidas,
                    fechas y tipos de planes registrados.
                </p>

                <span class="btn-reporte">
                    Ver reporte
                </span>

            </article>

        </a>

        <a
            href="reporte_pagos.php"
            class="enlace-reporte">

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    💰
                </div>

                <h2>Reporte de pagos</h2>

                <p>
                    Consulte pagos por fechas, clientes,
                    métodos de pago e ingresos registrados.
                </p>

                <span class="btn-reporte">
                    Ver reporte
                </span>

            </article>

        </a>

    </section>

</main>

</body>

</html>