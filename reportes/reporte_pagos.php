<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   FUNCIÓN ESCAPAR
========================================= */

function e($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================
   VALIDAR FECHA
========================================= */

function fechaValida($fecha)
{
    if ($fecha === "") {
        return true;
    }

    $objetoFecha =
        DateTime::createFromFormat(
            "Y-m-d",
            $fecha
        );

    return
        $objetoFecha !== false &&
        $objetoFecha->format("Y-m-d") === $fecha;
}


/* =========================================
   RECIBIR FILTROS
========================================= */

$buscar = trim(
    $_GET["buscar"] ?? ""
);

$fecha_inicio = trim(
    $_GET["fecha_inicio"] ?? ""
);

$fecha_fin = trim(
    $_GET["fecha_fin"] ?? ""
);

$metodoFiltro = trim(
    $_GET["metodo"] ?? ""
);


/* =========================================
   LIMITAR BÚSQUEDA
========================================= */

if (
    mb_strlen($buscar) > 100
) {

    $buscar = mb_substr(
        $buscar,
        0,
        100
    );
}


/* =========================================
   VALIDAR FECHAS
========================================= */

if (
    !fechaValida(
        $fecha_inicio
    )
) {

    $fecha_inicio = "";
}


if (
    !fechaValida(
        $fecha_fin
    )
) {

    $fecha_fin = "";
}


/* =========================================
   VALIDAR RANGO
========================================= */

if (
    $fecha_inicio !== "" &&
    $fecha_fin !== "" &&
    $fecha_inicio > $fecha_fin
) {

    $temporal =
        $fecha_inicio;

    $fecha_inicio =
        $fecha_fin;

    $fecha_fin =
        $temporal;
}


/* =========================================
   MÉTODOS PERMITIDOS
========================================= */

$metodosPermitidos = [
    "",
    "Efectivo",
    "Transferencia"
];


if (
    !in_array(
        $metodoFiltro,
        $metodosPermitidos,
        true
    )
) {

    $metodoFiltro = "";
}


/* =========================================
   CONSULTA
========================================= */

$sql = "
    SELECT
        p.id_pago,
        p.valor,
        p.metodo_pago,
        p.fecha_pago,

        c.cedula,
        c.nombres,
        c.apellidos,

        m.tipo

    FROM pagos p

    INNER JOIN clientes c
        ON p.id_cliente = c.id_cliente

    INNER JOIN membresias m
        ON p.id_membresia = m.id_membresia

    WHERE
        p.estado = 'Registrado'

        AND (
            c.cedula LIKE ?
            OR c.nombres LIKE ?
            OR c.apellidos LIKE ?
            OR CONCAT(
                c.nombres,
                ' ',
                c.apellidos
            ) LIKE ?
        )

        AND (
            ? = ''
            OR p.fecha_pago >= ?
        )

        AND (
            ? = ''
            OR p.fecha_pago <= ?
        )

        AND (
            ? = ''
            OR p.metodo_pago = ?
        )

    ORDER BY
        p.fecha_pago DESC,
        p.id_pago DESC
";


/* =========================================
   TEXTO DE BÚSQUEDA
========================================= */

$textoBuscar =
    "%" . $buscar . "%";


/* =========================================
   PREPARAR CONSULTA
========================================= */

$stmt =
    mysqli_prepare(
        $conexion,
        $sql
    );


if (!$stmt) {

    error_log(
        "Error preparando reporte de pagos: " .
        mysqli_error($conexion)
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el reporte de pagos."
        )
    );

    exit();
}


/* =========================================
   PARÁMETROS
========================================= */

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssss",

    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,

    $fecha_inicio,
    $fecha_inicio,

    $fecha_fin,
    $fecha_fin,

    $metodoFiltro,
    $metodoFiltro
);


/* =========================================
   EJECUTAR CONSULTA
========================================= */

if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Error ejecutando reporte de pagos: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el reporte de pagos."
        )
    );

    exit();
}


/* =========================================
   OBTENER RESULTADOS
========================================= */

$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


if (!$resultado) {

    error_log(
        "No se pudo obtener el resultado del reporte de pagos."
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo obtener la información de los pagos."
        )
    );

    exit();
}


/* =========================================
   GUARDAR RESULTADOS Y TOTALES
========================================= */

$pagos = [];

$totalPagos = 0;

$totalRecaudado = 0.00;


while (
    $fila =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $pagos[] =
        $fila;


    $totalPagos++;


    $totalRecaudado +=
        (float)
        $fila["valor"];
}


/* =========================================
   CERRAR STATEMENT
========================================= */

mysqli_stmt_close(
    $stmt
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Reporte de Pagos | VICBAMGYM
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

        <?php } ?>

        <li>
            <a href="../logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<?php
require_once("../config/notificaciones.php");
?>

<!-- =================================
     CONTENIDO
================================= -->

<main class="reporte-detalle-contenido">

    <!-- ENCABEZADO -->

    <section class="encabezado-reporte-detalle">

        <div>

            <h1>
                REPORTE DE PAGOS
            </h1>

            <p>
                Consulte pagos por cliente,
                rango de fechas y método de pago.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_pagos_pdf.php?buscar=<?php
                    echo urlencode($buscar);
                ?>&fecha_inicio=<?php
                    echo urlencode($fecha_inicio);
                ?>&fecha_fin=<?php
                    echo urlencode($fecha_fin);
                ?>&metodo=<?php
                    echo urlencode($metodoFiltro);
                ?>"
                class="btn-pdf">

                📄 Exportar PDF

            </a>

        </div>

    </section>

    <!-- =================================
         FILTROS
    ================================= -->

    <section class="filtro-pagos-reporte">

        <form
            method="GET"
            action="reporte_pagos.php">

            <!-- CLIENTE -->

            <div class="campo-filtro">

                <label for="buscar">
                    Cliente
                </label>

                <input
                    type="text"
                    name="buscar"
                    id="buscar"
                    value="<?php
                        echo htmlspecialchars($buscar);
                    ?>"
                    placeholder="Nombre o cédula"
                    autocomplete="off">

            </div>

            <!-- FECHA INICIAL -->

            <div class="campo-filtro">

                <label for="fecha_inicio">
                    Fecha inicial
                </label>

                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    value="<?php
                        echo htmlspecialchars(
                            $fecha_inicio
                        );
                    ?>">

            </div>

            <!-- FECHA FINAL -->

            <div class="campo-filtro">

                <label for="fecha_fin">
                    Fecha final
                </label>

                <input
                    type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    value="<?php
                        echo htmlspecialchars(
                            $fecha_fin
                        );
                    ?>">

            </div>

            <!-- MÉTODO -->

            <div class="campo-filtro">

                <label for="metodo">
                    Método de pago
                </label>

                <select
                    name="metodo"
                    id="metodo">

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="Efectivo"
                        <?php
                        echo $metodoFiltro === "Efectivo"
                            ? "selected"
                            : "";
                        ?>>

                        Efectivo

                    </option>

                    <option
                        value="Transferencia"
                        <?php
                        echo $metodoFiltro === "Transferencia"
                            ? "selected"
                            : "";
                        ?>>

                        Transferencia

                    </option>

                    <option
                        value="Tarjeta"
                        <?php
                        echo $metodoFiltro === "Tarjeta"
                            ? "selected"
                            : "";
                        ?>>

                        Tarjeta

                    </option>

                </select>

            </div>

            <!-- BOTONES -->

            <div class="acciones-filtro">

                <button
                    type="submit"
                    class="btn-buscar-reporte">

                    🔍 Buscar

                </button>

                <a
                    href="reporte_pagos.php"
                    class="btn-limpiar-reporte">

                    Limpiar

                </a>

            </div>

        </form>

    </section>

    <!-- =================================
         RESUMEN
    ================================= -->

    <section class="resumen-pagos-reporte">

        <div class="tarjeta-total-reporte">

            <span>
                Pagos encontrados
            </span>

            <strong>
                <?php
                echo $totalPagos;
                ?>
            </strong>

        </div>

        <div class="tarjeta-ingresos-reporte">

            <span>
                Total recaudado
            </span>

            <strong>

                $<?php
                echo number_format(
                    $totalRecaudado,
                    2
                );
                ?>

            </strong>

        </div>

    </section>

    <!-- =================================
         FILTROS ACTIVOS
    ================================= -->

    <?php

    if (
        $buscar !== "" ||
        $fecha_inicio !== "" ||
        $fecha_fin !== "" ||
        $metodoFiltro !== ""
    ) {

    ?>

        <div class="resultado-filtro-reporte">

            Filtros aplicados:

            <?php if ($buscar !== "") { ?>

                <strong>
                    Cliente:
                    <?php
                    echo htmlspecialchars(
                        $buscar
                    );
                    ?>
                </strong>

            <?php } ?>

            <?php if ($fecha_inicio !== "") { ?>

                <strong>
                    | Desde:
                    <?php
                    echo date(
                        "d/m/Y",
                        strtotime(
                            $fecha_inicio
                        )
                    );
                    ?>
                </strong>

            <?php } ?>

            <?php if ($fecha_fin !== "") { ?>

                <strong>
                    | Hasta:
                    <?php
                    echo date(
                        "d/m/Y",
                        strtotime(
                            $fecha_fin
                        )
                    );
                    ?>
                </strong>

            <?php } ?>

            <?php if ($metodoFiltro !== "") { ?>

                <strong>
                    | Método:
                    <?php
                    echo htmlspecialchars(
                        $metodoFiltro
                    );
                    ?>
                </strong>

            <?php } ?>

        </div>

    <?php } ?>

    <!-- =================================
         TABLA
    ================================= -->

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

                <?php

                if ($totalPagos > 0) {

                    foreach ($pagos as $pago) {

                ?>

                        <tr>

                            <!-- ID -->

                            <td>
                                <?php
                                echo $pago[
                                    "id_pago"
                                ];
                                ?>
                            </td>

                            <!-- CÉDULA -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $pago["cedula"]
                                );
                                ?>

                            </td>

                            <!-- CLIENTE -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $pago["nombres"] .
                                    " " .
                                    $pago["apellidos"]
                                );

                                ?>

                            </td>

                            <!-- MEMBRESÍA -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $pago["tipo"]
                                );
                                ?>

                            </td>

                            <!-- VALOR -->

                            <td>

                                $<?php

                                echo number_format(
                                    (float)
                                    $pago["valor"],
                                    2
                                );

                                ?>

                            </td>

                            <!-- MÉTODO -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $pago[
                                        "metodo_pago"
                                    ]
                                );
                                ?>

                            </td>

                            <!-- FECHA -->

                            <td>

                                <?php

                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $pago[
                                            "fecha_pago"
                                        ]
                                    )
                                );

                                ?>

                            </td>

                        </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td
                            colspan="7"
                            class="sin-resultados">

                            No se encontraron pagos
                            con los filtros seleccionados.

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