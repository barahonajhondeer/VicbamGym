<?php

require_once("../config/conexion.php");

$id=$_GET['id'];

$sql="SELECT * FROM pagos
WHERE id_pago='$id'";

$resultado=mysqli_query($conexion,$sql);

$pago=mysqli_fetch_assoc($resultado);

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Editar Pago</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>

<nav class="navbar">

<div class="logo-menu">

<h2>VICBAMGYM</h2>

</div>

<ul class="menu">

<li><a href="../dashboard.php">🏠 Dashboard</a></li>

<li><a href="../clientes/clientes.php">Clientes</a></li>

<li><a href="../membresias/membresias.php">Membresías</a></li>

<li><a href="pagos.php">Pagos</a></li>

<li><a href="../logout.php">Salir</a></li>

</ul>

</nav>

<div class="contenedor-principal">

<div class="form-container">

<h2>EDITAR PAGO</h2>

<form action="actualizar_pago.php" method="POST">

<input
type="hidden"
name="id_pago"
value="<?php echo $pago['id_pago'];?>">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" required>

<?php

$sqlClientes="SELECT
id_cliente,
nombres,
apellidos
FROM clientes";

$resultadoClientes=mysqli_query($conexion,$sqlClientes);

while($cliente=mysqli_fetch_assoc($resultadoClientes))
{

?>

<option

value="<?php echo $cliente['id_cliente'];?>"

<?php

if($cliente['id_cliente']==$pago['id_cliente']){

echo "selected";

}

?>

>

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

<select name="id_membresia">

<?php

$sqlM="SELECT
id_membresia,
tipo
FROM membresias";

$resultadoM=mysqli_query($conexion,$sqlM);

while($m=mysqli_fetch_assoc($resultadoM))
{

?>

<option

value="<?php echo $m['id_membresia'];?>"

<?php

if($m['id_membresia']==$pago['id_membresia']){

echo "selected";

}

?>

>

<?php echo $m['tipo'];?>

</option>

<?php

}

?>

</select>

</div>

<div class="form-group">

<label>Valor</label>

<input

type="number"

step="0.01"

name="valor"

value="<?php echo $pago['valor'];?>"

required>

</div>

<div class="form-group">

<label>Método de Pago</label>

<select name="metodo_pago">

<option value="Efectivo"

<?php if($pago['metodo_pago']=="Efectivo") echo "selected";?>

>

Efectivo

</option>

<option value="Transferencia"

<?php if($pago['metodo_pago']=="Transferencia") echo "selected";?>

>

Transferencia

</option>

</select>

</div>

<div class="form-group">

<label>Fecha</label>

<input

type="date"

name="fecha_pago"

value="<?php echo $pago['fecha_pago'];?>"

required>

</div>

<button class="btn-guardar">

Actualizar Pago

</button>

</form>

</div>

</div>

</body>

</html>