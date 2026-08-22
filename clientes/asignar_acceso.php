<?php

require_once("../config/conexion.php");

$id_cliente = $_GET['id_cliente'] ?? null;

if (!$id_cliente) {
    die("Cliente no válido.");
}

$sql = "
    SELECT id_cliente, cedula, nombres, apellidos
    FROM clientes
    WHERE id_cliente = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Cliente no encontrado.");
}

$cliente = $resultado->fetch_assoc();

$sqlCuenta = "
    SELECT id_cuenta
    FROM cuentas_clientes
    WHERE id_cliente = ?
";

$stmtCuenta = $conexion->prepare($sqlCuenta);
$stmtCuenta->bind_param("i", $id_cliente);
$stmtCuenta->execute();

$tieneCuenta = $stmtCuenta->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acceso VICBAMGYM</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>

<div class="acceso-container">

    <h2 class="acceso-titulo">
        <?php
        echo $tieneCuenta
            ? "CAMBIAR CONTRASEÑA"
            : "CREAR ACCESO";
        ?>
    </h2>

    <div class="datos-cliente">

        <p>
            <span>Cliente:</span>

            <strong>
                <?= htmlspecialchars(
                    $cliente['nombres'] . ' ' .
                    $cliente['apellidos']
                ) ?>
            </strong>
        </p>

        <p>
            <span>Cédula:</span>

            <strong>
                <?= htmlspecialchars($cliente['cedula']) ?>
            </strong>
        </p>

    </div>

    <form
        action="guardar_acceso.php"
        method="POST"
        class="form-acceso"
    >

        <input
            type="hidden"
            name="id_cliente"
            value="<?= $cliente['id_cliente'] ?>"
        >

        <label>
            Nueva contraseña
        </label>

        <input
            type="password"
            name="password"
            minlength="6"
            required
            placeholder="Mínimo 6 caracteres"
        >

        <label>
            Confirmar contraseña
        </label>

        <input
            type="password"
            name="confirmar_password"
            minlength="6"
            required
            placeholder="Repita la contraseña"
        >

        <button type="submit">
            Guardar contraseña
        </button>

        <a
            href="clientes.php"
            class="btn-volver"
        >
            Volver a clientes
        </a>

    </form>

</div>
</body>
</html>