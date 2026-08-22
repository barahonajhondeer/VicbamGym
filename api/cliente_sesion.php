<?php

header("Content-Type: application/json; charset=UTF-8");

require_once("../config/conexion.php");

$id_cliente = $_GET["id_cliente"] ?? null;

if (!$id_cliente) {

    echo json_encode([
        "success" => false,
        "message" => "Cliente no válido"
    ]);

    exit;
}

$sqlCliente = "
    SELECT
        id_cliente,
        cedula,
        nombres,
        apellidos,
        telefono,
        correo,
        direccion
    FROM clientes
    WHERE id_cliente = ?
";

$stmt = $conexion->prepare($sqlCliente);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Cliente no encontrado"
    ]);

    exit;
}

$cliente = $resultado->fetch_assoc();

$sqlMembresia = "
    SELECT
        id_membresia,
        tipo,
        fecha_inicio,
        fecha_fin,
        estado
    FROM membresias
    WHERE id_cliente = ?
      AND estado = 'Activa'
    ORDER BY fecha_fin DESC
    LIMIT 1
";

$stmtMembresia = $conexion->prepare($sqlMembresia);
$stmtMembresia->bind_param("i", $id_cliente);
$stmtMembresia->execute();

$resultadoMembresia = $stmtMembresia->get_result();

$membresia = null;

if ($resultadoMembresia->num_rows > 0) {
    $membresia = $resultadoMembresia->fetch_assoc();
}

echo json_encode([
    "success" => true,
    "message" => "Sesión recuperada correctamente",
    "cliente" => $cliente,
    "membresia" => $membresia
]);