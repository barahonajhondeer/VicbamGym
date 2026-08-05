<?php

require_once("../config/conexion.php");

$sql="SELECT * FROM clientes";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

echo "<tr>";

echo "<td>".$fila['cedula']."</td>";

echo "<td>".$fila['nombres']."</td>";

echo "<td>".$fila['apellidos']."</td>";

echo "<td>".$fila['telefono']."</td>";

echo "<td>".$fila['correo']."</td>";

echo "<td>".$fila['direccion']."</td>";

echo "</tr>";

}

?>