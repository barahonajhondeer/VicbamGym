<?php

$servidorBD = getenv("DB_HOST") ?: "localhost";
$usuarioBD = getenv("DB_USER") ?: "vicbamgym_app";
$passwordBD = getenv("DB_PASS") ?: "VicbamGym#2026_Seguro!";
$nombreBD = getenv("DB_NAME") ?: "vicbamgym";


$conexion = mysqli_connect(
    $servidorBD,
    $usuarioBD,
    $passwordBD,
    $nombreBD
);


if (!$conexion) {

    error_log(
        "Error de conexión a la base de datos: " .
        mysqli_connect_error()
    );

    http_response_code(500);

    die(
        "No se pudo conectar con la base de datos."
    );
}


mysqli_set_charset(
    $conexion,
    "utf8mb4"
);

?>