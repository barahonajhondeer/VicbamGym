<?php

require_once("../config/conexion.php");

if(isset($_POST['id_cliente']))
{

    $id_cliente = $_POST['id_cliente'];

    $sql = "SELECT *

            FROM membresias

            WHERE id_cliente='$id_cliente'

            AND estado='Activa'";

    $resultado = mysqli_query($conexion,$sql);

    if(mysqli_num_rows($resultado)>0)
    {

        $fila = mysqli_fetch_assoc($resultado);

        echo json_encode($fila);

    }
    else
    {

        echo json_encode(null);

    }

}

?>