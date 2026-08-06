<?php

require_once("../config/conexion.php");

// =============================
// RECIBIR DATOS
// =============================

$id_pago       = $_POST['id_pago'];
$id_cliente    = $_POST['id_cliente'];
$id_membresia  = $_POST['id_membresia'];
$valor         = $_POST['valor'];
$metodo_pago   = $_POST['metodo_pago'];
$fecha_pago    = $_POST['fecha_pago'];

// =============================
// VALIDACIONES
// =============================

if(
    empty($id_cliente) ||
    empty($id_membresia) ||
    empty($valor) ||
    empty($metodo_pago) ||
    empty($fecha_pago)
){

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode("Complete todos los campos obligatorios.")
    );
    
    exit();

}

if($valor<=0){

    echo "<script>

    alert('El valor debe ser mayor a cero.');

    window.location='pagos.php';

    </script>";

    exit();

}

// =============================
// ACTUALIZAR
// =============================

$sql="UPDATE pagos

SET

id_cliente='$id_cliente',

id_membresia='$id_membresia',

valor='$valor',

metodo_pago='$metodo_pago',

fecha_pago='$fecha_pago'

WHERE id_pago='$id_pago'";

if(mysqli_query($conexion,$sql)){

    header(
        "Location: pagos.php?tipo=exito&mensaje=" .
        urlencode("Pago actualizado correctamente.")
    );
    
    exit();

}else{

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode("No se pudo actualizar el pago.")
    );
    
    exit();

}

?>