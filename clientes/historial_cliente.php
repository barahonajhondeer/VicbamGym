<?php

require_once("../config/conexion.php");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: clientes.php");
    exit();
}

$id_cliente = (int) $_GET["id"];

/* ===============================
   CONSULTAR CLIENTE
================================ */

$sqlCliente = "SELECT *
               FROM clientes
               WHERE id_cliente = ?";

$stmtCliente = mysqli_prepare($conexion, $sqlCliente);

mysqli_stmt_bind_param(
    $stmtCliente,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmtCliente);

$resultadoCliente = mysqli_stmt_get_result($stmtCliente);
$cliente = mysqli_fetch_assoc($resultadoCliente);

if (!$cliente) {
    echo "<script>
        alert('El cliente no existe.');
        window.location='clientes.php';
    </script>";

    exit();
}

/* ===============================
   CONSULTAR MEMBRESÍAS
================================ */

$sqlMembresias = "SELECT
                    id_membresia,
                    tipo,
                    valor,
                    fecha_inicio,
                    fecha_fin,
                    estado
                  FROM membresias
                  WHERE id_cliente = ?
                  ORDER BY id_membresia DESC";

$stmtMembresias = mysqli_prepare(
    $conexion,
    $sqlMembresias
);

mysqli_stmt_bind_param(
    $stmtMembresias,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmtMembresias);

$resultadoMembresias = mysqli_stmt_get_result(
    $stmtMembresias
);

/* ===============================
   CONSULTAR PAGOS
================================ */

$sqlPagos = "SELECT
                p.id_pago,
                p.valor,
                p.metodo_pago,
                p.fecha_pago,
                m.tipo
             FROM pagos p
             INNER JOIN membresias m
                ON p.id_membresia = m.id_membresia
             WHERE p.id_cliente = ?
             ORDER BY p.fecha_pago DESC,
                      p.id_pago DESC";

$stmtPagos = mysqli_prepare(
    $conexion,
    $sqlPagos
);

mysqli_stmt_bind_param(
    $stmtPagos,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmtPagos);

$resultadoPagos = mysqli_stmt_get_result($stmtPagos);

/* ===============================
   TOTAL PAGADO
================================ */

$sqlTotal = "SELECT
                COALESCE(SUM(valor), 0) AS total
             FROM pagos
             WHERE id_cliente = ?";

$stmtTotal = mysqli_prepare($conexion, $sqlTotal);

mysqli_stmt_bind_param(
    $stmtTotal,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmtTotal);

$resultadoTotal = mysqli_stmt_get_result($stmtTotal);
$datosTotal = mysqli_fetch_assoc($resultadoTotal);

$totalPagado = $datosTotal["total"] ?? 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Historial del cliente | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body class="historial-body">

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
            <a href="clientes.php" class="menu-activo">
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
            <a href="../logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<main class="historial-contenido">

    <section class="historial-encabezado">

        <div>

            <h1>HISTORIAL DEL CLIENTE</h1>

            <p>
                Información completa de membresías y pagos.
            </p>

        </div>

        <a
            href="clientes.php"
            class="btn-volver-reporte">

            ← Volver a clientes

        </a>

    </section>

    <!-- DATOS PERSONALES -->

    <section class="ficha-cliente">

        <div class="avatar-cliente">
            👤
        </div>

        <div class="datos-cliente">

            <h2>
                <?php
                echo htmlspecialchars(
                    $cliente["nombres"] .
                    " " .
                    $cliente["apellidos"]
                );
                ?>
            </h2>

            <div class="datos-cliente-grid">

                <p>
                    <strong>Cédula:</strong>
                    <?php
                    echo htmlspecialchars($cliente["cedula"]);
                    ?>
                </p>

                <p>
                    <strong>Teléfono:</strong>
                    <?php
                    echo htmlspecialchars($cliente["telefono"]);
                    ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?php
                    echo htmlspecialchars($cliente["correo"]);
                    ?>
                </p>

                <p>
                    <strong>Dirección:</strong>
                    <?php
                    echo htmlspecialchars($cliente["direccion"]);
                    ?>
                </p>

                <p>
                    <strong>Fecha de registro:</strong>

                    <?php
                    echo !empty($cliente["fecha_registro"])
                        ? date(
                            "d/m/Y",
                            strtotime($cliente["fecha_registro"])
                        )
                        : "Sin fecha";
                    ?>
                </p>

            </div>

        </div>

        <div class="total-cliente">

            <span>Total pagado</span>

            <strong>
                $<?php
                echo number_format(
                    (float) $totalPagado,
                    2
                );
                ?>
            </strong>

        </div>

    </section>

    <!-- HISTORIAL DE MEMBRESÍAS -->

    <section class="seccion-historial">

        <h2>Historial de membresías</h2>

        <div class="tabla-responsive">

            <table class="tabla-reporte">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                if (
                    mysqli_num_rows($resultadoMembresias) > 0
                ) {
                ?>

                    <?php
                    while (
                        $membresia =
                        mysqli_fetch_assoc($resultadoMembresias)
                    ) {
                    ?>

                        <tr>

                            <td>
                                <?php
                                echo $membresia["id_membresia"];
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $membresia["tipo"]
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    (float) $membresia["valor"],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia["fecha_inicio"]
                                    )
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia["fecha_fin"]
                                    )
                                );
                                ?>
                            </td>

                            <td>

                                <?php
                                if (
                                    $membresia["estado"] ===
                                    "Activa"
                                ) {
                                ?>

                                    <span class="estado-activa">
                                        Activa
                                    </span>

                                <?php } else { ?>

                                    <span class="estado-vencida">
                                        Vencida
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td
                            colspan="6"
                            class="sin-resultados">

                            Este cliente no tiene membresías.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </section>

    <!-- HISTORIAL DE PAGOS -->

    <section class="seccion-historial">

        <h2>Historial de pagos</h2>

        <div class="tabla-responsive">

            <table class="tabla-reporte">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Membresía</th>
                        <th>Valor</th>
                        <th>Método</th>
                        <th>Fecha</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                if (mysqli_num_rows($resultadoPagos) > 0) {
                ?>

                    <?php
                    while (
                        $pago =
                        mysqli_fetch_assoc($resultadoPagos)
                    ) {
                    ?>

                        <tr>

                            <td>
                                <?php echo $pago["id_pago"]; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago["tipo"]
                                );
                                ?>
                            </td>

                            <td>
                                $<?php
                                echo number_format(
                                    (float) $pago["valor"],
                                    2
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $pago["metodo_pago"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $pago["fecha_pago"]
                                    )
                                );
                                ?>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td
                            colspan="5"
                            class="sin-resultados">

                            Este cliente no tiene pagos registrados.

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