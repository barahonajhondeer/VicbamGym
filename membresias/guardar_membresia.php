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
    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode("Todos los campos son obligatorios.")
    );
    exit();
}

// =========================
// DEFINIR VALOR Y DURACIÓN
// =========================

switch ($tipo) {

    case "Mensual":

        $valor = 25.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio . " +1 month")
        );

        break;

    case "Trimestral":

        $valor = 65.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio . " +3 months")
        );

        break;

    case "Semestral":

        $valor = 120.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio . " +6 months")
        );

        break;

    case "Anual":

        $valor = 220.00;

        $fecha_fin = date(
            "Y-m-d",
            strtotime($fecha_inicio . " +1 year")
        );

        break;

    default:

        echo "<script>
            alert('Tipo de membresía incorrecto.');
            window.location='membresias.php';
        </script>";

        exit();
}
// =========================
// FECHA LÍMITE DE PAGO
// =========================

$fecha_limite_pago = $fecha_fin;

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
    header(
        "Location: membresias.php?tipo=advertencia&mensaje=" .
        urlencode("Este cliente ya posee una membresía activa.")
    );
    exit();
}

// =========================
// INSERTAR
// =========================

$sql = "INSERT INTO membresias
(
id_cliente,
tipo,
valor,
fecha_inicio,
fecha_fin,
fecha_limite_pago,
estado
)

VALUES
(
'$id_cliente',
'$tipo',
'$valor',
'$fecha_inicio',
'$fecha_fin',
'$fecha_limite_pago ',
'$estado'
)";

if(mysqli_query($conexion,$sql))
{
    header(
        "Location: membresias.php?tipo=exito&mensaje=" .
        urlencode("Membresía registrada correctamente.")
    );
    exit();
}
else
{
    header(
        "Location: membresias.php?tipo=error&mensaje=" .
        urlencode("No se pudo registrar la membresía.")
    );
    exit();
}

?>