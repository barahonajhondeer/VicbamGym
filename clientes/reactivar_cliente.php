<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header("../dashboard.php");
    exit();
}

$id_cliente = (int) ($_GET["id"] ?? 0);

if ($id_cliente <= 0) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode("Cliente no válido.")
    );

    exit();
}

$sql = "UPDATE clientes
        SET estado = 'Activo'
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
            "Cliente reactivado correctamente."
        )
    );

} else {

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode(
            "No se pudo reactivar el cliente."
        )
    );
}

exit();

?>