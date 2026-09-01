<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   VALIDAR ID
========================================= */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (
    !$id ||
    $id <= 0
) {

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode("Cliente no válido.")
    );

    exit();
}


/* =========================================
   BUSCAR CLIENTE CON PREPARE
========================================= */

$sql = "
    SELECT
        id_cliente,
        cedula,
        nombres,
        apellidos,
        telefono,
        correo,
        direccion,
        estado
    FROM clientes
    WHERE id_cliente = ?
    LIMIT 1
";


$stmt = mysqli_prepare(
    $conexion,
    $sql
);


if (!$stmt) {

    error_log(
        "Error preparando consulta editar cliente: " .
        mysqli_error($conexion)
    );

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo cargar la información del cliente."
        )
    );

    exit();
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
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
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente solicitado no existe."
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
        Editar Cliente | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css"
    >

</head>


<body class="clientes-body">


<div class="form-container">


    <h2>
        EDITAR CLIENTE
    </h2>


    <form
        action="actualizar_cliente.php"
        method="POST"
    >

        <?php echo csrf_field(); ?>


        <!-- ID CLIENTE -->

        <input
            type="hidden"
            name="id_cliente"
            value="<?php
                echo (int) $fila["id_cliente"];
            ?>"
        >


        <!-- CÉDULA -->

        <div class="form-group">

            <label for="cedula">
                Cédula
            </label>

            <input
                type="text"
                id="cedula"
                name="cedula"

                maxlength="10"

                pattern="[0-9]{10}"

                inputmode="numeric"

                value="<?php
                    echo htmlspecialchars(
                        $fila["cedula"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- NOMBRES -->

        <div class="form-group">

            <label for="nombres">
                Nombres
            </label>

            <input
                type="text"
                id="nombres"
                name="nombres"

                maxlength="100"

                value="<?php
                    echo htmlspecialchars(
                        $fila["nombres"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- APELLIDOS -->

        <div class="form-group">

            <label for="apellidos">
                Apellidos
            </label>

            <input
                type="text"
                id="apellidos"
                name="apellidos"

                maxlength="100"

                value="<?php
                    echo htmlspecialchars(
                        $fila["apellidos"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- TELÉFONO -->

        <div class="form-group">

            <label for="telefono">
                Teléfono
            </label>

            <input
                type="text"
                id="telefono"
                name="telefono"

                maxlength="15"

                pattern="[0-9]{7,15}"

                inputmode="numeric"

                value="<?php
                    echo htmlspecialchars(
                        $fila["telefono"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- CORREO -->

        <div class="form-group">

            <label for="correo">
                Correo
            </label>

            <input
                type="email"
                id="correo"
                name="correo"

                maxlength="150"

                value="<?php
                    echo htmlspecialchars(
                        $fila["correo"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- DIRECCIÓN -->

        <div class="form-group">

            <label for="direccion">
                Dirección
            </label>

            <input
                type="text"
                id="direccion"
                name="direccion"

                maxlength="255"

                value="<?php
                    echo htmlspecialchars(
                        $fila["direccion"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?>"

                required
            >

        </div>


        <!-- BOTONES -->

        <div class="acciones-formulario">

            <button
                type="submit"
                class="btn-guardar"
            >

                Actualizar Cliente

            </button>


            <a
                href="clientes.php"
                class="btn-cancelar"
            >

                Cancelar

            </a>

        </div>


    </form>


</div>


</body>

</html>