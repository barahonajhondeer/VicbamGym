<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   FUNCIÓN PARA CONSULTAS DE CONTEO
========================================= */

function obtenerTotal($conexion, $sql)
{
    $resultado = mysqli_query(
        $conexion,
        $sql
    );

    if (!$resultado) {

        error_log(
            "Error en reporte: " .
            mysqli_error($conexion)
        );

        return 0;
    }

    $fila = mysqli_fetch_assoc(
        $resultado
    );

    return $fila["total"] ?? 0;
}


/* =========================================
   CLIENTES ACTIVOS
========================================= */

$sqlClientes = "
    SELECT
        COUNT(*) AS total
    FROM clientes
    WHERE estado = 'Activo'
";

$totalClientes =
    (int) obtenerTotal(
        $conexion,
        $sqlClientes
    );


/* =========================================
   MEMBRESÍAS ACTIVAS
========================================= */

$sqlActivas = "
    SELECT
        COUNT(*) AS total
    FROM membresias
    WHERE estado = 'Activa'
";

$totalActivas =
    (int) obtenerTotal(
        $conexion,
        $sqlActivas
    );


/* =========================================
   MEMBRESÍAS VENCIDAS
========================================= */

$sqlVencidas = "
    SELECT
        COUNT(*) AS total
    FROM membresias
    WHERE estado = 'Vencida'
";

$totalVencidas =
    (int) obtenerTotal(
        $conexion,
        $sqlVencidas
    );


/* =========================================
   INGRESOS REGISTRADOS
========================================= */

$sqlTotalIngresos = "
    SELECT
        COALESCE(
            SUM(valor),
            0
        ) AS total
    FROM pagos
    WHERE estado = 'Registrado'
";


$resultadoIngresos =
    mysqli_query(
        $conexion,
        $sqlTotalIngresos
    );


$totalIngresos = 0;


if (!$resultadoIngresos) {

    error_log(
        "Error consultando ingresos del reporte: " .
        mysqli_error($conexion)
    );

} else {

    $filaIngresos =
        mysqli_fetch_assoc(
            $resultadoIngresos
        );

    $totalIngresos =
        (float)
        ($filaIngresos["total"] ?? 0);
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
        Reportes | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

</head>

<body class="reportes-body">


<!-- =========================================
     MENÚ SUPERIOR
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

            <a
                href="reportes.php"
                class="menu-activo"
            >
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
     CONTENIDO PRINCIPAL
========================================= -->

<main class="reportes-contenido">


    <!-- =====================================
         ENCABEZADO
    ====================================== -->

    <section class="encabezado-reportes">

        <h1>
            GESTIÓN DE REPORTES
        </h1>

        <p>
            Consulte la información general de clientes,
            membresías y pagos registrados en el sistema.
        </p>

    </section>


    <!-- =====================================
         RESUMEN GENERAL
    ====================================== -->

    <section class="resumen-reportes">


        <!-- CLIENTES -->

        <div class="tarjeta-resumen">

            <h3>
                Clientes activos
            </h3>

            <span>
                <?php
                echo $totalClientes;
                ?>
            </span>

        </div>


        <!-- MEMBRESÍAS ACTIVAS -->

        <div class="tarjeta-resumen">

            <h3>
                Membresías activas
            </h3>

            <span>
                <?php
                echo $totalActivas;
                ?>
            </span>

        </div>


        <!-- MEMBRESÍAS VENCIDAS -->

        <div class="tarjeta-resumen">

            <h3>
                Membresías vencidas
            </h3>

            <span>
                <?php
                echo $totalVencidas;
                ?>
            </span>

        </div>


        <!-- INGRESOS -->

        <div class="tarjeta-resumen">

            <h3>
                Ingresos registrados
            </h3>

            <span>

                $<?php
                echo number_format(
                    $totalIngresos,
                    2
                );
                ?>

            </span>

        </div>

    </section>


    <!-- =====================================
         TIPOS DE REPORTES
    ====================================== -->

    <section class="opciones-reportes">


        <!-- CLIENTES -->

        <a
            href="reporte_cliente.php"
            class="enlace-reporte"
        >

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    👥
                </div>

                <h2>
                    Reporte de clientes
                </h2>

                <p>
                    Consulte la información de los
                    clientes registrados en el gimnasio.
                </p>

                <span class="btn-reporte">
                    Ver reporte
                </span>

            </article>

        </a>


        <!-- MEMBRESÍAS -->

        <a
            href="reporte_membresias.php"
            class="enlace-reporte"
        >

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    💳
                </div>

                <h2>
                    Reporte de membresías
                </h2>

                <p>
                    Consulte membresías activas,
                    vencidas, fechas y tipos de
                    planes registrados.
                </p>

                <span class="btn-reporte">
                    Ver reporte
                </span>

            </article>

        </a>


        <!-- PAGOS -->

        <a
            href="reporte_pagos.php"
            class="enlace-reporte"
        >

            <article class="tarjeta-reporte">

                <div class="icono-reporte">
                    💰
                </div>

                <h2>
                    Reporte de pagos
                </h2>

                <p>
                    Consulte pagos por fechas,
                    clientes, métodos de pago e
                    ingresos registrados.
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