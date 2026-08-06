<?php

require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");

if ($_SESSION["rol"] !== "Administrador") {

    echo "<script>
        alert('No tiene permisos para realizar esta acción.');
        window.location='clientes.php';
    </script>";

    exit();
}

$id=$_GET['id'];

$sql="DELETE FROM clientes
WHERE id_cliente='$id'";

mysqli_query($conexion,$sql);

header("Location: clientes.php");

?>