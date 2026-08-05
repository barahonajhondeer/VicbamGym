    <?php

require_once("../config/conexion.php");

// ===============================
// RECIBIR DATOS
// ===============================

$id_cliente    = $_POST['id_cliente'];
$id_membresia  = $_POST['id_membresia'];
$valor         = $_POST['valor'];
$metodo_pago   = $_POST['metodo_pago'];
$fecha_pago    = $_POST['fecha_pago'];

// ===============================
// VALIDACIONES
// ===============================

if(
    empty($id_cliente) ||
    empty($id_membresia) ||
    empty($valor) ||
    empty($metodo_pago) ||
    empty($fecha_pago)
){

    echo "<script>

    alert('Todos los campos son obligatorios.');

    window.location='pagos.php';

    </script>";

    exit();

}

if($valor<=0){

    echo "<script>

    alert('El valor del pago debe ser mayor a cero.');

    window.location='pagos.php';

    </script>";

    exit();

}

// ===============================
// VERIFICAR QUE EL CLIENTE EXISTA
// ===============================

$sql="SELECT * FROM clientes
WHERE id_cliente='$id_cliente'";

$resultado=mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado)==0){

    echo "<script>

    alert('El cliente seleccionado no existe.');

    window.location='pagos.php';

    </script>";

    exit();

}

// ===============================
// VERIFICAR MEMBRESÍA
// ===============================

$sql="SELECT *
FROM membresias
WHERE id_membresia='$id_membresia'
AND estado='Activa'";

$resultado=mysqli_query($conexion,$sql);

if(mysqli_num_rows($resultado)==0){

    echo "<script>

    alert('La membresía no está activa.');

    window.location='pagos.php';

    </script>";

    exit();

}

// ===============================
// INSERTAR
// ===============================

$sql="INSERT INTO pagos(

id_cliente,

id_membresia,

valor,

metodo_pago,

fecha_pago

)

VALUES(

'$id_cliente',

'$id_membresia',

'$valor',

'$metodo_pago',

'$fecha_pago'

)";

if(mysqli_query($conexion,$sql)){

    echo "<script>

    alert('Pago registrado correctamente.');

    window.location='pagos.php';

    </script>";

}else{

    echo "<script>

    alert('Error al registrar el pago.');

    window.location='pagos.php';

    </script>";

}

?>