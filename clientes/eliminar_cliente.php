<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

/* =================================
   SOLO ADMINISTRADOR
================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No tiene permisos para realizar esta acción."
        )
    );

    exit();
}

/* =================================
   RECIBIR ID
================================= */

$id_cliente = (int) ($_GET["id"] ?? 0);

if ($id_cliente <= 0) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no es válido."
        )
    );

    exit();
}

/* =================================
   COMPROBAR QUE EXISTA
================================= */

$sqlExiste = "SELECT id_cliente
              FROM clientes
              WHERE id_cliente = ?
              LIMIT 1";

$stmtExiste = mysqli_prepare(
    $conexion,
    $sqlExiste
);

mysqli_stmt_bind_param(
    $stmtExiste,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmtExiste);

$resultadoExiste =
    mysqli_stmt_get_result($stmtExiste);

if (
    mysqli_num_rows($resultadoExiste) === 0
) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "El cliente seleccionado no existe."
        )
    );

    exit();
}

/* =================================
   DESACTIVAR CLIENTE
================================= */

$sql = "UPDATE clientes
        SET estado = 'Inactivo'
        WHERE id_cliente = ?";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: clientes.php?tipo=exito&mensaje=" .
        urlencode(
            "Cliente desactivado correctamente. Su historial se conserva."
        )
    );

} else {

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo desactivar el cliente."
        )
    );
}

exit();

?>
