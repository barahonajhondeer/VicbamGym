<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>VICBAMGYM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-body"></bodyclass>>

<?php
require_once("config/notificaciones.php");
?>

<div class="login-container">

    <h1>VICBAMGYM</h1>
    <h3>Control de Clientes y Membresías</h3>

    <form action="login.php" method="POST">

        <label>Usuario</label>
        <input type="text" name="usuario" required>

        <label>Contraseña</label>
        <input type="password" name="password" required>

        <button type="submit">INGRESAR</button>

    </form>

</div>

</body>
</html>