<?php

require_once("config/verificar_sesion.php");
require_once("config/conexion.php");


/* =========================================
   CLIENTES ACTIVOS
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
   SOLO PAGOS REGISTRADOS
========================================= */

$sqlIngresosMes = "
    SELECT
        COALESCE(
            SUM(valor),
            0
        ) AS total
    FROM pagos
    WHERE
        MONTH(fecha_pago) =
        MONTH(CURDATE())
    AND
        YEAR(fecha_pago) =
        YEAR(CURDATE())
    AND
        estado = 'Registrado'
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
    AND
        estado = 'Registrado'
    GROUP BY
        MONTH(fecha_pago)
    ORDER BY
        MONTH(fecha_pago)
";

$resultadoGrafico =
    mysqli_query(
        $conexion,
        $sqlGrafico
    );


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
   PRÓXIMAS A VENCER
   5 DÍAS
========================================= */

$sqlProximas = "
    SELECT
        m.id_membresia,
        m.tipo,
        m.fecha_fin,

        c.cedula,
        c.nombres,
        c.apellidos

    FROM membresias m

    INNER JOIN clientes c
        ON c.id_cliente =
        m.id_cliente

    WHERE
        m.fecha_fin >= CURDATE()

    AND
        m.fecha_fin <=
        DATE_ADD(
            CURDATE(),
            INTERVAL 5 DAY
        )

    AND
        c.estado = 'Activo'

    ORDER BY
        m.fecha_fin ASC
";

$resultadoProximas =
    mysqli_query(
        $conexion,
        $sqlProximas
    );


/* =========================================
   TOTAL PRÓXIMAS A VENCER
========================================= */

$totalProximas =
    mysqli_num_rows(
        $resultadoProximas
    );

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
        Dashboard | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="assets/css/styles.css"
    >

    <!-- CHART.JS -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>


<!-- =========================================
     MENÚ
========================================= -->

<nav class="navbar">

    <div class="logo-menu">

        <h2>
            VICBAMGYM
        </h2>

    </div>


    <ul class="menu">

        <li>

            <a
                href="dashboard.php"
                class="menu-activo"
            >
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


        <?php

        if (
            isset($_SESSION["rol"]) &&
            $_SESSION["rol"] === "Administrador"
        ) {

        ?>

            <li>

                <a href="usuarios/usuarios.php">
                    👨‍💼 Usuarios
                </a>

            </li>

        <?php

        }

        ?>


        <li>

            <a href="logout.php">
                🚪 Salir
            </a>

        </li>

    </ul>

</nav>



<!-- =========================================
     CONTENIDO PRINCIPAL
========================================= -->

<div class="dashboard-container">


    <!-- =====================================
         BIENVENIDA
    ====================================== -->

    <div class="dashboard-bienvenida">

        <div>

            <h1>
                Panel de control
            </h1>


            <p>

                Bienvenido,

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["nombre"] ??
                        $_SESSION["usuario"] ??
                        "Usuario"
                    );

                    ?>

                </strong>

            </p>


            <small>

                Rol:

                <?php

                echo htmlspecialchars(
                    $_SESSION["rol"] ?? ""
                );

                ?>

            </small>

        </div>


        <!-- =================================
             RESPALDO SOLO ADMIN
        ================================== -->

        <?php

        if (
            isset($_SESSION["rol"]) &&
            $_SESSION["rol"] === "Administrador"
        ) {

        ?>

            <div class="dashboard-respaldo">

                <a
                    href="respaldos/generar_respaldo.php"
                    class="btn-respaldo"
                >

                    💾 Descargar respaldo

                </a>

            </div>

        <?php

        }

        ?>

    </div>



    <!-- =====================================
         TARJETAS
    ====================================== -->

    <div class="dashboard-cards">


        <!-- CLIENTES -->

        <div class="dashboard-card">

            <div class="card-icono">
                👥
            </div>

            <div>

                <span>
                    Clientes activos
                </span>

                <h2>

                    <?php
                    echo $totalClientes;
                    ?>

                </h2>

            </div>

        </div>



        <!-- MEMBRESÍAS ACTIVAS -->

        <div class="dashboard-card">

            <div class="card-icono">
                ✅
            </div>

            <div>

                <span>
                    Membresías activas
                </span>

                <h2>

                    <?php
                    echo $totalActivas;
                    ?>

                </h2>

            </div>

        </div>



        <!-- VENCIDAS -->

        <div class="dashboard-card">

            <div class="card-icono">
                ⚠️
            </div>

            <div>

                <span>
                    Membresías vencidas
                </span>

                <h2>

                    <?php
                    echo $totalVencidas;
                    ?>

                </h2>

            </div>

        </div>



        <!-- INGRESOS MES -->

        <div class="dashboard-card">

            <div class="card-icono">
                💵
            </div>

            <div>

                <span>
                    Ingresos del mes
                </span>

                <h2>

                    $

                    <?php

                    echo number_format(
                        $ingresosMes,
                        2
                    );

                    ?>

                </h2>

            </div>

        </div>



        <!-- PAGOS HOY -->

        <div class="dashboard-card">

            <div class="card-icono">
                💰
            </div>

            <div>

                <span>
                    Pagos realizados hoy
                </span>

                <h2>

                    <?php
                    echo $pagosHoy;
                    ?>

                </h2>

            </div>

        </div>


    </div>



    <!-- =====================================
         SEGUNDA FILA
    ====================================== -->

    <div class="dashboard-grid">


        <!-- =================================
             GRÁFICO
        ================================== -->

        <div class="dashboard-panel">

            <h2>
                Ingresos mensuales
            </h2>


            <div class="grafico-dashboard">

                <canvas
                    id="graficoIngresos"
                >
                </canvas>

            </div>

        </div>



        <!-- =================================
             PRÓXIMAS A VENCER
        ================================== -->

        <div class="dashboard-panel">

            <div class="panel-titulo">

                <h2>
                    Próximas a vencer
                </h2>


                <span class="badge-alerta">

                    <?php
                    echo $totalProximas;
                    ?>

                </span>

            </div>


            <div class="lista-proximas">


                <?php

                if (
                    $totalProximas === 0
                ) {

                ?>

                    <div class="sin-datos-dashboard">

                        No existen membresías
                        próximas a vencer.

                    </div>

                <?php

                } else {

                    while (
                        $fila =
                        mysqli_fetch_assoc(
                            $resultadoProximas
                        )
                    ) {

                        $fechaFin =
                            new DateTime(
                                $fila["fecha_fin"]
                            );

                        $hoy =
                            new DateTime(
                                date("Y-m-d")
                            );

                        $diferencia =
                            $hoy->diff(
                                $fechaFin
                            );

                        $dias =
                            (int)
                            $diferencia->days;

                ?>

                        <div class="item-proxima">


                            <div class="datos-cliente-dashboard">

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $fila["nombres"] .
                                        " " .
                                        $fila["apellidos"]
                                    );

                                    ?>

                                </strong>


                                <small>

                                    C.I.

                                    <?php

                                    echo htmlspecialchars(
                                        $fila["cedula"]
                                    );

                                    ?>

                                </small>

                            </div>


                            <div class="datos-membresia-dashboard">

                                <span>

                                    <?php

                                    echo htmlspecialchars(
                                        $fila["tipo"]
                                    );

                                    ?>

                                </span>


                                <small>

                                    Vence:

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $fila["fecha_fin"]
                                        )
                                    );

                                    ?>

                                </small>

                            </div>


                            <div class="dias-restantes">

                                <?php

                                if ($dias === 0) {

                                    echo "Hoy";

                                } elseif (
                                    $dias === 1
                                ) {

                                    echo "1 día";

                                } else {

                                    echo
                                        $dias .
                                        " días";
                                }

                                ?>

                            </div>

                        </div>

                <?php

                    }

                }

                ?>

            </div>

        </div>


    </div>

</div>



<!-- =========================================
     CHART.JS
========================================= -->

<script>

const contexto =
    document.getElementById(
        "graficoIngresos"
    );


const ingresosMensuales = <?php

    echo json_encode(
        array_values(
            $ingresosPorMes
        )
    );

?>;


new Chart(
    contexto,
    {

        type: "bar",

        data: {

            labels: [

                "Ene",
                "Feb",
                "Mar",
                "Abr",
                "May",
                "Jun",
                "Jul",
                "Ago",
                "Sep",
                "Oct",
                "Nov",
                "Dic"

            ],

            datasets: [

                {

                    label:
                        "Ingresos",

                    data:
                        ingresosMensuales,

                    backgroundColor:
                        "rgba(255, 0, 0, 0.75)",

                    borderColor:
                        "#ff0000",

                    borderWidth:
                        1,

                    borderRadius:
                        5

                }

            ]

        },


        options: {

            responsive: true,

            maintainAspectRatio: false,


            plugins: {

                legend: {

                    labels: {

                        color:
                            "#ffffff"

                    }

                }

            },


            scales: {

                x: {

                    ticks: {

                        color:
                            "#cccccc"

                    },

                    grid: {

                        color:
                            "rgba(255,255,255,.05)"

                    }

                },


                y: {

                    beginAtZero: true,

                    ticks: {

                        color:
                            "#cccccc",

                        callback:
                            function(value) {

                                return "$" + value;

                            }

                    },

                    grid: {

                        color:
                            "rgba(255,255,255,.08)"

                    }

                }

            }

        }

    }
);

</script>


</body>

</html>