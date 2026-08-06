<?php

require_once("../config/conexion.php");

// Verificar que llegue el ID
if(!isset($_GET['id']))
{
    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode("No se recibió el identificador del pago.")
    );
    
    exit(); 
}

$id = $_GET['id'];

// Verificar que exista

$sql = "SELECT * FROM pagos
WHERE id_pago='$id'";

$resultado = mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado)==0)
{
    echo "<script>

    alert('El pago no existe.');

    window.location='pagos.php';

    </script>";

    exit();
}

// Eliminar

$sqlEliminar = "DELETE FROM pagos
WHERE id_pago='$id'";

if(mysqli_query($conexion,$sqlEliminar))
{
    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode("Pago eliminado correctamente.")
    );
    
    exit();;
}
else
{
    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode("No se pudo eliminar el pago.")
    );
    
    exit();
}

?>