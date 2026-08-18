<?php

require_once("../config/verificar_sesion.php");
require_once("../config/conexion.php");

if ($_SESSION["rol"] !== "Administrador") {
    header("Location: membresias.php");
    exit();
}

$id_membresia = (int) ($_GET["id"] ?? 0);

if ($id_membresia <= 0) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode("Membresía no válida.")
    );

    exit();
}

/* =========================
   VERIFICAR PAGOS
========================= */

$sql = "SELECT COUNT(*) AS total
        FROM pagos
        WHERE id_membresia = ?";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_membresia
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$datos = mysqli_fetch_assoc($resultado);

if ((int) $datos["total"] > 0) {

    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode(
            "No se puede eliminar la membresía porque posee pagos registrados."
        )
    );

    exit();
}

/* =========================
   ELIMINAR
========================= */

$sql = "DELETE FROM membresias
        WHERE id_membresia = ?";

$stmt = mysqli_prepare(
    $conexion,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_membresia
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: membresias.php?tipo=exito&mensaje=" .
        urlencode("Membresía eliminada correctamente.")
    );

} else {

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode("No se pudo eliminar la membresía.")
    );
}

exit();

?>