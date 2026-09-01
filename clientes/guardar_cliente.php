<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   VALIDAR CSRF
========================================= */

verificar_csrf();


/* =========================================
   SOLO PERMITIR POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: clientes.php");
    exit();
}


/* =========================================
   FUNCIÓN PARA REDIRECCIONAR
========================================= */

function redirigirConMensaje($tipo, $mensaje)
{
    header(
        "Location: clientes.php?tipo=" .
        urlencode($tipo) .
        "&mensaje=" .
        urlencode($mensaje)
    );

    exit();
}


/* =========================================
   RECIBIR Y LIMPIAR DATOS
========================================= */

$cedula =
    trim($_POST["cedula"] ?? "");

$nombres =
    trim($_POST["nombres"] ?? "");

$apellidos =
    trim($_POST["apellidos"] ?? "");

$telefono =
    trim($_POST["telefono"] ?? "");

$correo =
    strtolower(
        trim($_POST["correo"] ?? "")
    );

$direccion =
    trim($_POST["direccion"] ?? "");


/* =========================================
   VALIDAR CAMPOS VACÍOS
========================================= */

if (
    $cedula === "" ||
    $nombres === "" ||
    $apellidos === "" ||
    $telefono === "" ||
    $correo === "" ||
    $direccion === ""
) {

    redirigirConMensaje(
        "advertencia",
        "Todos los campos son obligatorios."
    );
}


/* =========================================
   VALIDAR CÉDULA
========================================= */

if (
    !preg_match(
        '/^[0-9]{10}$/',
        $cedula
    )
) {

    redirigirConMensaje(
        "advertencia",
        "La cédula debe contener exactamente 10 dígitos numéricos."
    );
}


/* =========================================
   VALIDAR NOMBRES
========================================= */

if (
    mb_strlen($nombres) < 2 ||
    mb_strlen($nombres) > 100
) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese nombres válidos."
    );
}


/* =========================================
   VALIDAR APELLIDOS
========================================= */

if (
    mb_strlen($apellidos) < 2 ||
    mb_strlen($apellidos) > 100
) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese apellidos válidos."
    );
}


/* =========================================
   VALIDAR TELÉFONO
========================================= */

/*
   Permite únicamente números
   entre 7 y 15 dígitos.
*/

if (
    !preg_match(
        '/^[0-9]{7,15}$/',
        $telefono
    )
) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese un número de teléfono válido."
    );
}


/* =========================================
   VALIDAR CORREO
========================================= */

if (
    !filter_var(
        $correo,
        FILTER_VALIDATE_EMAIL
    )
) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese un correo electrónico válido."
    );
}


/* =========================================
   VALIDAR LONGITUD DEL CORREO
========================================= */

if (
    strlen($correo) > 150
) {

    redirigirConMensaje(
        "advertencia",
        "El correo electrónico es demasiado largo."
    );
}


/* =========================================
   VALIDAR DIRECCIÓN
========================================= */

if (
    mb_strlen($direccion) < 3 ||
    mb_strlen($direccion) > 255
) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese una dirección válida."
    );
}


/* =========================================
   VALIDAR CÉDULA REPETIDA
========================================= */

$sqlCedula = "
    SELECT id_cliente
    FROM clientes
    WHERE cedula = ?
    LIMIT 1
";

$stmtCedula =
    mysqli_prepare(
        $conexion,
        $sqlCedula
    );


if (!$stmtCedula) {

    error_log(
        "Error preparando consulta de cédula: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "Ocurrió un problema al procesar la solicitud."
    );
}


mysqli_stmt_bind_param(
    $stmtCedula,
    "s",
    $cedula
);


mysqli_stmt_execute(
    $stmtCedula
);


mysqli_stmt_store_result(
    $stmtCedula
);


if (
    mysqli_stmt_num_rows(
        $stmtCedula
    ) > 0
) {

    mysqli_stmt_close(
        $stmtCedula
    );

    redirigirConMensaje(
        "advertencia",
        "Ya existe un cliente registrado con esa cédula."
    );
}


mysqli_stmt_close(
    $stmtCedula
);


/* =========================================
   VALIDAR CORREO REPETIDO
========================================= */

$sqlCorreo = "
    SELECT id_cliente
    FROM clientes
    WHERE correo = ?
    LIMIT 1
";

$stmtCorreo =
    mysqli_prepare(
        $conexion,
        $sqlCorreo
    );


if (!$stmtCorreo) {

    error_log(
        "Error preparando consulta de correo: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "Ocurrió un problema al procesar la solicitud."
    );
}


mysqli_stmt_bind_param(
    $stmtCorreo,
    "s",
    $correo
);


mysqli_stmt_execute(
    $stmtCorreo
);


mysqli_stmt_store_result(
    $stmtCorreo
);


if (
    mysqli_stmt_num_rows(
        $stmtCorreo
    ) > 0
) {

    mysqli_stmt_close(
        $stmtCorreo
    );

    redirigirConMensaje(
        "advertencia",
        "El correo electrónico ya se encuentra registrado."
    );
}


mysqli_stmt_close(
    $stmtCorreo
);


/* =========================================
   INSERTAR CLIENTE
========================================= */

$sqlInsertar = "
    INSERT INTO clientes
    (
        cedula,
        nombres,
        apellidos,
        telefono,
        correo,
        direccion,
        fecha_registro,
        estado
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        CURDATE(),
        'Activo'
    )
";


$stmtInsertar =
    mysqli_prepare(
        $conexion,
        $sqlInsertar
    );


if (!$stmtInsertar) {

    error_log(
        "Error preparando INSERT cliente: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "No se pudo registrar el cliente."
    );
}


mysqli_stmt_bind_param(
    $stmtInsertar,
    "ssssss",
    $cedula,
    $nombres,
    $apellidos,
    $telefono,
    $correo,
    $direccion
);


/* =========================================
   EJECUTAR REGISTRO
========================================= */

if (
    mysqli_stmt_execute(
        $stmtInsertar
    )
) {

    mysqli_stmt_close(
        $stmtInsertar
    );

    redirigirConMensaje(
        "exito",
        "Cliente registrado correctamente."
    );

} else {

    error_log(
        "Error registrando cliente: " .
        mysqli_stmt_error(
            $stmtInsertar
        )
    );

    mysqli_stmt_close(
        $stmtInsertar
    );

    redirigirConMensaje(
        "error",
        "No se pudo registrar el cliente."
    );
}