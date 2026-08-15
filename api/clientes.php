<?php

header("Content-Type: application/json; charset=UTF-8");

require_once("../config/conexion.php");

$sql = "SELECT
            id_cliente,
            cedula,
            nombres,
            apellidos,
            telefono,
            correo,
            direccion,
            fecha_registro
        FROM clientes
        ORDER BY nombres ASC";

$resultado = mysqli_query($conexion, $sql);

$clientes = [];

if ($resultado) {

    while ($fila = mysqli_fetch_assoc($resultado)) {

        $clientes[] = [
            "id_cliente" => (int) $fila["id_cliente"],
            "cedula" => $fila["cedula"],
            "nombres" => $fila["nombres"],
            "apellidos" => $fila["apellidos"],
            "telefono" => $fila["telefono"],
            "correo" => $fila["correo"],
            "direccion" => $fila["direccion"],
            "fecha_registro" => $fila["fecha_registro"]
        ];
    }

    http_response_code(200);

    echo json_encode(
        $clientes,
        JSON_UNESCAPED_UNICODE
    );

} else {

    http_response_code(500);

    echo json_encode([
        "error" => true,
        "mensaje" => "No se pudieron obtener los clientes."
    ]);
}

?>