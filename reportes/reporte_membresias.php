<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   ACTUALIZAR MEMBRESÍAS VENCIDAS
========================================= */

$sqlActualizar = "
    UPDATE membresias
    SET estado = 'Vencida'
    WHERE fecha_fin < CURDATE()
    AND estado = 'Activa'
";


if (
    !mysqli_query(
        $conexion,
        $sqlActualizar
    )
) {

    error_log(
        "Error actualizando estados de membresías en reporte: " .
        mysqli_error($conexion)
    );
}


/* =========================================
   RECIBIR FILTROS
========================================= */

$buscar = trim(
    $_GET["buscar"] ?? ""
);

$tipoFiltro = trim(
    $_GET["tipo"] ?? ""
);

$estadoFiltro = trim(
    $_GET["estado"] ?? ""
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
   TIPOS PERMITIDOS
========================================= */

$tiposPermitidos = [
    "",
    "Mensual",
    "Trimestral",
    "Semestral",
    "Anual"
];


if (
    !in_array(
        $tipoFiltro,
        $tiposPermitidos,
        true
    )
) {

    $tipoFiltro = "";
}


/* =========================================
   ESTADOS PERMITIDOS
========================================= */

$estadosPermitidos = [
    "",
    "Activa",
    "Vencida"
];


if (
    !in_array(
        $estadoFiltro,
        $estadosPermitidos,
        true
    )
) {

    $estadoFiltro = "";
}


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
   CONSULTA FILTRADA
========================================= */

$sql = "
    SELECT
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

    WHERE
        (
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
            OR m.tipo = ?
        )

        AND (
            ? = ''
            OR m.estado = ?
        )

    ORDER BY
        m.id_membresia DESC
";


/* =========================================
   PREPARAR BÚSQUEDA
========================================= */

$textoBuscar =
    "%" . $buscar . "%";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando reporte de membresías: " .
        mysqli_error($conexion)
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el reporte de membresías."
        )
    );

    exit();
}


/* =========================================
   ASIGNAR PARÁMETROS
========================================= */

mysqli_stmt_bind_param(
    $stmt,
    "ssssssss",

    $textoBuscar,
    $textoBuscar,
    $textoBuscar,
    $textoBuscar,

    $tipoFiltro,
    $tipoFiltro,

    $estadoFiltro,
    $estadoFiltro
);


/* =========================================
   EJECUTAR
========================================= */

if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Error ejecutando reporte de membresías: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo generar el reporte de membresías."
        )
    );

    exit();
}


/* =========================================
   RESULTADO
========================================= */

$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


if (!$resultado) {

    error_log(
        "No se pudo obtener el resultado del reporte de membresías."
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: reportes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo obtener la información de las membresías."
        )
    );

    exit();
}


/* =========================================
   GUARDAR RESULTADOS
========================================= */

$membresias = [];

$totalMembresias = 0;
$totalActivas = 0;
$totalVencidas = 0;


while (
    $fila =
    mysqli_fetch_assoc(
        $resultado
    )
) {

    $membresias[] =
        $fila;


    $totalMembresias++;


    if (
        $fila["estado"] ===
        "Activa"
    ) {

        $totalActivas++;
    }


    if (
        $fila["estado"] ===
        "Vencida"
    ) {

        $totalVencidas++;
    }
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
        Reporte de Membresías | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body class="reportes-body">

<!-- =================================
     MENÚ
================================= -->

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
                REPORTE DE MEMBRESÍAS
            </h1>

            <p>
                Consulte las membresías registradas
                según cliente, tipo o estado.
            </p>

        </div>

        <div class="acciones-reporte">

            <a
                href="reportes.php"
                class="btn-volver-reporte">

                ← Volver

            </a>

            <a
                href="exportar_membresias_pdf.php?buscar=<?php
                    echo urlencode($buscar);
                ?>&tipo=<?php
                    echo urlencode($tipoFiltro);
                ?>&estado=<?php
                    echo urlencode($estadoFiltro);
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
            action="reporte_membresias.php">

            <!-- BUSCAR CLIENTE -->

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

            <!-- TIPO -->

            <div class="campo-filtro">

                <label for="tipo">
                    Tipo
                </label>

                <select
                    name="tipo"
                    id="tipo">

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="Mensual"
                        <?php
                        echo $tipoFiltro === "Mensual"
                            ? "selected"
                            : "";
                        ?>>

                        Mensual

                    </option>

                    <option
                        value="Trimestral"
                        <?php
                        echo $tipoFiltro === "Trimestral"
                            ? "selected"
                            : "";
                        ?>>

                        Trimestral

                    </option>

                    <option
                        value="Semestral"
                        <?php
                        echo $tipoFiltro === "Semestral"
                            ? "selected"
                            : "";
                        ?>>

                        Semestral

                    </option>

                    <option
                        value="Anual"
                        <?php
                        echo $tipoFiltro === "Anual"
                            ? "selected"
                            : "";
                        ?>>

                        Anual

                    </option>

                </select>

            </div>

            <!-- ESTADO -->

            <div class="campo-filtro">

                <label for="estado">
                    Estado
                </label>

                <select
                    name="estado"
                    id="estado">

                    <option value="">
                        Todos
                    </option>

                    <option
                        value="Activa"
                        <?php
                        echo $estadoFiltro === "Activa"
                            ? "selected"
                            : "";
                        ?>>

                        Activa

                    </option>

                    <option
                        value="Vencida"
                        <?php
                        echo $estadoFiltro === "Vencida"
                            ? "selected"
                            : "";
                        ?>>

                        Vencida

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
                    href="reporte_membresias.php"
                    class="btn-limpiar-reporte">

                    Limpiar

                </a>

            </div>

        </form>

    </section>

    <!-- =================================
         CONTADORES
    ================================= -->

    <section class="resumen-membresias-reporte">

        <!-- TOTAL -->

        <div class="tarjeta-total-reporte">

            <span>
                Total de membresías
            </span>

            <strong>
                <?php
                echo $totalMembresias;
                ?>
            </strong>

        </div>

        <!-- ACTIVAS -->

        <div class="tarjeta-estado-reporte activa">

            <span>
                Membresías activas
            </span>

            <strong>
                <?php
                echo $totalActivas;
                ?>
            </strong>

        </div>

        <!-- VENCIDAS -->

        <div class="tarjeta-estado-reporte vencida">

            <span>
                Membresías vencidas
            </span>

            <strong>
                <?php
                echo $totalVencidas;
                ?>
            </strong>

        </div>

    </section>

    <!-- MOSTRAR FILTROS ACTIVOS -->

    <?php

    if (
        $buscar !== "" ||
        $tipoFiltro !== "" ||
        $estadoFiltro !== ""
    ) {

    ?>

        <div class="resultado-filtro-reporte">

            Filtros aplicados:

            <?php if ($buscar !== "") { ?>

                <strong>
                    Cliente:
                    <?php
                    echo htmlspecialchars($buscar);
                    ?>
                </strong>

            <?php } ?>

            <?php if ($tipoFiltro !== "") { ?>

                <strong>
                    | Tipo:
                    <?php
                    echo htmlspecialchars($tipoFiltro);
                    ?>
                </strong>

            <?php } ?>

            <?php if ($estadoFiltro !== "") { ?>

                <strong>
                    | Estado:
                    <?php
                    echo htmlspecialchars($estadoFiltro);
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

                        <th>Tipo</th>

                        <th>Valor</th>

                        <th>Fecha inicio</th>

                        <th>Fecha fin</th>

                        <th>Estado</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if ($totalMembresias > 0) {

                    foreach (
                        $membresias as $membresia
                    ) {

                ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <?php
                                echo $membresia[
                                    "id_membresia"
                                ];
                                ?>

                            </td>

                            <!-- CÉDULA -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $membresia["cedula"]
                                );
                                ?>

                            </td>

                            <!-- CLIENTE -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $membresia["nombres"] .
                                    " " .
                                    $membresia["apellidos"]
                                );

                                ?>

                            </td>

                            <!-- TIPO -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $membresia["tipo"]
                                );
                                ?>

                            </td>

                            <!-- VALOR -->

                            <td>

                                $<?php
                                echo number_format(
                                    (float)
                                    $membresia["valor"],
                                    2
                                );
                                ?>

                            </td>

                            <!-- INICIO -->

                            <td>

                                <?php

                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia[
                                            "fecha_inicio"
                                        ]
                                    )
                                );

                                ?>

                            </td>

                            <!-- FIN -->

                            <td>

                                <?php

                                echo date(
                                    "d/m/Y",
                                    strtotime(
                                        $membresia[
                                            "fecha_fin"
                                        ]
                                    )
                                );

                                ?>

                            </td>

                            <!-- ESTADO -->

                            <td>

                                <?php

                                if (
                                    $membresia[
                                        "estado"
                                    ] === "Activa"
                                ) {

                                ?>

                                    <span
                                        class="estado-activa">

                                        🟢 Activa

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span
                                        class="estado-vencida">

                                        🔴 Vencida

                                    </span>

                                <?php } ?>

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

                            No se encontraron
                            membresías con los filtros
                            seleccionados.

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