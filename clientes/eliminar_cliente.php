<?php

require_once("../config/conexion.php");

$id=$_GET['id'];

$sql="DELETE FROM clientes
WHERE id_cliente='$id'";

mysqli_query($conexion,$sql);

header("Location: clientes.php");

?>