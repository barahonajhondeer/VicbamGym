<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");
require_once("../config/csrf.php");


/* =========================================
   VALIDAR CSRF
========================================= */

verificar_csrf();


/* =========================================
   SOLO POST
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
   RECIBIR DATOS
========================================= */

$id =
    filter_input(
        INPUT_POST,
        "id_cliente",
        FILTER_VALIDATE_INT
    );

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
   VALIDAR ID
========================================= */

if (
    !$id ||
    $id <= 0
) {

    redirigirConMensaje(
        "error",
        "Cliente no válido."
    );
}


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
   VERIFICAR QUE EL CLIENTE EXISTE
========================================= */

$sqlExiste = "
    SELECT id_cliente
    FROM clientes
    WHERE id_cliente = ?
    LIMIT 1
";

$stmtExiste =
    mysqli_prepare(
        $conexion,
        $sqlExiste
    );


if (!$stmtExiste) {

    error_log(
        "Error preparando consulta cliente existente: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "No se pudo procesar la solicitud."
    );
}


mysqli_stmt_bind_param(
    $stmtExiste,
    "i",
    $id
);


mysqli_stmt_execute(
    $stmtExiste
);


mysqli_stmt_store_result(
    $stmtExiste
);


if (
    mysqli_stmt_num_rows(
        $stmtExiste
    ) === 0
) {

    mysqli_stmt_close(
        $stmtExiste
    );

    redirigirConMensaje(
        "advertencia",
        "El cliente no existe."
    );
}


mysqli_stmt_close(
    $stmtExiste
);


/* =========================================
   VALIDAR CÉDULA REPETIDA
   EXCLUYENDO EL CLIENTE ACTUAL
========================================= */

$sqlCedula = "
    SELECT id_cliente
    FROM clientes
    WHERE cedula = ?
    AND id_cliente <> ?
    LIMIT 1
";

$stmtCedula =
    mysqli_prepare(
        $conexion,
        $sqlCedula
    );


if (!$stmtCedula) {

    error_log(
        "Error preparando validación de cédula: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "No se pudo procesar la solicitud."
    );
}


mysqli_stmt_bind_param(
    $stmtCedula,
    "si",
    $cedula,
    $id
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
        "Ya existe otro cliente registrado con esa cédula."
    );
}


mysqli_stmt_close(
    $stmtCedula
);


/* =========================================
   VALIDAR CORREO REPETIDO
   EXCLUYENDO EL CLIENTE ACTUAL
========================================= */

$sqlCorreo = "
    SELECT id_cliente
    FROM clientes
    WHERE correo = ?
    AND id_cliente <> ?
    LIMIT 1
";

$stmtCorreo =
    mysqli_prepare(
        $conexion,
        $sqlCorreo
    );


if (!$stmtCorreo) {

    error_log(
        "Error preparando validación de correo: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "No se pudo procesar la solicitud."
    );
}


mysqli_stmt_bind_param(
    $stmtCorreo,
    "si",
    $correo,
    $id
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
        "El correo electrónico ya pertenece a otro cliente."
    );
}


mysqli_stmt_close(
    $stmtCorreo
);


/* =========================================
   ACTUALIZAR CLIENTE
========================================= */

$sqlActualizar = "
    UPDATE clientes

    SET
        cedula = ?,
        nombres = ?,
        apellidos = ?,
        telefono = ?,
        correo = ?,
        direccion = ?

    WHERE id_cliente = ?
";


$stmtActualizar =
    mysqli_prepare(
        $conexion,
        $sqlActualizar
    );


if (!$stmtActualizar) {

    error_log(
        "Error preparando UPDATE cliente: " .
        mysqli_error($conexion)
    );

    redirigirConMensaje(
        "error",
        "No se pudo actualizar el cliente."
    );
}


mysqli_stmt_bind_param(
    $stmtActualizar,
    "ssssssi",
    $cedula,
    $nombres,
    $apellidos,
    $telefono,
    $correo,
    $direccion,
    $id
);


/* =========================================
   EJECUTAR
========================================= */

if (
    mysqli_stmt_execute(
        $stmtActualizar
    )
) {

    mysqli_stmt_close(
        $stmtActualizar
    );

    redirigirConMensaje(
        "exito",
        "Cliente actualizado correctamente."
    );

} else {

    error_log(
        "Error actualizando cliente: " .
        mysqli_stmt_error(
            $stmtActualizar
        )
    );

    mysqli_stmt_close(
        $stmtActualizar
    );

    redirigirConMensaje(
        "error",
        "No se pudo actualizar el cliente."
    );
}