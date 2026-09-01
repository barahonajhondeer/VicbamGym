<?php

require_once("config/verificar_sesion.php");
require_once("config/conexion.php");


/* =========================================
   TOTAL CLIENTES ACTIVOS
========================================= */

$sqlClientes = "
    SELECT COUNT(*) AS total
    FROM clientes
    WHERE estado = 'Activo'
";

$resultadoClientes = mysqli_query(
    $conexion,
    $sqlClientes
);

$filaClientes = mysqli_fetch_assoc(
    $resultadoClientes
);

$totalClientes =
    (int) $filaClientes["total"];


/* =========================================
   MEMBRESÍAS ACTIVAS
========================================= */

$sqlActivas = "
    SELECT COUNT(*) AS total
    FROM membresias
    WHERE fecha_fin >= CURDATE()
";

$resultadoActivas = mysqli_query(
    $conexion,
    $sqlActivas
);

$filaActivas = mysqli_fetch_assoc(
    $resultadoActivas
);

$totalActivas =
    (int) $filaActivas["total"];


/* =========================================
   MEMBRESÍAS VENCIDAS
========================================= */

$sqlVencidas = "
    SELECT COUNT(*) AS total
    FROM membresias
    WHERE fecha_fin < CURDATE()
";

$resultadoVencidas = mysqli_query(
    $conexion,
    $sqlVencidas
);

$filaVencidas = mysqli_fetch_assoc(
    $resultadoVencidas
);

$totalVencidas =
    (int) $filaVencidas["total"];


/* =========================================
   INGRESOS DEL MES

   IMPORTANTE:
   SOLO PAGOS REGISTRADOS
========================================= */

$sqlIngresosMes = "
    SELECT
        COALESCE(SUM(valor), 0) AS total
    FROM pagos

    WHERE
        MONTH(fecha_pago) =
        MONTH(CURDATE())

    AND
        YEAR(fecha_pago) =
        YEAR(CURDATE())

    AND estado = 'Registrado'
";

$resultadoIngresosMes =
    mysqli_query(
        $conexion,
        $sqlIngresosMes
    );

$filaIngresosMes =
    mysqli_fetch_assoc(
        $resultadoIngresosMes
    );

$ingresosMes =
    (float) $filaIngresosMes["total"];


/* =========================================
   PAGOS REALIZADOS HOY
========================================= */

$sqlPagosHoy = "
    SELECT COUNT(*) AS total
    FROM pagos

    WHERE fecha_pago = CURDATE()

    AND estado = 'Registrado'
";

$resultadoPagosHoy =
    mysqli_query(
        $conexion,
        $sqlPagosHoy
    );

$filaPagosHoy =
    mysqli_fetch_assoc(
        $resultadoPagosHoy
    );

$pagosHoy =
    (int) $filaPagosHoy["total"];


/* =========================================
   INGRESOS POR MES
   PARA CHART.JS
========================================= */

$sqlGrafico = "
    SELECT

        MONTH(fecha_pago) AS mes,

        COALESCE(
            SUM(valor),
            0
        ) AS total

    FROM pagos

    WHERE
        YEAR(fecha_pago) =
        YEAR(CURDATE())

    AND estado = 'Registrado'

    GROUP BY MONTH(fecha_pago)

    ORDER BY MONTH(fecha_pago)
";

$resultadoGrafico =
    mysqli_query(
        $conexion,
        $sqlGrafico
    );


/* =========================================
   INICIALIZAR LOS 12 MESES
========================================= */

$ingresosPorMes = array_fill(
    1,
    12,
    0
);


while (
    $filaGrafico =
    mysqli_fetch_assoc(
        $resultadoGrafico
    )
) {

    $mes =
        (int) $filaGrafico["mes"];

    $ingresosPorMes[$mes] =
        (float) $filaGrafico["total"];
}


/* =========================================
   MEMBRESÍAS PRÓXIMAS A VENCER
========================================= */

$sqlProximas = "
    SELECT

        m.id_membresia,
        m.tipo,
        m.fecha_fin,

        c.nombres,
        c.apellidos

    FROM membresias m

    INNER JOIN clientes c
        ON c.id_cliente = m.id_cliente

    WHERE
        m.fecha_fin >= CURDATE()

    AND
        m.fecha_fin <=
        DATE_ADD(
            CURDATE(),
            INTERVAL 5 DAY
        )

    AND c.estado = 'Activo'

    ORDER BY m.fecha_fin ASC
";

$resultadoProximas =
    mysqli_query(
        $conexion,
        $sqlProximas
    );

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
            <a href="usuarios/usuarios.php">
                👨‍💼 Usuarios
            </a>
        </li>

        <li>
            <a href="logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<?php
require_once("config/notificaciones.php");
?>

<main class="dashboard-contenido">

    <section class="dashboard-encabezado">

        <h1>PANEL DE ADMINISTRACIÓN</h1>

        <p>
            Resumen general y accesos principales del sistema VICBAMGYM.
        </p>

        <p class="usuario-sesion">

Bienvenido:

<strong>
    <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
</strong>

| Rol:

<strong>
    <?php echo htmlspecialchars($_SESSION["rol"]); ?>
</strong>

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