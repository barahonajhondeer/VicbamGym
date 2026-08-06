<?php
require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");
?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Clientes</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>
<nav class="navbar">

<div class="logo-menu">
    <h2>VICBAMGYM</h2>
</div>

<ul class="menu">

    <li><a href="../dashboard.php">🏠 Dashboard</a></li>

    <li><a href="../clientes/clientes.php">👤 Clientes</a></li>

    <li><a href="../membresias/membresias.php">💳 Membresías</a></li>

    <li><a href="../pagos/pagos.php">💰 Pagos</a></li>

    <li><a href="../reportes/reportes.php">📊 Reportes</a></li>

    <li><a href="../usuarios/usuarios.php">👨‍💼 Usuarios</a></li>

    <li><a href="../logout.php">🚪 Salir</a></li>

</ul>

</nav>

<?php
require_once("../config/notificaciones.php");
?>

<div class="contenedor-principal">

<div class="form-container">

<h2>REGISTRO DE CLIENTES</h2>

<form action="guardar_cliente.php" method="POST">

<div class="form-group">
    <label>Cédula</label>
    <input
        type="text"
        name="cedula"
        id="cedula"
        maxlength="10"
        pattern="[0-9]{10}"
        title="La cédula debe contener exactamente 10 dígitos Númericos."
        required>
</div>

<div class="form-group">

<label>Nombres</label>

<input 
        type="text" 
        name="nombres"
        required>

</div>

<div class="form-group">

<label>Apellidos</label>

<input type="text" name="apellidos" required>

</div>

<div class="form-group">

<label>Teléfono</label>

<input type="text" name="telefono" required>

</div>

<div class="form-group">
    <label>Correo</label>
    <input
        type="email"
        name="correo"
        id="correo"
        title="El Correo debe ser como el ejemplo: usuario@email.com."
        required>
</div>

<div class="form-group">

<label>Dirección</label>

<input type="text" name="direccion" required>

</div>

<button class="btn-guardar">

Guardar Cliente

</button>

</form>

</div>

<div class="tabla-container">

    <h2>CLIENTES REGISTRADOS</h2>

<table>

    <tr>
        <th>ID</th>
        <th>Cédula</th>
        <th>Nombres</th>
        <th>Apellidos</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Dirección</th>
        <th>Acciones</th>
    </tr>

<?php

$sql = "SELECT * FROM clientes";

$resultado = mysqli_query($conexion,$sql);

while($fila = mysqli_fetch_assoc($resultado))
{
?>

<tr>

    <td><?php echo $fila['id_cliente']; ?></td>

    <td><?php echo $fila['cedula']; ?></td>

    <td><?php echo $fila['nombres']; ?></td>

    <td><?php echo $fila['apellidos']; ?></td>

    <td><?php echo $fila['telefono']; ?></td>

    <td><?php echo $fila['correo']; ?></td>

    <td><?php echo $fila['direccion']; ?></td>

    <td>

 <a href="historial_cliente.php?id=<?php echo $fila['id_cliente']; ?>"  class="btn-historial">
    Historial

</a>

<a href="editar_cliente.php?id=<?php echo $fila['id_cliente']; ?>" class="btn-editar">
    Editar
</a>

<a
href="eliminar_cliente.php?id=<?php echo $fila['id_cliente']; ?>"
class="btn-eliminar"
onclick="return confirm('¿Está seguro de eliminar este cliente?');">

Eliminar

</a>

</td>

</tr>

<?php
}
?>

</table>

</div>

</div>

</body>

</html>