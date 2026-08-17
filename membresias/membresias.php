<?php

require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");

/* =================================
   ACTUALIZAR ESTADOS AUTOMÁTICAMENTE
================================= */

$sqlActualizarEstados = "UPDATE membresias
                         SET estado = 'Vencida'
                         WHERE fecha_fin < CURDATE()
                         AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizarEstados);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Membresías | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body>

<!-- =================================
     MENÚ SUPERIOR
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
                👤 Clientes
            </a>
        </li>

        <li>
            <a
                href="membresias.php"
                class="menu-activo">

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

<!-- =================================
     NOTIFICACIONES
================================= -->

<?php
require_once("../config/notificaciones.php");
?>

<!-- =================================
     CONTENIDO PRINCIPAL
================================= -->

<div class="contenedor-principal">

    <!-- =================================
         FORMULARIO
    ================================= -->

    <div class="form-container">

        <h2>REGISTRO DE MEMBRESÍAS</h2>

        <form
            action="guardar_membresia.php"
            method="POST">

            <!-- CLIENTE -->

            <div class="form-group">

                <label>Cliente</label>

                <select
                    name="id_cliente"
                    required>

                    <option value="">
                        Seleccione un cliente
                    </option>

                    <?php

                    $sqlClientes = "SELECT
                                        id_cliente,
                                        nombres,
                                        apellidos
                                    FROM clientes
                                    ORDER BY nombres ASC,
                                             apellidos ASC";

                    $resultadoClientes = mysqli_query(
                        $conexion,
                        $sqlClientes
                    );

                    if ($resultadoClientes) {

                        while (
                            $cliente =
                            mysqli_fetch_assoc(
                                $resultadoClientes
                            )
                        ) {

                    ?>

                            <option
                                value="<?php
                                    echo $cliente[
                                        'id_cliente'
                                    ];
                                ?>">

                                <?php
                                echo htmlspecialchars(
                                    $cliente['nombres'] .
                                    " " .
                                    $cliente['apellidos']
                                );
                                ?>

                            </option>

                    <?php

                        }
                    }

                    ?>

                </select>

            </div>

            <!-- TIPO DE MEMBRESÍA -->

            <div class="form-group">

                <label>
                    Tipo de Membresía
                </label>

                <select
                    name="tipo"
                    required>

                    <option value="">
                        Seleccione
                    </option>

                    <option value="Mensual">
                        Mensual
                    </option>

                    <option value="Trimestral">
                        Trimestral
                    </option>

                    <option value="Semestral">
                        Semestral
                    </option>

                    <option value="Anual">
                        Anual
                    </option>

                </select>

            </div>

            <!-- FECHA INICIO -->

            <div class="form-group">

                <label>
                    Fecha Inicio
                </label>

                <input
                    type="date"
                    name="fecha_inicio"
                    required>

            </div>

            <button
                type="submit"
                class="btn-guardar">

                Guardar Membresía

            </button>

        </form>

    </div>

    <!-- =================================
         TABLA
    ================================= -->

    <div
        class="tabla-container"
        data-tabla-buscable>

        <h2>MEMBRESÍAS REGISTRADAS</h2>

        <!-- BUSCADOR -->

        <div class="herramientas-tabla">

            <div class="buscador-tabla">

                <label for="buscarMembresias">
                    Buscar membresía
                </label>

                <input
                    type="search"
                    id="buscarMembresias"
                    data-buscador
                    placeholder="Cliente, tipo, estado o fecha"
                    autocomplete="off">

            </div>

            <span
                class="contador-resultados"
                data-contador-resultados>
            </span>

        </div>

        <div class="tabla-responsive">

            <table id="tablaMembresias">

                <thead>

                    <tr>

                        <th
                            data-ordenable
                            data-tipo="numero">

                            ID

                        </th>

                        <th data-ordenable>
                            Cliente
                        </th>

                        <th data-ordenable>
                            Tipo
                        </th>

                        <th
                            data-ordenable
                            data-tipo="fecha">

                            Inicio

                        </th>

                        <th
                            data-ordenable
                            data-tipo="fecha">

                            Fin

                        </th>

                        <th data-ordenable>
                            Estado
                        </th>

                        <th
                            data-ordenable
                            data-tipo="numero">

                            Días restantes

                        </th>

                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $sql = "SELECT

                            m.id_membresia,
                            m.id_cliente,
                            m.valor,

                            c.nombres,
                            c.apellidos,
                            c.cedula,

                            m.tipo,
                            m.fecha_inicio,
                            m.fecha_fin,
                            m.estado,

                            DATEDIFF(
                                m.fecha_fin,
                                CURDATE()
                            ) AS dias_restantes

                        FROM membresias m

                        INNER JOIN clientes c

                            ON m.id_cliente =
                               c.id_cliente

                        ORDER BY
                            m.id_membresia DESC";

                $resultado = mysqli_query(
                    $conexion,
                    $sql
                );

                if (!$resultado) {

                    die(
                        "Error al consultar las membresías: " .
                        mysqli_error($conexion)
                    );
                }

                while (
                    $fila =
                    mysqli_fetch_assoc($resultado)
                ) {

                    $diasRestantes =
                        (int) $fila[
                            'dias_restantes'
                        ];

                    /* =========================
                       DEFINIR COLOR DE FILA
                    ========================= */

                    $claseFila = "";

                    if (
                        $fila['estado'] ===
                        'Vencida' ||
                        $diasRestantes < 0
                    ) {

                        $claseFila =
                            "fila-vencida";

                    } elseif (
                        $diasRestantes <= 5
                    ) {

                        $claseFila =
                            "fila-proxima";
                    }

                ?>

                    <tr
                        class="<?php
                            echo $claseFila;
                        ?>">

                        <!-- ID -->

                        <td
                            data-orden="<?php
                                echo $fila[
                                    'id_membresia'
                                ];
                            ?>">

                            <?php
                            echo $fila[
                                'id_membresia'
                            ];
                            ?>

                        </td>

                        <!-- CLIENTE -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $fila['nombres'] .
                                    " " .
                                    $fila['apellidos']
                                );
                            ?>">

                            <?php

                            echo htmlspecialchars(
                                $fila['nombres'] .
                                " " .
                                $fila['apellidos']
                            );

                            ?>

                        </td>

                        <!-- TIPO -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $fila['tipo']
                                );
                            ?>">

                            <?php
                            echo htmlspecialchars(
                                $fila['tipo']
                            );
                            ?>

                        </td>

                        <!-- FECHA INICIO -->

                        <td
                            data-orden="<?php
                                echo $fila[
                                    'fecha_inicio'
                                ];
                            ?>">

                            <?php

                            echo date(
                                "d/m/Y",
                                strtotime(
                                    $fila[
                                        'fecha_inicio'
                                    ]
                                )
                            );

                            ?>

                        </td>

                        <!-- FECHA FIN -->

                        <td
                            data-orden="<?php
                                echo $fila[
                                    'fecha_fin'
                                ];
                            ?>">

                            <?php

                            echo date(
                                "d/m/Y",
                                strtotime(
                                    $fila[
                                        'fecha_fin'
                                    ]
                                )
                            );

                            ?>

                        </td>

                        <!-- ESTADO -->

                        <td>

                            <?php

                            if (
                                $fila['estado'] ===
                                'Activa'
                            ) {

                                if (
                                    $diasRestantes <= 5
                                ) {

                                    echo "
                                    <span
                                        class='estado-proxima'>

                                        ⚠ Próxima a vencer

                                    </span>
                                    ";

                                } else {

                                    echo "
                                    <span
                                        class='estado-activa'>

                                        Activa

                                    </span>
                                    ";
                                }

                            } else {

                                echo "
                                <span
                                    class='estado-vencida'>

                                    Vencida

                                </span>
                                ";
                            }

                            ?>

                        </td>

                        <!-- DÍAS RESTANTES -->

                        <td
                            data-orden="<?php
                                echo $diasRestantes;
                            ?>">

                            <?php

                            if (
                                $fila['estado'] ===
                                'Vencida' ||
                                $diasRestantes < 0
                            ) {

                                echo "
                                <span
                                    class='dias-vencida'>

                                    Vencida hace " .
                                    abs(
                                        $diasRestantes
                                    ) .
                                    " días

                                </span>
                                ";

                            } elseif (
                                $diasRestantes === 0
                            ) {

                                echo "
                                <span
                                    class='dias-hoy'>

                                    Vence hoy

                                </span>
                                ";

                            } elseif (
                                $diasRestantes <= 5
                            ) {

                                echo "
                                <span
                                    class='dias-proximos'>

                                    Faltan " .
                                    $diasRestantes .
                                    " días

                                </span>
                                ";

                            } else {

                                echo "
                                <span
                                    class='dias-normales'>

                                    Faltan " .
                                    $diasRestantes .
                                    " días

                                </span>
                                ";
                            }

                            ?>

                        </td>

                        <!-- ACCIONES -->

                        <td
                            class="acciones-membresia">

                            <!-- RENOVAR -->

                            <?php

                            if (
                                $fila['estado'] ===
                                'Vencida' ||
                                $diasRestantes <= 5
                            ) {

                            ?>

                                <a
                                    href="renovar_membresia.php?id=<?php
                                        echo $fila[
                                            'id_membresia'
                                        ];
                                    ?>"
                                    class="btn-renovar">

                                    Renovar

                                </a>

                            <?php } ?>

                            <!-- EDITAR -->

                            <a
                                href="editar_membresia.php?id=<?php
                                    echo $fila[
                                        'id_membresia'
                                    ];
                                ?>"
                                class="btn-editar">

                                Editar

                            </a>

                            <!-- ELIMINAR SOLO ADMIN -->

                            <?php

                            if (
                                isset(
                                    $_SESSION["rol"]
                                ) &&
                                $_SESSION["rol"] ===
                                "Administrador"
                            ) {

                            ?>

                                <a
                                    href="eliminar_membresia.php?id=<?php
                                        echo $fila[
                                            'id_membresia'
                                        ];
                                    ?>"
                                    class="btn-eliminar"
                                    onclick="
                                    return confirm(
                                        '¿Desea eliminar esta membresía?'
                                    );">

                                    Eliminar

                                </a>

                            <?php } ?>

                        </td>

                    </tr>

                <?php

                }

                ?>

                    <!-- =========================
                         SIN RESULTADOS DE BÚSQUEDA
                    ========================= -->

                    <tr
                        data-sin-resultados
                        class="fila-busqueda-vacia"
                        style="display:none;">

                        <td colspan="8">

                            No se encontraron
                            membresías.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =================================
     JAVASCRIPT
================================= -->

<script src="../assets/js/tablas.js"></script>

</body>

</html>