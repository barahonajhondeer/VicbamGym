<?php

require_once("../config/conexion.php");

/* =================================
   RECIBIR FILTROS
================================= */

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$condicion = "";
$parametros = [];
$tipos = "";

if (!empty($fecha_inicio) && !empty($fecha_fin)) {

    $condicion = " WHERE p.fecha_pago BETWEEN ? AND ? ";
    $parametros[] = $fecha_inicio;
    $parametros[] = $fecha_fin;
    $tipos = "ss";
}

/* =================================
   CONSULTAR PAGOS
================================= */

$sql = "SELECT
            p.id_pago,
            c.cedula,
            c.nombres,
            c.apellidos,
            m.tipo,
            p.valor,
            p.metodo_pago,
            p.fecha_pago
        FROM pagos p
        INNER JOIN clientes c
            ON p.id_cliente = c.id_cliente
        INNER JOIN membresias m
            ON p.id_membresia = m.id_membresia
        $condicion
        ORDER BY p.fecha_pago DESC,
                 p.id_pago DESC";

$stmt = mysqli_prepare($conexion, $sql);

if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        $parametros[0],
        $parametros[1]
    );
}

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {
    die(
        "Error al consultar los pagos: " .
        mysqli_error($conexion)
    );
}

$totalPagos = mysqli_num_rows($resultado);

/* =================================
   TOTAL RECAUDADO
================================= */

$sqlTotal = "SELECT
                COALESCE(SUM(p.valor), 0) AS total
             FROM pagos p
             $condicion";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

if (!empty($parametros)) {

    mysqli_stmt_bind_param(
        $stmtTotal,
        $tipos,
        $parametros[0],
        $parametros[1]
    );
}

mysqli_stmt_execute($stmtTotal);

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);
$datosTotal = mysqli_fetch_assoc($resultadoTotal);

$totalRecaudado = $datosTotal['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reporte de pagos | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body class="reportes-body">

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

<main class="reporte-detalle-contenido">

    <section class="encabezado-reporte-detalle">

        <div>

            <h1>REPORTE DE PAGOS</h1>

            <p>
                Consulte los pagos registrados y filtre
                los resultados por rango de fechas.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_pagos_pdf.php<?php
                    echo !empty($fecha_inicio) &&
                         !empty($fecha_fin)
                        ? '?fecha_inicio=' .
                          urlencode($fecha_inicio) .
                          '&fecha_fin=' .
                          urlencode($fecha_fin)
                        : '';
                ?>"
                class="btn-pdf">

                📄 Exportar PDF

            </a>

        </div>

    </section>

    <!-- FILTROS -->

    <section class="filtro-pagos-reporte">

        <form method="GET" action="reporte_pagos.php">

            <div class="campo-filtro">

                <label>Fecha inicial</label>

                <input
                    type="date"
                    name="fecha_inicio"
                    value="<?php
                        echo htmlspecialchars($fecha_inicio);
                    ?>">

            </div>

            <div class="campo-filtro">

                <label>Fecha final</label>

                <input
                    type="date"
                    name="fecha_fin"
                    value="<?php
                        echo htmlspecialchars($fecha_fin);
                    ?>">

            </div>

            <div class="acciones-filtro">

                <button
                    type="submit"
                    class="btn-buscar-reporte">

                    Buscar

                </button>

                <a
                    href="reporte_pagos.php"
                    class="btn-limpiar-reporte">

                    Limpiar

                </a>

            </div>

        </form>

    </section>

    <!-- RESUMEN -->

    <section class="resumen-pagos-reporte">

        <div class="tarjeta-total-reporte">

            <span>Pagos encontrados</span>

            <strong>
                <?php echo $totalPagos; ?>
            </strong>

        </div>

        <div class="tarjeta-ingresos-reporte">

            <span>Total recaudado</span>

            <strong>
                $<?php
                echo number_format(
                    (float) $totalRecaudado,
                    2
                );
                ?>
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
                        <th>Cliente</th>
                        <th>Membresía</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Fecha</th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($totalPagos > 0) { ?>

                    <?php
                    while (
                        $pago = mysqli_fetch_assoc($resultado)
                    ) {
                    ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago['id_pago']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago['cedula']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago['nombres'] .
                                    " " .
                                    $pago['apellidos']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago['tipo']
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    (float) $pago['valor'],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago['metodo_pago']
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $pago['fecha_pago']
                                    )
                                );
                                ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td
                            colspan="7"
                            class="sin-resultados">

                            No existen pagos para el periodo
                            seleccionado.

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