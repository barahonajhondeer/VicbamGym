<?php

require_once("../config/conexion.php");

$cedula = $_POST['cedula'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$telefono = $_POST['telefono'];
$correo = $_POST['correo'];
$direccion = $_POST['direccion'];

/* ==========================
   VALIDAR CÉDULA
========================== */

if(!preg_match('/^[0-9]{10}$/', $cedula)){

    echo "<script>
    alert('La cédula debe contener exactamente 10 dígitos numéricos.');
    window.location='clientes.php';
    </script>";

    exit();

}

/* ==========================
   VALIDAR CORREO
========================== */

if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){

    echo "<script>
    alert('Ingrese un correo electrónico válido.');
    window.location='clientes.php';
    </script>";

    exit();

}

/* ==========================
   VALIDAR CÉDULA REPETIDA
========================== */

$sql = "SELECT * FROM clientes WHERE cedula='$cedula'";

$resultado = mysqli_query($conexion, $sql);

if(mysqli_num_rows($resultado) > 0){

    echo "<script>
    alert('Ya existe un cliente registrado con esa cédula.');
    window.location='clientes.php';
    </script>";

    exit();

}

/* ==========================
   VALIDAR CORREO REPETIDO
========================== */

$sql = "SELECT * FROM clientes WHERE correo='$correo'";

$resultado = mysqli_query($conexion, $sql);

if(mysqli_num_rows($resultado) > 0){

    echo "<script>
    alert('El correo electrónico ya se encuentra registrado.');
    window.location='clientes.php';
    </script>";

    exit();

}

/* ==========================
   INSERTAR CLIENTE
========================== */

$sql = "INSERT INTO clientes
(
cedula,
nombres,
apellidos,
telefono,
correo,
direccion,
fecha_registro
)

VALUES
(
'$cedula',
'$nombres',
'$apellidos',
'$telefono',
'$correo',
'$direccion',
CURDATE()
)";

if(mysqli_query($conexion, $sql)){

    echo "<script>
    alert('Cliente registrado correctamente.');
    window.location='clientes.php';
    </script>";

}else{

    echo "<script>
    alert('Ocurrió un error al registrar el cliente.');
    window.location='clientes.php';
    </script>";

}

?>