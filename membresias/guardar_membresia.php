<?php

require_once("../config/conexion.php");

// =========================
// RECIBIR DATOS DEL FORMULARIO
// =========================

$id_cliente   = $_POST['id_cliente'];
$tipo         = $_POST['tipo'];
$fecha_inicio = $_POST['fecha_inicio'];

// =========================
// VALIDACIONES
// =========================

if(empty($id_cliente) || empty($tipo) || empty($fecha_inicio))
{
    echo "<script>
    alert('Todos los campos son obligatorios.');
    window.location='membresias.php';
    </script>";
    exit();
}

// =========================
// CALCULAR FECHA FIN
// =========================

switch($tipo)
{
    case "Mensual":
        $fecha_fin = date("Y-m-d", strtotime($fecha_inicio . " +30 days"));
        break;

    case "Trimestral":
        $fecha_fin = date("Y-m-d", strtotime($fecha_inicio . " +90 days"));
        break;

    case "Semestral":
        $fecha_fin = date("Y-m-d", strtotime($fecha_inicio . " +180 days"));
        break;

    case "Anual":
        $fecha_fin = date("Y-m-d", strtotime($fecha_inicio . " +365 days"));
        break;

    default:
        echo "<script>
        alert('Tipo de membresía incorrecto.');
        window.location='membresias.php';
        </script>";
        exit();
}

// =========================
// ESTADO
// =========================

$hoy = date("Y-m-d");

if($fecha_fin >= $hoy)
{
    $estado = "Activa";
}
else
{
    $estado = "Vencida";
}

// =========================
// VALIDAR QUE EL CLIENTE
// NO TENGA OTRA MEMBRESÍA ACTIVA
// =========================

$sqlValidar = "SELECT *
FROM membresias
WHERE id_cliente='$id_cliente'
AND estado='Activa'";

$resultado = mysqli_query($conexion,$sqlValidar);

if(mysqli_num_rows($resultado)>0)
{
    echo "<script>
    alert('Este cliente ya posee una membresía activa.');
    window.location='membresias.php';
    </script>";
    exit();
}

// =========================
// INSERTAR
// =========================

$sql = "INSERT INTO membresias
(
id_cliente,
tipo,
fecha_inicio,
fecha_fin,
estado
)

VALUES
(
'$id_cliente',
'$tipo',
'$fecha_inicio',
'$fecha_fin',
'$estado'
)";

if(mysqli_query($conexion,$sql))
{
    echo "<script>
    alert('Membresía registrada correctamente.');
    window.location='membresias.php';
    </script>";
}
else
{
    echo "<script>
    alert('Error al registrar la membresía.');
    window.location='membresias.php';
    </script>";
}

?>