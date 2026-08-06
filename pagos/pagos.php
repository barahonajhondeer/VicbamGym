<?php
require_once("../config/conexion.php");
?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Pagos</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>

<!-- MENÚ -->

<nav class="navbar">

<div class="logo-menu">
<h2>VICBAMGYM</h2>
</div>

<ul class="menu">

<li><a href="../dashboard.php">🏠 Dashboard</a></li>

<li><a href="../clientes/clientes.php">👤 Clientes</a></li>

<li><a href="../membresias/membresias.php">💳 Membresías</a></li>

<li><a href="pagos.php">💰 Pagos</a></li>

<li><a href="../reportes/reportes.php">📊 Reportes</a></li>

<li><a href="../logout.php">🚪 Salir</a></li>

</ul>

</nav>

<div class="contenedor-principal">

<!-- FORMULARIO -->

<div class="form-container">

<h2>REGISTRO DE PAGOS</h2>

<form action="guardar_pago.php" method="POST">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" id="id_cliente"> required>

<option value="">Seleccione un cliente</option>

<?php

$sql="SELECT id_cliente,nombres,apellidos
FROM clientes
ORDER BY nombres";

$resultado=mysqli_query($conexion,$sql);

while($cliente=mysqli_fetch_assoc($resultado))
{

?>

<option value="<?php echo $cliente['id_cliente']; ?>">

<?php

echo $cliente['nombres']." ".$cliente['apellidos'];

?>

</option>

<?php


}

?>

</select>

</div>

<div class="form-group">

<label>Membresía</label>

<input
type="hidden"
name="id_membresia"
id="id_membresia">

<div class="form-group">

<label>Tipo de Membresía</label>

<input
type="text"
id="tipo"
readonly>

</div>

<div class="form-group">

<label>Fecha Inicio</label>

<input
type="text"
id="fecha_inicio"
readonly>

</div>

<div class="form-group">

<label>Fecha Fin</label>

<input
type="text"
id="fecha_fin"
readonly>

</div>

<div class="form-group">

<label>Estado</label>

<input
type="text"
id="estado"
readonly>

</div>

<div class="form-group">

    <label>Valor</label>

    <input
        type="text"
        id="valor"
        name="valor"
        readonly>

</div>

</div>

<div class="form-group">

<label>Valor recibido</label>

<input
type="number"
step="0.01"
name="valor"
required>

</div>

<div class="form-group">

<label>Método de Pago</label>

<select name="metodo_pago" required>

<option value="">Seleccione</option>

<option>Efectivo</option>

<option>Transferencia</option>

</select>

</div>

<div class="form-group">

<label>Fecha del Pago</label>

<input
type="date"
name="fecha_pago"
value="<?php echo date('Y-m-d');?>"
required>

</div>

<button class="btn-guardar">

Registrar Pago

</button>

</form>

</div>

<!-- TABLA -->

<div class="tabla-container">

<h2>PAGOS REGISTRADOS</h2>

<table>

<tr>

<th>ID</th>

<th>Cliente</th>

<th>Membresía</th>

<th>Valor</th>

<th>Método</th>

<th>Fecha</th>

<th>Acciones</th>

</tr>

<?php

$sql="SELECT

p.id_pago,

c.nombres,

c.apellidos,

m.tipo,

p.valor,

p.metodo_pago,

p.fecha_pago

FROM pagos p

INNER JOIN clientes c

ON p.id_cliente=c.id_cliente

INNER JOIN membresias m

ON p.id_membresia=m.id_membresia

ORDER BY p.id_pago DESC";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<tr>

<td><?php echo $fila['id_pago']; ?></td>

<td><?php echo $fila['nombres']." ".$fila['apellidos']; ?></td>

<td><?php echo $fila['tipo']; ?></td>

<td>$ <?php echo number_format($fila['valor'],2); ?></td>

<td><?php echo $fila['metodo_pago']; ?></td>

<td><?php echo $fila['fecha_pago']; ?></td>

<td>

<a
class="btn-editar"
href="editar_pago.php?id=<?php echo $fila['id_pago']; ?>">

Editar

</a>

<a
class="btn-eliminar"
href="eliminar_pago.php?id=<?php echo $fila['id_pago']; ?>"
onclick="return confirm('¿Desea eliminar este pago?')">

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

<script>

document.getElementById("id_cliente").addEventListener("change",function(){
    
    console.log("Cliente seleccionado");

let id=this.value;

fetch("obtener_membresia.php",{

method:"POST",

headers:{
'Content-Type':'application/x-www-form-urlencoded'
},

body:"id_cliente="+id

})

.then(response=>response.json())

.then(data=>{

if(data){

document.getElementById("id_membresia").value=data.id_membresia;

document.getElementById("tipo").value=data.tipo;

document.getElementById("valor").value=data.valor; 

document.getElementById("fecha_inicio").value=data.fecha_inicio;

document.getElementById("fecha_fin").value=data.fecha_fin;

document.getElementById("estado").value=data.estado;

}else{

document.getElementById("id_membresia").value="";

document.getElementById("tipo").value="";

document.getElementById("valor").value="";

document.getElementById("fecha_inicio").value="";

document.getElementById("fecha_fin").value="";

document.getElementById("estado").value="";

alert("El cliente no posee una membresía activa.");

}

});

});

</script>

</body>


</html>