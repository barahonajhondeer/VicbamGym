<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Dashboard | VICBAMGYM</title>

<link rel="stylesheet" href="assets/css/styles.css">

</head>

<body class="dashboard-body">

<div class="sidebar">

    <h2>VICBAMGYM</h2>

    <ul>

        <li><a href="clientes/clientes.php">👥 Clientes</a></li>

        <li><a href="membresias/membresias.php">💳 Membresías</a></li>

        <li><a href="pagos/pagos.php">💲 Pagos</a></li>

        <li><a href="reportes.php">📊 Reportes</a></li>

        <li><a href="usuarios.php">⚙ Usuarios</a></li>

        <li><a href="logout.php">🚪 Cerrar Sesión</a></li>

    </ul>

</div>

<div class="contenido">

    <div class="header">

        <h1>Panel Administrativo</h1>

    </div>

    <div class="cards">

    <a href="clientes/clientes.php" class="card-link">
    <div class="card">
        <h3>Clientes</h3>
        <p>Administrar clientes registrados.</p>
    </div>
</a>

<a href="membresias/membresias.php" class="card-link">
    <div class="card">
        <h3>Membresías</h3>
        <p>Control de membresías.</p>
    </div>
</a>

<a href="pagos/pagos.php" class="card-link">
    <div class="card">
        <h3>Pagos</h3>
        <p>Gestión de pagos.</p>
    </div>
</a>

    </div>

    <div class="bienvenida">

        <h2>Bienvenido a VICBAMGYM</h2>

        <p>

        Sistema de Gestión para Clientes, Membresías y Pagos.

        </p>

    </div>

</div>

</body>

</html>