<?php

require_once("../config/conexion.php");

// =============================
// RECIBIR DATOS
// =============================

$id_membresia = $_POST['id_membresia'];
$id_cliente   = $_POST['id_cliente'];
$tipo         = $_POST['tipo'];
$fecha_inicio = $_POST['fecha_inicio'];

// =============================
// VALIDAR CAMPOS
// =============================

if(
    empty($id_cliente) ||
    empty($tipo) ||
    empty($fecha_inicio)
){

    echo "<script>

    alert('Todos los campos son obligatorios.');

    window.location='membresias.php';

    </script>";

    exit();

}

// =============================
// CALCULAR FECHA FIN
// =============================

switch($tipo){

    case "Mensual":

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio."+30 days")
        );

    break;

    case "Trimestral":

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio."+90 days")
        );

    break;

    case "Semestral":

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio."+180 days")
        );

    break;

    case "Anual":

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio."+365 days")
        );

    break;

}

// =============================
// CALCULAR ESTADO
// =============================

$hoy = date("Y-m-d");

if($fecha_fin >= $hoy){

    $estado="Activa";

}else{

    $estado="Vencida";

}

// =============================
// ACTUALIZAR
// =============================

$sql = "UPDATE membresias

SET

id_cliente='$id_cliente',

tipo='$tipo',

fecha_inicio='$fecha_inicio',

fecha_fin='$fecha_fin',

estado='$estado'

WHERE id_membresia='$id_membresia'";

if(mysqli_query($conexion,$sql)){

    echo "<script>

    alert('Membresía actualizada correctamente.');

    window.location='membresias.php';

    </script>";

}else{

    echo "<script>

    alert('Error al actualizar la membresía.');

    window.location='membresias.php';

    </script>";

}

?>