<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   VALIDAR ID DE MEMBRESÍA
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
        id_membresia,
        id_cliente,
        tipo,
        fecha_inicio,
        fecha_fin,
        fecha_limite_pago,
        estado,
        valor
    FROM membresias
    WHERE id_membresia = ?
    LIMIT 1
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando consulta de membresía: " .
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


$resultado =
    mysqli_stmt_get_result(
        $stmt
    );


if (
    mysqli_num_rows(
        $resultado
    ) === 0
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


$fila =
    mysqli_fetch_assoc(
        $resultado
    );


mysqli_stmt_close(
    $stmt
);


/* =========================================
   CONSULTAR CLIENTES ACTIVOS
========================================= */

$sqlClientes = "
    SELECT
        id_cliente,
        nombres,
        apellidos
    FROM clientes
    WHERE estado = 'Activo'
       OR id_cliente = ?
    ORDER BY nombres, apellidos
";


$stmtClientes =
    mysqli_prepare(
        $conexion,
        $sqlClientes
    );


if (!$stmtClientes) {

    error_log(
        "Error preparando consulta de clientes: " .
        mysqli_error($conexion)
    );

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudieron cargar los clientes."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmtClientes,
    "i",
    $fila["id_cliente"]
);


mysqli_stmt_execute(
    $stmtClientes
);


$resultadoClientes =
    mysqli_stmt_get_result(
        $stmtClientes
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
        Editar Membresía | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

</head>


<body>


<div class="form-container">

    <h2>
        EDITAR MEMBRESÍA
    </h2>


    <form
        action="actualizar_membresia.php"
        method="POST"
    >

        <?php echo csrf_field(); ?>


        <!-- =================================
             ID MEMBRESÍA
        ================================== -->

        <input
            type="hidden"
            name="id_membresia"
            value="<?php
                echo (int)
                $fila["id_membresia"];
            ?>"
        >


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

                <?php

                while (
                    $cliente =
                    mysqli_fetch_assoc(
                        $resultadoClientes
                    )
                ) {

                    $idCliente =
                        (int)
                        $cliente["id_cliente"];

                ?>

                    <option
                        value="<?php
                            echo $idCliente;
                        ?>"

                        <?php

                        if (
                            $idCliente ===
                            (int)
                            $fila["id_cliente"]
                        ) {

                            echo "selected";
                        }

                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $cliente["nombres"] .
                            " " .
                            $cliente["apellidos"],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </option>

                <?php

                }

                ?>

            </select>

        </div>


        <!-- =================================
             TIPO DE MEMBRESÍA
        ================================== -->

        <div class="form-group">

            <label for="tipo">

                Tipo de Membresía

            </label>


            <select
                name="tipo"
                id="tipo"
                required
            >

                <option
                    value="Mensual"
                    <?php
                    if (
                        $fila["tipo"] ===
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
                        $fila["tipo"] ===
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
                        $fila["tipo"] ===
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
                        $fila["tipo"] ===
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


        <!-- =================================
             FECHA DE INICIO
        ================================== -->

        <div class="form-group">

            <label for="fecha_inicio">

                Fecha Inicio

            </label>


            <input
                type="date"
                name="fecha_inicio"
                id="fecha_inicio"

                value="<?php
                    echo htmlspecialchars(
                        $fila["fecha_inicio"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                max="<?php
                    echo date("Y-m-d");
                ?>"

                required
            >

        </div>


        <!-- =================================
             ACTUALIZAR
        ================================== -->

        <button
            type="submit"
            class="btn-guardar"
        >

            Actualizar Membresía

        </button>


        <a
            href="membresias.php"
            class="btn-editar"
        >

            Cancelar

        </a>

    </form>

</div>


<?php

mysqli_stmt_close(
    $stmtClientes
);

?>


</body>

</html>