<?php

require_once("../config/conexion.php");
require_once("../config/csrf.php");

$id = $_GET['id'];

$sql = "SELECT * FROM membresias
WHERE id_membresia='$id'";

$resultado = mysqli_query($conexion,$sql);

$fila = mysqli_fetch_assoc($resultado);

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Editar Membresía</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>

<div class="form-container">

<h2>EDITAR MEMBRESÍA</h2>

<form action="actualizar_membresia.php" method="POST">
<?php echo csrf_field(); ?>

<input
type="hidden"
name="id_membresia"
value="<?php echo $fila['id_membresia']; ?>">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" required>

<?php

$sqlClientes = "SELECT
id_cliente,
nombres,
apellidos
FROM clientes
ORDER BY nombres";

$resultadoClientes = mysqli_query($conexion,$sqlClientes);

while($cliente=mysqli_fetch_assoc($resultadoClientes))
{

?>

<option
value="<?php echo $cliente['id_cliente']; ?>"

<?php

if($cliente['id_cliente']==$fila['id_cliente'])
{

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

<label>Tipo de Membresía</label>

<select name="tipo">

<option value="Mensual"
<?php if($fila['tipo']=="Mensual") echo "selected"; ?>>

Mensual

</option>

<option value="Trimestral"
<?php if($fila['tipo']=="Trimestral") echo "selected"; ?>>

Trimestral

</option>

<option value="Semestral"
<?php if($fila['tipo']=="Semestral") echo "selected"; ?>>

Semestral

</option>

<option value="Anual"
<?php if($fila['tipo']=="Anual") echo "selected"; ?>>

Anual

</option>

</select>

</div>

<div class="form-group">

<label>Fecha Inicio</label>

<input
type="date"
name="fecha_inicio"
value="<?php echo $fila['fecha_inicio']; ?>"
required>

</div>

<button class="btn-guardar">

Actualizar Membresía

</button>

</form>

</div>

</body>

</html>