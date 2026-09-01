<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   VALIDAR ID
========================================= */

$id_membresia = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (
    !$id_membresia ||
    $id_membresia <= 0
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía seleccionada no es válida."
        )
    );

    exit();
}


/* =========================================
   CONSULTAR MEMBRESÍA
========================================= */

$sql = "
    SELECT
        m.id_membresia,
        m.id_cliente,
        m.tipo,
        m.fecha_inicio,
        m.fecha_fin,
        m.fecha_limite_pago,
        m.estado,
        m.valor,
        c.nombres,
        c.apellidos,
        c.estado AS estado_cliente
    FROM membresias m
    INNER JOIN clientes c
        ON c.id_cliente = m.id_cliente
    WHERE m.id_membresia = ?
    LIMIT 1
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando consulta de renovación: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo cargar la membresía."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_membresia
);


mysqli_stmt_execute(
    $stmt
);


$resultado = mysqli_stmt_get_result(
    $stmt
);


if (
    mysqli_num_rows($resultado) === 0
) {

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "La membresía seleccionada no existe."
        )
    );

    exit();
}


$membresia = mysqli_fetch_assoc(
    $resultado
);


mysqli_stmt_close(
    $stmt
);


/* =========================================
   VALIDAR ESTADO DEL CLIENTE
========================================= */

if (
    $membresia["estado_cliente"] !== "Activo"
) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede renovar la membresía porque el cliente está inactivo."
        )
    );

    exit();
}


/* =========================================
   PRECIOS
========================================= */

$precios = [
    "Mensual" => 25.00,
    "Trimestral" => 65.00,
    "Semestral" => 120.00,
    "Anual" => 220.00
];


$valorActual =
    $precios[$membresia["tipo"]] ?? 0;

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
        Renovar membresía | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

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

            <a
                href="membresias.php"
                class="menu-activo"
            >

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
     CONTENIDO
========================================= -->

<div class="contenedor-edicion">

    <div class="form-container">

        <h2>
            RENOVAR MEMBRESÍA
        </h2>


        <form
            action="procesar_renovacion.php"
            method="POST"
        >

            <?php echo csrf_field(); ?>


            <!-- ID MEMBRESÍA -->

            <input
                type="hidden"
                name="id_membresia"
                value="<?php
                    echo (int)
                    $membresia["id_membresia"];
                ?>"
            >



            <!-- CLIENTE -->

            <div class="form-group">

                <label>
                    Cliente
                </label>


                <input
                    type="text"

                    value="<?php

                        echo htmlspecialchars(
                            $membresia["nombres"] .
                            " " .
                            $membresia["apellidos"],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                    ?>"

                    readonly
                >

            </div>



            <!-- TIPO -->

            <div class="form-group">

                <label for="tipo_renovacion">

                    Tipo de membresía

                </label>


                <select
                    name="tipo"
                    id="tipo_renovacion"
                    required
                >


                    <option
                        value="Mensual"

                        <?php

                        if (
                            $membresia["tipo"] ===
                            "Mensual"
                        ) {

                            echo "selected";
                        }

                        ?>
                    >

                        Mensual

                    </option>


                    <option
                        value="Trimestral"

                        <?php

                        if (
                            $membresia["tipo"] ===
                            "Trimestral"
                        ) {

                            echo "selected";
                        }

                        ?>
                    >

                        Trimestral

                    </option>


                    <option
                        value="Semestral"

                        <?php

                        if (
                            $membresia["tipo"] ===
                            "Semestral"
                        ) {

                            echo "selected";
                        }

                        ?>
                    >

                        Semestral

                    </option>


                    <option
                        value="Anual"

                        <?php

                        if (
                            $membresia["tipo"] ===
                            "Anual"
                        ) {

                            echo "selected";
                        }

                        ?>
                    >

                        Anual

                    </option>

                </select>

            </div>



            <!-- VALOR -->

            <div class="form-group">

                <label>
                    Valor
                </label>


                <input
                    type="text"
                    id="valor_renovacion"

                    value="<?php
                        echo number_format(
                            $valorActual,
                            2,
                            ".",
                            ""
                        );
                    ?>"

                    readonly
                >

            </div>



            <!-- FECHA DE INICIO -->

            <div class="form-group">

                <label for="fecha_inicio">

                    Nueva fecha de inicio

                </label>


                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"

                    value="<?php
                        echo date("Y-m-d");
                    ?>"

                    max="<?php
                        echo date("Y-m-d");
                    ?>"

                    required
                >

            </div>



            <!-- BOTONES -->

            <button
                type="submit"
                class="btn-guardar"
            >

                Confirmar renovación

            </button>


            <a
                href="membresias.php"
                class="btn-cancelar"
            >

                Cancelar

            </a>

        </form>

    </div>

</div>



<!-- =========================================
     ACTUALIZAR PRECIO VISUAL
========================================= -->

<script>

const precios = {

    Mensual: 25.00,
    Trimestral: 65.00,
    Semestral: 120.00,
    Anual: 220.00

};


const tipo =
    document.getElementById(
        "tipo_renovacion"
    );


const valor =
    document.getElementById(
        "valor_renovacion"
    );


tipo.addEventListener(
    "change",
    function () {

        const precio =
            precios[this.value];


        if (
            precio !== undefined
        ) {

            valor.value =
                precio.toFixed(2);

        } else {

            valor.value = "";
        }
    }
);

</script>


</body>

</html>