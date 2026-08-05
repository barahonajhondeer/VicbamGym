<?php

require_once("../config/conexion.php");

// Verificar que llegue el ID
if(!isset($_GET['id']))
{
    echo "<script>

    alert('No se recibió el identificador de la membresía.');

    window.location='membresias.php';

    </script>";

    exit();
}

$id = $_GET['id'];

// Verificar que exista la membresía

$sql = "SELECT * FROM membresias
WHERE id_membresia='$id'";

$resultado = mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado)==0)
{
    echo "<script>

    alert('La membresía no existe.');

    window.location='membresias.php';

    </script>";

    exit();
}

// Eliminar

$sqlEliminar = "DELETE FROM membresias
WHERE id_membresia='$id'";

if(mysqli_query($conexion,$sqlEliminar))
{
    echo "<script>

    alert('Membresía eliminada correctamente.');

    window.location='membresias.php';

    </script>";
}
else
{
    echo "<script>

    alert('Error al eliminar la membresía.');

    window.location='membresias.php';

    </script>";
}

?>