<?php

require_once("../config/conexion.php");

$cedula = trim($_POST['cedula'] ?? '');
$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

/* ==========================
   FUNCIÓN PARA REDIRECCIONAR
========================== */

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

/* ==========================
   VALIDAR CAMPOS VACÍOS
========================== */

if (
    $cedula === '' ||
    $nombres === '' ||
    $apellidos === '' ||
    $telefono === '' ||
    $correo === '' ||
    $direccion === ''
) {
    redirigirConMensaje(
        "advertencia",
        "Todos los campos son obligatorios."
    );
}

/* ==========================
   VALIDAR CÉDULA
========================== */

if (!preg_match('/^[0-9]{10}$/', $cedula)) {

    redirigirConMensaje(
        "advertencia",
        "La cédula debe contener exactamente 10 dígitos numéricos."
    );
}

/* ==========================
   VALIDAR CORREO
========================== */

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

    redirigirConMensaje(
        "advertencia",
        "Ingrese un correo electrónico válido."
    );
}

/* ==========================
   VALIDAR CÉDULA REPETIDA
========================== */

$sqlCedula = "SELECT id_cliente
              FROM clientes
              WHERE cedula = ?
              LIMIT 1";

$stmtCedula = mysqli_prepare($conexion, $sqlCedula);

mysqli_stmt_bind_param(
    $stmtCedula,
    "s",
    $cedula
);

mysqli_stmt_execute($stmtCedula);

$resultadoCedula = mysqli_stmt_get_result($stmtCedula);

if (mysqli_num_rows($resultadoCedula) > 0) {

    redirigirConMensaje(
        "advertencia",
        "Ya existe un cliente registrado con esa cédula."
    );
}

/* ==========================
   VALIDAR CORREO REPETIDO
========================== */

$sqlCorreo = "SELECT id_cliente
              FROM clientes
              WHERE correo = ?
              LIMIT 1";

$stmtCorreo = mysqli_prepare($conexion, $sqlCorreo);

mysqli_stmt_bind_param(
    $stmtCorreo,
    "s",
    $correo
);

mysqli_stmt_execute($stmtCorreo);

$resultadoCorreo = mysqli_stmt_get_result($stmtCorreo);

if (mysqli_num_rows($resultadoCorreo) > 0) {

    redirigirConMensaje(
        "advertencia",
        "El correo electrónico ya se encuentra registrado."
    );
}

/* ==========================
   INSERTAR CLIENTE
========================== */

$sqlInsertar = "INSERT INTO clientes
(
    cedula,
    nombres,
    apellidos,
    telefono,
    correo,
    direccion,
    fecha_registro
)
VALUES
(
    ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    CURDATE()
)";

$stmtInsertar = mysqli_prepare(
    $conexion,
    $sqlInsertar
);

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

if (mysqli_stmt_execute($stmtInsertar)) {

    redirigirConMensaje(
        "exito",
        "Cliente registrado correctamente."
    );

} else {

    redirigirConMensaje(
        "error",
        "No se pudo registrar el cliente."
    );
}

?>