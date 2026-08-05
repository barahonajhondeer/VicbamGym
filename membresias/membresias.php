<?php
require_once("../config/conexion.php");
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Membresías</title>

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

<div class="contenedor-principal">

<!-- ========================= -->
<!-- FORMULARIO -->
<!-- ========================= -->

<div class="form-container">

<h2>REGISTRO DE MEMBRESÍAS</h2>

<form action="guardar_membresia.php" method="POST">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" required>

<option value="">Seleccione un cliente</option>

<?php

$sql="SELECT id_cliente,nombres,apellidos
FROM clientes
ORDER BY nombres";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<option
value="<?php echo $fila['id_cliente']; ?>">

<?php
echo $fila['nombres']." ".$fila['apellidos'];
?>

</option>

<?php
}
?>

</select>

</div>

<div class="form-group">

<label>Tipo de Membresía</label>

<select name="tipo" required>

<option value="">Seleccione</option>

<option value="Mensual">Mensual</option>

<option value="Trimestral">Trimestral</option>

<option value="Semestral">Semestral</option>

<option value="Anual">Anual</option>

</select>

</div>

<div class="form-group">

<label>Fecha Inicio</label>

<input
type="date"
name="fecha_inicio"
required>

</div>

<button class="btn-guardar">

Guardar Membresía

</button>

</form>

</div>

<!-- ========================= -->
<!-- TABLA -->
<!-- ========================= -->

<div class="tabla-container">

<h2>MEMBRESÍAS REGISTRADAS</h2>

<table>

<tr>

<th>ID</th>

<th>Cliente</th>

<th>Tipo</th>

<th>Inicio</th>

<th>Fin</th>

<th>Estado</th>

<th>Acciones</th>

</tr>

<?php

$sql="SELECT

m.id_membresia,

c.nombres,

c.apellidos,

m.tipo,

m.fecha_inicio,

m.fecha_fin,

m.estado

FROM membresias m

INNER JOIN clientes c

ON m.id_cliente=c.id_cliente

ORDER BY m.id_membresia DESC";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<tr>

<td>

<?php echo $fila['id_membresia']; ?>

</td>

<td>

<?php
echo $fila['nombres']." ".$fila['apellidos'];
?>

</td>

<td>

<?php echo $fila['tipo']; ?>

</td>

<td>

<?php echo $fila['fecha_inicio']; ?>

</td>

<td>

<?php echo $fila['fecha_fin']; ?>

</td>

<td>

<?php

if($fila['estado']=="Activa")
{

echo "<span class='estado-activa'>Activa</span>";

}
else
{

echo "<span class='estado-vencida'>Vencida</span>";

}

?>

</td>

<td>

<a
class="btn-editar"
href="editar_membresia.php?id=<?php echo $fila['id_membresia']; ?>">

Editar

</a>

<a
class="btn-eliminar"
href="eliminar_membresia.php?id=<?php echo $fila['id_membresia']; ?>"
onclick="return confirm('¿Desea eliminar esta membresía?')">

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