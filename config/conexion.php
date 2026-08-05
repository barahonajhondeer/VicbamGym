<?php

$conexion = mysqli_connect(
    "localhost",
    "root",
    "",
    "vicbamgym"
);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>