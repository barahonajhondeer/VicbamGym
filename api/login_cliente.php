<?php

header("Content-Type: application/json");

require_once("../config/conexion.php");

$data = json_decode(file_get_contents("php://input"), true);

$cedula = $data["cedula"] ?? "";
$password = $data["password"] ?? "";

if (empty($cedula) || empty($password)) {

    echo json_encode([
        "success" => false,
        "message" => "Cédula y contraseña son obligatorias"
    ]);

    exit;
}

$sql = "
    SELECT
        c.id_cliente,
        c.cedula,
        c.nombres,
        c.apellidos,
        c.telefono,
        c.correo,
        c.direccion,
        cc.password
    FROM clientes c
    INNER JOIN cuentas_clientes cc
        ON c.id_cliente = cc.id_cliente
    WHERE c.cedula = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado"
    ]);

    exit;
}

$cliente = $resultado->fetch_assoc();

if (!password_verify($password, $cliente["password"])) {

    echo json_encode([
        "success" => false,
        "message" => "Contraseña incorrecta"
    ]);

    exit;
}

unset($cliente["password"]);

// BUSCAR MEMBRESÍA ACTIVA

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

$stmtMembresia->bind_param(
    "i",
    $cliente["id_cliente"]
);

$stmtMembresia->execute();

$resultadoMembresia =
    $stmtMembresia->get_result();

$membresia = null;

if ($resultadoMembresia->num_rows > 0) {

    $membresia =
        $resultadoMembresia->fetch_assoc();
}

echo json_encode([
    "success" => true,
    "message" => "Inicio de sesión correcto",
    "cliente" => $cliente,
    "membresia" => $membresia
]);