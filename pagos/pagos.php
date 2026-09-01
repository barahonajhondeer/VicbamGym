<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");


/* =========================================
   CONSULTAR CLIENTES ACTIVOS
========================================= */

$sqlClientes = "
    SELECT
        id_cliente,
        cedula,
        nombres,
        apellidos
    FROM clientes
    WHERE estado = 'Activo'
    ORDER BY nombres ASC, apellidos ASC
";

$resultadoClientes = mysqli_query(
    $conexion,
    $sqlClientes
);

if (!$resultadoClientes) {

    die(
        "Error al consultar clientes: " .
        mysqli_error($conexion)
    );
}


/* =========================================
   CONSULTAR HISTORIAL DE PAGOS
========================================= */

$sqlPagos = "
    SELECT
        p.id_pago,
        p.id_cliente,
        p.id_membresia,
        p.valor,
        p.metodo_pago,
        p.fecha_pago,
        p.estado,
        p.motivo_anulacion,

        c.cedula,
        c.nombres,
        c.apellidos,

        m.tipo

        u.nombre AS usuario_anulacion

    FROM pagos p

    INNER JOIN clientes c
        ON c.id_cliente = p.id_cliente

    INNER JOIN membresias m
        ON m.id_membresia = p.id_membresia

    LEFT JOIN usuarios u
        ON u.id_usuario = p.anulado_por

    ORDER BY p.id_pago DESC
";

$resultadoPagos = mysqli_query(
    $conexion,
    $sqlPagos
);

if (!$resultadoPagos) {

    die(
        "Error al consultar pagos: " .
        mysqli_error($conexion)
    );
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
        Pagos | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

</head>

<body>


<!-- =========================================
     MENÚ PRINCIPAL
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

            <a
                href="pagos.php"
                class="menu-activo"
            >
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

<div class="contenedor-principal">


    <!-- =====================================
         FORMULARIO REGISTRO DE PAGO
    ====================================== -->

    <div class="form-container">

        <h2>
            REGISTRAR PAGO
        </h2>


        <form
            action="guardar_pago.php"
            method="POST"
            id="formPago"
        >


            <!-- CLIENTE -->

            <div class="form-group">

                <label for="id_cliente">
                    Cliente
                </label>


                <select
                    name="id_cliente"
                    id="id_cliente"
                    required
                >

                    <option value="">
                        Seleccione un cliente
                    </option>


                    <?php

                    while (
                        $cliente =
                        mysqli_fetch_assoc(
                            $resultadoClientes
                        )
                    ) {

                    ?>

                        <option
                            value="<?php
                                echo (int)
                                $cliente["id_cliente"];
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $cliente["cedula"] .
                                " - " .
                                $cliente["nombres"] .
                                " " .
                                $cliente["apellidos"]
                            );

                            ?>

                        </option>

                    <?php

                    }

                    ?>

                </select>

            </div>


            <!-- MEMBRESÍA -->

            <div class="form-group">

                <label for="id_membresia">
                    Membresía
                </label>


                <select
                    name="id_membresia"
                    id="id_membresia"
                    required
                >

                    <option value="">
                        Seleccione primero un cliente
                    </option>

                </select>

            </div>


            <!-- VALOR -->

            <div class="form-group">

                <label for="valor">
                    Valor del pago
                </label>


                <input
                    type="number"
                    name="valor"
                    id="valor"
                    min="0.01"
                    step="0.01"
                    placeholder="0.00"
                    required
                >

            </div>


            <!-- MÉTODO -->

            <div class="form-group">

                <label for="metodo_pago">
                    Método de pago
                </label>


                <select
                    name="metodo_pago"
                    id="metodo_pago"
                    required
                >

                    <option value="">
                        Seleccione
                    </option>

                    <option value="Efectivo">
                        Efectivo
                    </option>

                    <option value="Transferencia">
                        Transferencia
                    </option>

                </select>

            </div>


            <!-- FECHA -->

            <div class="form-group">

                <label for="fecha_pago">
                    Fecha de pago
                </label>


                <input
                    type="date"
                    name="fecha_pago"
                    id="fecha_pago"
                    value="<?php
                        echo date("Y-m-d");
                    ?>"
                    max="<?php
                        echo date("Y-m-d");
                    ?>"
                    required
                >

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                class="btn-guardar"
            >

                Registrar Pago

            </button>

        </form>

    </div>



    <!-- =====================================
         HISTORIAL DE PAGOS
    ====================================== -->

    <div
        class="tabla-container"
        data-tabla-buscable
    >

        <h2>
            HISTORIAL DE PAGOS
        </h2>


        <!-- BUSCADOR -->

        <div class="herramientas-tabla">

            <div class="buscador-tabla">

                <label for="buscarPagos">

                    Buscar pago

                </label>


                <input
                    type="search"
                    id="buscarPagos"
                    data-buscador
                    placeholder="Cliente, cédula, membresía, método o estado"
                    autocomplete="off"
                >

            </div>


            <span
                class="contador-resultados"
                data-contador-resultados
            >
            </span>

        </div>


        <!-- =================================
             TABLA
        ================================== -->

        <div class="tabla-responsive">

            <table id="tablaPagos">

                <thead>

                    <tr>

                        <th
                            data-ordenable
                            data-tipo="numero"
                        >
                            ID
                        </th>


                        <th data-ordenable>
                            Cliente
                        </th>


                        <th data-ordenable>
                            Membresía
                        </th>


                        <th
                            data-ordenable
                            data-tipo="numero"
                        >
                            Valor
                        </th>


                        <th data-ordenable>
                            Método
                        </th>


                        <th
                            data-ordenable
                            data-tipo="fecha"
                        >
                            Fecha
                        </th>


                        <th data-ordenable>
                            Estado
                        </th>


                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php

                while (
                    $fila =
                    mysqli_fetch_assoc(
                        $resultadoPagos
                    )
                ) {

                    $idPago =
                        (int) $fila["id_pago"];

                    $estadoPago =
                        $fila["estado"] ??
                        "Registrado";

                ?>

                    <tr
                        class="<?php
                            echo
                            $estadoPago === "Anulado"
                            ? "fila-pago-anulado"
                            : "";
                        ?>"
                    >


                        <!-- ID -->

                        <td
                            data-orden="<?php
                                echo $idPago;
                            ?>"
                        >

                            <?php
                            echo $idPago;
                            ?>

                        </td>


                        <!-- CLIENTE + CÉDULA -->

                        <td class="cliente-pago">

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

                                C.I.:

                                <?php

                                echo htmlspecialchars(
                                    $fila["cedula"]
                                );

                                ?>

                            </small>

                        </td>


                        <!-- MEMBRESÍA -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["tipo"]
                            );

                            ?>

                        </td>


                        <!-- VALOR -->

                        <td
                            data-orden="<?php
                                echo
                                (float)
                                $fila["valor"];
                            ?>"
                        >

                            $

                            <?php

                            echo number_format(
                                (float)
                                $fila["valor"],
                                2
                            );

                            ?>

                        </td>


                        <!-- MÉTODO -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $fila["metodo_pago"]
                            );

                            ?>

                        </td>


                        <!-- FECHA -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $fila["fecha_pago"]
                                );
                            ?>"
                        >

                            <?php

                            echo date(
                                "d/m/Y",
                                strtotime(
                                    $fila["fecha_pago"]
                                )
                            );

                            ?>

                        </td>


                        <!-- ESTADO -->

                        <td
                            data-orden="<?php
                                echo htmlspecialchars(
                                    $estadoPago
                                );
                            ?>"
                        >

                            <?php

                            if (
                                $estadoPago ===
                                "Registrado"
                            ) {

                            ?>

                                <span
                                    class="estado-activa"
                                >

                                    Registrado

                                </span>

                            <?php

                            } else {

                            ?>

                                <span
                                    class="estado-vencida"
                                >

                                    Anulado

                                </span>


                                <?php

                                if (
                                    !empty(
                                        $fila[
                                            "motivo_anulacion"
                                        ]
                                    )
                                ) {

                                ?>

                                    <div
                                        class="motivo-pago"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $fila[
                                                "motivo_anulacion"
                                            ]
                                        );

                                        ?>

                                    </div>
                                    <div class="auditoria-pago">

                                    <?php

                                    if (
                                        !empty(
                                            $fila["usuario_anulacion"]
                                        )
                                    ) {

                                        echo "Anulado por: " .
                                            htmlspecialchars(
                                                $fila["usuario_anulacion"]
                                            );

                                    }

                                    ?>

                                    <?php

                                    if (
                                        !empty(
                                            $fila["fecha_anulacion"]
                                        )
                                    ) {

                                        echo "<br>";

                                        echo "Fecha: " .
                                            date(
                                                "d/m/Y H:i",
                                                strtotime(
                                                    $fila["fecha_anulacion"]
                                                )
                                            );

                                    }

                                    ?>

                                </div>

                                <?php

                                }

                                ?>

                            <?php

                            }

                            ?>

                        </td>


                        <!-- ACCIONES -->

                        <td class="acciones-pago">

                            <?php

                            if (
                                isset(
                                    $_SESSION["rol"]
                                ) &&
                                $_SESSION["rol"] ===
                                "Administrador" &&
                                $estadoPago ===
                                "Registrado"
                            ) {

                            ?>

                                <button
                                    type="button"
                                    class="btn-eliminar"
                                    onclick="abrirAnulacion(
                                        <?php
                                            echo $idPago;
                                        ?>
                                    )"
                                >

                                    Anular

                                </button>

                            <?php

                            } elseif (
                                $estadoPago ===
                                "Anulado"
                            ) {

                            ?>

                                <span
                                    class="texto-anulado"
                                >

                                    Sin acciones

                                </span>

                            <?php

                            } else {

                            ?>

                                <span>
                                    -
                                </span>

                            <?php

                            }

                            ?>

                        </td>

                    </tr>


                <?php

                }

                ?>


                    <!-- SIN RESULTADOS -->

                    <tr
                        data-sin-resultados
                        class="fila-busqueda-vacia"
                        style="display:none;"
                    >

                        <td colspan="8">

                            No se encontraron pagos.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- =========================================
     MODAL ANULAR PAGO
========================================= -->

<div
    id="modalAnulacion"
    class="modal-anulacion"
>

    <div class="modal-anulacion-contenido">

        <h3>
            Anular pago
        </h3>


        <p>
            El pago permanecerá registrado,
            pero dejará de contar como ingreso.
        </p>


        <form
            action="anular_pago.php"
            method="POST"
        >

            <input
                type="hidden"
                name="id_pago"
                id="idPagoAnular"
            >


            <div class="form-group">

                <label for="motivo_anulacion">

                    Motivo de la anulación

                </label>


                <textarea
                    name="motivo_anulacion"
                    id="motivo_anulacion"
                    maxlength="255"
                    minlength="3"
                    required
                    placeholder="Ejemplo: pago registrado por error"
                ></textarea>

            </div>


            <div class="acciones-modal">

                <button
                    type="submit"
                    class="btn-eliminar"
                >

                    Confirmar anulación

                </button>


                <button
                    type="button"
                    class="btn-cancelar"
                    onclick="cerrarAnulacion()"
                >

                    Cancelar

                </button>

            </div>

        </form>

    </div>

</div>



<!-- =========================================
     JS GENERAL DE TABLAS
========================================= -->

<script src="../assets/js/tablas.js"></script>



<!-- =========================================
     CARGAR MEMBRESÍAS DEL CLIENTE
========================================= -->

<script>

const clienteSelect =
    document.getElementById(
        "id_cliente"
    );

const membresiaSelect =
    document.getElementById(
        "id_membresia"
    );

const valorInput =
    document.getElementById(
        "valor"
    );


clienteSelect.addEventListener(
    "change",
    function () {

        const idCliente =
            this.value;


        valorInput.value = "";


        if (!idCliente) {

            membresiaSelect.innerHTML =
                "<option value=''>" +
                "Seleccione primero un cliente" +
                "</option>";

            return;
        }


        membresiaSelect.innerHTML =
            "<option value=''>" +
            "Cargando..." +
            "</option>";


        fetch(
            "obtener_membresia.php?id_cliente=" +
            encodeURIComponent(
                idCliente
            )
        )

        .then(
            response => {

                if (!response.ok) {

                    throw new Error(
                        "No se pudo consultar la membresía."
                    );
                }

                return response.json();

            }
        )

        .then(
            data => {

                membresiaSelect.innerHTML =
                    "";


                if (
                    !Array.isArray(data) ||
                    data.length === 0
                ) {

                    membresiaSelect.innerHTML =
                        "<option value=''>" +
                        "El cliente no tiene membresías disponibles" +
                        "</option>";

                    return;
                }


                const opcionInicial =
                    document.createElement(
                        "option"
                    );

                opcionInicial.value = "";

                opcionInicial.textContent =
                    "Seleccione una membresía";

                membresiaSelect.appendChild(
                    opcionInicial
                );


                data.forEach(
                    membresia => {

                        const opcion =
                            document.createElement(
                                "option"
                            );


                        opcion.value =
                            membresia.id_membresia;


                        opcion.dataset.saldo =
                            membresia.saldo;


                        let texto =
                            membresia.tipo;


                        if (
                            membresia.fecha_fin
                        ) {

                            texto +=
                                " | Vence: " +
                                membresia.fecha_fin;
                        }


                        if (
                            membresia.saldo !==
                            undefined
                        ) {

                            texto +=
                                " | Saldo: $" +
                                parseFloat(
                                    membresia.saldo
                                ).toFixed(2);
                        }


                        opcion.textContent =
                            texto;


                        membresiaSelect.appendChild(
                            opcion
                        );

                    }
                );

            }
        )

        .catch(
            error => {

                console.error(
                    error
                );


                membresiaSelect.innerHTML =
                    "<option value=''>" +
                    "Error al cargar membresías" +
                    "</option>";

            }
        );

    }
);


/* =========================================
   AL SELECCIONAR MEMBRESÍA,
   COLOCAR SALDO MÁXIMO
========================================= */

membresiaSelect.addEventListener(
    "change",
    function () {

        const opcion =
            this.options[
                this.selectedIndex
            ];


        if (
            !opcion ||
            !opcion.dataset.saldo
        ) {

            valorInput.value = "";
            valorInput.removeAttribute(
                "max"
            );

            return;
        }


        const saldo =
            parseFloat(
                opcion.dataset.saldo
            );


        if (
            !isNaN(saldo)
        ) {

            valorInput.max =
                saldo.toFixed(2);

            valorInput.value =
                saldo.toFixed(2);
        }

    }
);

</script>



<!-- =========================================
     MODAL DE ANULACIÓN
========================================= -->

<script>

function abrirAnulacion(idPago) {

    document.getElementById(
        "idPagoAnular"
    ).value =
        idPago;


    document.getElementById(
        "motivo_anulacion"
    ).value =
        "";


    document.getElementById(
        "modalAnulacion"
    ).style.display =
        "flex";


    setTimeout(
        function () {

            document.getElementById(
                "motivo_anulacion"
            ).focus();

        },
        100
    );
}


function cerrarAnulacion() {

    document.getElementById(
        "modalAnulacion"
    ).style.display =
        "none";

}


/* CERRAR HACIENDO CLIC FUERA */

document.getElementById(
    "modalAnulacion"
).addEventListener(
    "click",
    function(event) {

        if (
            event.target === this
        ) {

            cerrarAnulacion();

        }

    }
);


/* CERRAR CON ESC */

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

            cerrarAnulacion();

        }

    }
);

</script>


</body>

</html>