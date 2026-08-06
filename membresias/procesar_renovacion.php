<?php

require_once("../config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: membresias.php");
    exit();
}

$id_membresia = (int) ($_POST["id_membresia"] ?? 0);
$tipo = trim($_POST["tipo"] ?? "");
$valor = (float) ($_POST["valor"] ?? 0);
$fecha_inicio = $_POST["fecha_inicio"] ?? "";

$tiposPermitidos = [
    "Mensual" => 1,
    "Trimestral" => 3,
    "Semestral" => 6,
    "Anual" => 12
];

if (
    $id_membresia <= 0 ||
    !isset($tiposPermitidos[$tipo]) ||
    $valor <= 0 ||
    empty($fecha_inicio)
) {

    echo "<script>
        alert('Los datos de renovación no son válidos.');
        window.location='membresias.php';
    </script>";

    exit();
}

$meses = $tiposPermitidos[$tipo];

$fecha = new DateTime($fecha_inicio);
$fecha->modify("+$meses months");

$fecha_fin = $fecha->format("Y-m-d");

$sql = "UPDATE membresias
        SET tipo = ?,
            valor = ?,
            fecha_inicio = ?,
            fecha_fin = ?,
            estado = 'Activa'
        WHERE id_membresia = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sdssi",
    $tipo,
    $valor,
    $fecha_inicio,
    $fecha_fin,
    $id_membresia
);

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: membresias.php?tipo=exito&mensaje=" .
        urlencode("Membresía renovada correctamente.")
    );
    exit();

} else {

    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode("No se pudo renovar la membresía.")
    );
    exit();
}

mysqli_stmt_close($stmt);

?>