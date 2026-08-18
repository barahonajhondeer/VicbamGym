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

$sqlEliminar="DELETE FROM clientes
WHERE id_cliente='$id'";

mysqli_query($conexion,$sql);

header("Location: clientes.php");

if(mysqli_query($conexion,$sqlEliminar))
{
    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode("Cliente eliminado correctamente.")
    );
    
    exit();;
}
else
{
    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode("No se pudo eliminar el Cliente.")
    );
    
    exit();
}

?>


