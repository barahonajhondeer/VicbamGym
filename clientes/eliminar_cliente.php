<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: clientes.php");
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

/* =========================
   VERIFICAR MEMBRESÍAS
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM membresias
        WHERE id_cliente = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$datos = mysqli_fetch_assoc($resultado);

if ((int) $datos["total"] > 0) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede eliminar el cliente porque posee membresías registradas."
        )
    );

    exit();
}

/* =========================
   VERIFICAR PAGOS
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM pagos
        WHERE id_cliente = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_cliente
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$datos = mysqli_fetch_assoc($resultado);

if ((int) $datos["total"] > 0) {

    header(
        "Location: clientes.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede eliminar el cliente porque posee pagos registrados."
        )
    );

    exit();
}

/* =========================
   ELIMINAR
========================= */

$sql = "DELETE FROM clientes
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
        urlencode("Cliente eliminado correctamente.")
    );

} else {

    header(
        "Location: clientes.php?tipo=error&mensaje=" .
        urlencode("No se pudo eliminar el cliente.")
    );
}

exit();

?>
