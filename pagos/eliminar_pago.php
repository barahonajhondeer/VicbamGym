<?php

require_once("../config/conexion.php");

// Verificar que llegue el ID
if(!isset($_GET['id']))
{
    echo "<script>

    alert('No se recibió el identificador del pago.');

    window.location='pagos.php';

    </script>";

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
    echo "<script>

    alert('Pago eliminado correctamente.');

    window.location='pagos.php';

    </script>";
}
else
{
    echo "<script>

    alert('Error al eliminar el pago.');

    window.location='pagos.php';

    </script>";
}

?>