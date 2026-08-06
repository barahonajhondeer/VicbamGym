<?php

header("Content-Type: application/json; charset=utf-8");

require_once("../config/conexion.php");

if (!isset($_POST["id_cliente"])) {
    echo json_encode(null);
    exit();
}

$id_cliente = intval($_POST["id_cliente"]);

$sql = "SELECT
            m.id_membresia,
            m.tipo,
            m.valor,
            m.fecha_inicio,
            m.fecha_fin,
            m.fecha_limite_pago,
            m.estado,

            IFNULL(SUM(p.valor),0) AS total_abonado

        FROM membresias m

        LEFT JOIN pagos p
            ON m.id_membresia=p.id_membresia

        WHERE m.id_cliente=?

        AND m.estado='Activa'

        GROUP BY
            m.id_membresia,
            m.tipo,
            m.valor,
            m.fecha_inicio,
            m.fecha_fin,
            m.fecha_limite_pago,
            m.estado

        ORDER BY m.id_membresia DESC

        LIMIT 1";

$stmt=mysqli_prepare($conexion,$sql);

mysqli_stmt_bind_param(
$stmt,
"i",
$id_cliente
);

mysqli_stmt_execute($stmt);

$resultado=mysqli_stmt_get_result($stmt);

if($fila=mysqli_fetch_assoc($resultado))
{

$total=$fila["valor"];

$abonado=$fila["total_abonado"];

$saldo=$total-$abonado;

if($saldo<0)
{
    $saldo=0;
}

$fila["saldo_pendiente"]=$saldo;

echo json_encode($fila);

}
else
{

echo json_encode(null);

}

?>