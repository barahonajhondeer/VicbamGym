<?php

require_once("../config/conexion.php");

$id=$_POST['id_cliente'];

$cedula=$_POST['cedula'];

$nombres=$_POST['nombres'];

$apellidos=$_POST['apellidos'];

$telefono=$_POST['telefono'];

$correo=$_POST['correo'];

$direccion=$_POST['direccion'];

$sql="UPDATE clientes SET

cedula='$cedula',

nombres='$nombres',

apellidos='$apellidos',

telefono='$telefono',

correo='$correo',

direccion='$direccion'

WHERE id_cliente='$id'";

mysqli_query($conexion,$sql);

header("Location: clientes.php");

?>