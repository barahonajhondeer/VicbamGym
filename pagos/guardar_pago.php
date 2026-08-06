<?php

require_once("../config/conexion.php");

$id_cliente    = intval($_POST["id_cliente"]);
$id_membresia  = intval($_POST["id_membresia"]);
$valorAbono    = floatval($_POST["valor"]);
$metodo_pago   = trim($_POST["metodo_pago"]);
$fecha_pago    = $_POST["fecha_pago"];

/* ==========================
   VALIDACIONES
========================== */

if (
    $id_cliente <= 0 ||
    $id_membresia <= 0 ||
    $valorAbono <= 0 ||
    empty($metodo_pago) ||
    empty($fecha_pago)
) {

    header(
        "Location: pagos.php?tipo=advertencia&mensaje=" .
        urlencode("Complete todos los campos obligatorios.")
    );
    
    exit();
}

/* ==========================
   OBTENER VALOR DE MEMBRESÍA
========================== */

$sql = "SELECT valor
        FROM membresias
        WHERE id_membresia=?";

$stmt = mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$id_membresia
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$membresia = mysqli_fetch_assoc($resultado);

if(!$membresia){

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode("La membresía seleccionada no existe.")
    );
    
    exit();
}

$valorTotal = floatval($membresia["valor"]);

/* ==========================
   TOTAL ABONADO
========================== */

$sql = "SELECT
            IFNULL(SUM(valor),0) total
        FROM pagos
        WHERE id_membresia=?";

$stmt = mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$id_membresia
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

$total = mysqli_fetch_assoc($resultado);

$totalAbonado = floatval($total["total"]);

$saldo = $valorTotal - $totalAbonado;

/* ==========================
   VALIDAR ABONO
========================== */

if($valorAbono > $saldo){

    header(
        "Location: pagos.php?tipo=info&mensaje=" .
        urlencode("Esta membresía ya se encuentra pagada completamente.")
    );
    
    exit();

}

/* ==========================
   INSERTAR PAGO
========================== */

$sql = "INSERT INTO pagos
(
id_cliente,
id_membresia,
valor,
metodo_pago,
fecha_pago
)

VALUES
(
?,
?,
?,
?,
?
)";

$stmt = mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param(

$stmt,

"iidss",

$id_cliente,

$id_membresia,

$valorAbono,

$metodo_pago,

$fecha_pago

);

if (mysqli_stmt_execute($stmt)) {

    $nuevoSaldo = $saldo - $valorAbono;

    if ($nuevoSaldo <= 0) {

        header(
            "Location: pagos.php?tipo=exito&mensaje=" .
            urlencode(
                "Pago completado. La membresía quedó totalmente pagada."
            )
        );

        exit();

    } else {

        header(
            "Location: pagos.php?tipo=exito&mensaje=" .
            urlencode(
                "Abono registrado correctamente. Saldo pendiente: $" .
                number_format($nuevoSaldo, 2) .
                "."
            )
        );

        exit();
    }

} else {

    header(
        "Location: pagos.php?tipo=error&mensaje=" .
        urlencode("No se pudo registrar el abono.")
    );

    exit();
}

?>