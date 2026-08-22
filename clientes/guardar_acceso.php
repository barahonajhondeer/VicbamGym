<?php

require_once("../config/conexion.php");

$id_cliente = $_POST['id_cliente'] ?? null;
$password = $_POST['password'] ?? "";
$confirmar = $_POST['confirmar_password'] ?? "";

if (!$id_cliente) {
    die("Cliente no válido.");
}

if (strlen($password) < 6) {

    echo "
    <script>
        alert('La contraseña debe tener mínimo 6 caracteres.');
        history.back();
    </script>
    ";

    exit;
}

if ($password !== $confirmar) {

    echo "
    <script>
        alert('Las contraseñas no coinciden.');
        history.back();
    </script>
    ";

    exit;
}

$hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$sql = "
    INSERT INTO cuentas_clientes
        (id_cliente, password)
    VALUES (?, ?)

    ON DUPLICATE KEY UPDATE
        password = ?
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "iss",
    $id_cliente,
    $hash,
    $hash
);

if ($stmt->execute()) {

    echo "
    <script>
        alert('Acceso actualizado correctamente.');
        window.location='clientes.php';
    </script>
    ";

} else {

    echo "
    <script>
        alert('Error al guardar el acceso.');
        history.back();
    </script>
    ";
}