<?php
require_once("../config/conexion.php");

$id = $_GET['id'];

$sql = "SELECT * FROM clientes WHERE id_cliente='$id'";

$resultado = mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado)==0){

    echo "Cliente no encontrado.";
    
    exit();
    
    }

$fila = mysqli_fetch_assoc($resultado);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Editar Cliente</title>

<link rel="stylesheet" href="assets/css/styles.css">

</head>

<body class="clientes-body">

<div class="form-container">

<h2>EDITAR CLIENTE</h2>

<form action="actualizar_cliente.php" method="POST">

<input
type="hidden"
name="id_cliente"
value="<?php echo $fila['id_cliente']; ?>">

<div class="form-group">

<label>Cédula</label>

<input
type="text"
name="cedula"
value="<?php echo $fila['cedula']; ?>"
required>

</div>

<div class="form-group">

<label>Nombres</label>

<input
type="text"
name="nombres"
value="<?php echo $fila['nombres']; ?>"
required>

</div>

<div class="form-group">

<label>Apellidos</label>

<input
type="text"
name="apellidos"
value="<?php echo $fila['apellidos']; ?>"
required>

</div>

<div class="form-group">

<label>Teléfono</label>

<input
type="text"
name="telefono"
value="<?php echo $fila['telefono']; ?>"
required>

</div>

<div class="form-group">

<label>Correo</label>

<input
type="email"
name="correo"
value="<?php echo $fila['correo']; ?>"
required>

</div>

<div class="form-group">

<label>Dirección</label>

<input
type="text"
name="direccion"
value="<?php echo $fila['direccion']; ?>"
required>

</div>

<button class="btn-guardar">

Actualizar Cliente

</button>

</form>

</div>

</body>

</html>