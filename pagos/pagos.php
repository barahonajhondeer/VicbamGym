<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   FUNCIÓN PARA ESCAPAR TEXTO
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
    ORDER BY
        nombres ASC,
        apellidos ASC
";


$resultadoClientes = mysqli_query(
    $conexion,
    $sqlClientes
);


if (!$resultadoClientes) {

    error_log(
        "Error consultando clientes en pagos.php: " .
        mysqli_error($conexion)
    );

    $resultadoClientes = false;
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
        p.fecha_anulacion,
        p.anulado_por,

        c.cedula,
        c.nombres,
        c.apellidos,

        m.tipo,

        u.nombre AS usuario_anulacion

    FROM pagos p

    INNER JOIN clientes c
        ON c.id_cliente = p.id_cliente

    INNER JOIN membresias m
        ON m.id_membresia = p.id_membresia

    LEFT JOIN usuarios u
        ON u.id_usuario = p.anulado_por

    ORDER BY
        p.id_pago DESC
";


$resultadoPagos = mysqli_query(
    $conexion,
    $sqlPagos
);


if (!$resultadoPagos) {

    error_log(
        "Error consultando historial de pagos: " .
        mysqli_error($conexion)
    );

    $resultadoPagos = false;
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

            <?php
            echo csrf_field();
            ?>


            <!-- =================================
                 CLIENTE
            ================================== -->

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
                                    echo (int)
                                    $cliente[
                                        "id_cliente"
                                    ];
                                ?>"
                            >

                                <?php

                                echo e(
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

                    }

                    ?>

                </select>

            </div>


            <!-- =================================
                 MEMBRESÍA
            ================================== -->

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


            <!-- =================================
                 VALOR
            ================================== -->

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


            <!-- =================================
                 MÉTODO
            ================================== -->

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


            <!-- =================================
                 FECHA
            ================================== -->

            <div class="form-group">

                <label for="fecha_pago">

                    Fecha de pago

                </label>


                <input
                    type="date"
                    name="fecha_pago"
                    id="fecha_pago"

                    value="<?php
                        echo date(
                            "Y-m-d"
                        );
                    ?>"

                    max="<?php
                        echo date(
                            "Y-m-d"
                        );
                    ?>"

                    required
                >

            </div>


            <!-- =================================
                 BOTÓN
            ================================== -->

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


        <!-- =================================
             BUSCADOR
        ================================== -->

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

                if ($resultadoPagos) {

                    while (
                        $fila =
                        mysqli_fetch_assoc(
                            $resultadoPagos
                        )
                    ) {

                        $idPago =
                            (int)
                            $fila[
                                "id_pago"
                            ];


                        $estadoPago =
                            $fila[
                                "estado"
                            ] ??
                            "Registrado";

                ?>


                    <tr
                        class="<?php

                            echo
                                $estadoPago ===
                                "Anulado"
                                ? "fila-pago-anulado"
                                : "";

                        ?>"
                    >


                        <!-- =================================
                             ID
                        ================================== -->

                        <td
                            data-orden="<?php
                                echo $idPago;
                            ?>"
                        >

                            <?php
                            echo $idPago;
                            ?>

                        </td>


                        <!-- =================================
                             CLIENTE
                        ================================== -->

                        <td class="cliente-pago">

                            <strong>

                                <?php

                                echo e(
                                    $fila["nombres"] .
                                    " " .
                                    $fila["apellidos"]
                                );

                                ?>

                            </strong>


                            <small>

                                C.I.:

                                <?php

                                echo e(
                                    $fila["cedula"]
                                );

                                ?>

                            </small>

                        </td>


                        <!-- =================================
                             MEMBRESÍA
                        ================================== -->

                        <td
                            data-orden="<?php
                                echo e(
                                    $fila["tipo"]
                                );
                            ?>"
                        >

                            <?php

                            echo e(
                                $fila["tipo"]
                            );

                            ?>

                        </td>


                        <!-- =================================
                             VALOR
                        ================================== -->

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


                        <!-- =================================
                             MÉTODO
                        ================================== -->

                        <td
                            data-orden="<?php
                                echo e(
                                    $fila[
                                        "metodo_pago"
                                    ]
                                );
                            ?>"
                        >

                            <?php

                            echo e(
                                $fila[
                                    "metodo_pago"
                                ]
                            );

                            ?>

                        </td>


                        <!-- =================================
                             FECHA
                        ================================== -->

                        <td
                            data-orden="<?php
                                echo e(
                                    $fila[
                                        "fecha_pago"
                                    ]
                                );
                            ?>"
                        >

                            <?php

                            echo date(
                                "d/m/Y",
                                strtotime(
                                    $fila[
                                        "fecha_pago"
                                    ]
                                )
                            );

                            ?>

                        </td>


                        <!-- =================================
                             ESTADO
                        ================================== -->

                        <td
                            data-orden="<?php

                                echo e(
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


                                <!-- =========================
                                     MOTIVO DE ANULACIÓN
                                ========================== -->

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

                                        echo e(
                                            $fila[
                                                "motivo_anulacion"
                                            ]
                                        );

                                        ?>

                                    </div>

                                <?php

                                }

                                ?>


                                <!-- =========================
                                     AUDITORÍA
                                ========================== -->

                                <?php

                                if (
                                    !empty(
                                        $fila[
                                            "usuario_anulacion"
                                        ]
                                    ) ||
                                    !empty(
                                        $fila[
                                            "fecha_anulacion"
                                        ]
                                    )
                                ) {

                                ?>

                                    <div
                                        class="auditoria-pago"
                                    >


                                        <?php

                                        if (
                                            !empty(
                                                $fila[
                                                    "usuario_anulacion"
                                                ]
                                            )
                                        ) {

                                            echo
                                                "Anulado por: " .
                                                e(
                                                    $fila[
                                                        "usuario_anulacion"
                                                    ]
                                                );
                                        }

                                        ?>


                                        <?php

                                        if (
                                            !empty(
                                                $fila[
                                                    "fecha_anulacion"
                                                ]
                                            )
                                        ) {

                                            if (
                                                !empty(
                                                    $fila[
                                                        "usuario_anulacion"
                                                    ]
                                                )
                                            ) {

                                                echo "<br>";
                                            }


                                            echo
                                                "Fecha: " .
                                                date(
                                                    "d/m/Y H:i",
                                                    strtotime(
                                                        $fila[
                                                            "fecha_anulacion"
                                                        ]
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


                        <!-- =================================
                             ACCIONES
                        ================================== -->

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

                }

                ?>


                    <!-- =================================
                         SIN RESULTADOS
                    ================================== -->

                    <tr
                        data-sin-resultados
                        class="fila-busqueda-vacia"
                        style="display:none;"
                    >

                        <td colspan="8">

                            No se encontraron pagos.

                        </td>

                    </tr>


                    <?php

                    if (!$resultadoPagos) {

                    ?>

                        <tr>

                            <td
                                colspan="8"
                                style="text-align:center;"
                            >

                                No se pudo cargar el historial de pagos.

                            </td>

                        </tr>

                    <?php

                    }

                    ?>


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

            <?php
            echo csrf_field();
            ?>


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
     JAVASCRIPT TABLAS
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


/* =========================================
   AL CAMBIAR CLIENTE
========================================= */

clienteSelect.addEventListener(
    "change",
    function () {

        const idCliente =
            this.value;


        /* LIMPIAR VALOR */

        valorInput.value = "";

        valorInput.removeAttribute(
            "max"
        );


        /* SIN CLIENTE */

        if (!idCliente) {

            membresiaSelect.innerHTML =
                "<option value=''>" +
                "Seleccione primero un cliente" +
                "</option>";

            return;
        }


        /* MOSTRAR CARGANDO */

        membresiaSelect.innerHTML =
            "<option value=''>" +
            "Cargando..." +
            "</option>";


        /* CONSULTAR SERVIDOR */

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
                        "Error al consultar membresías"
                    );
                }

                return response.json();
            }
        )

        .then(
            data => {

                membresiaSelect.innerHTML =
                    "";


                /* VALIDAR RESPUESTA */

                if (
                    !Array.isArray(data) ||
                    data.length === 0
                ) {

                    membresiaSelect.innerHTML =
                        "<option value=''>" +
                        "El cliente no tiene membresías activas disponibles" +
                        "</option>";

                    return;
                }


                /* OPCIÓN INICIAL */

                const inicial =
                    document.createElement(
                        "option"
                    );


                inicial.value = "";

                inicial.textContent =
                    "Seleccione una membresía";


                membresiaSelect.appendChild(
                    inicial
                );


                /* AGREGAR MEMBRESÍAS */

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

                            const saldo =
                                parseFloat(
                                    membresia.saldo
                                );


                            if (
                                !isNaN(saldo)
                            ) {

                                texto +=
                                    " | Saldo: $" +
                                    saldo.toFixed(2);
                            }
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


                valorInput.value = "";

                valorInput.removeAttribute(
                    "max"
                );
            }
        );
    }
);



/* =========================================
   COLOCAR SALDO AL SELECCIONAR MEMBRESÍA
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
            opcion.value === "" ||
            opcion.dataset.saldo === undefined
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
            !isNaN(saldo) &&
            saldo > 0
        ) {

            valorInput.max =
                saldo.toFixed(2);


            valorInput.value =
                saldo.toFixed(2);

        } else {

            valorInput.value = "";

            valorInput.removeAttribute(
                "max"
            );
        }
    }
);

</script>



<!-- =========================================
     MODAL DE ANULACIÓN
========================================= -->

<script>

/* =========================================
   ABRIR MODAL
========================================= */

function abrirAnulacion(idPago) {

    const campoId =
        document.getElementById(
            "idPagoAnular"
        );


    const motivo =
        document.getElementById(
            "motivo_anulacion"
        );


    const modal =
        document.getElementById(
            "modalAnulacion"
        );


    campoId.value =
        idPago;


    motivo.value =
        "";


    modal.style.display =
        "flex";


    setTimeout(
        function () {

            motivo.focus();

        },
        100
    );
}


/* =========================================
   CERRAR MODAL
========================================= */

function cerrarAnulacion() {

    const modal =
        document.getElementById(
            "modalAnulacion"
        );


    modal.style.display =
        "none";


    document.getElementById(
        "idPagoAnular"
    ).value =
        "";


    document.getElementById(
        "motivo_anulacion"
    ).value =
        "";
}


/* =========================================
   CERRAR HACIENDO CLIC FUERA
========================================= */

document.getElementById(
    "modalAnulacion"
).addEventListener(
    "click",
    function (event) {

        if (
            event.target === this
        ) {

            cerrarAnulacion();
        }
    }
);


/* =========================================
   CERRAR CON ESC
========================================= */

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key ===
            "Escape"
        ) {

            cerrarAnulacion();
        }
    }
);

</script>


</body>

</html>