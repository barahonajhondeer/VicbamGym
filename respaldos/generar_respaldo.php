<?php

require_once("../config/verificar_sesion.php");

/* =========================================
   SOLO ADMINISTRADOR
========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {

    header(
        "Location: ../dashboard.php"
    );

    exit();
}


/* =========================================
   CONFIGURACIÓN
========================================= */

/*
   En XAMPP normalmente mysqldump está aquí.
*/

$rutaMysqldump =
    "C:\\xampp\\mysql\\bin\\mysqldump.exe";


$servidor =
    "127.0.0.1";

$usuarioBD =
    "root";

$passwordBD =
    "";

$nombreBD =
    "vicbamgym";


/* =========================================
   NOMBRE DEL ARCHIVO
========================================= */

$fecha =
    date("Y-m-d_H-i-s");


$nombreArchivo =
    "vicbamgym_backup_" .
    $fecha .
    ".sql";


/* =========================================
   CARPETA TEMPORAL
========================================= */

$carpeta =
    __DIR__ . "/archivos";


if (
    !is_dir(
        $carpeta
    )
) {

    mkdir(
        $carpeta,
        0755,
        true
    );
}


$rutaArchivo =
    $carpeta .
    "/" .
    $nombreArchivo;


/* =========================================
   CREAR COMANDO
========================================= */

$comando =
    '"' .
    $rutaMysqldump .
    '"' .

    " --host=" .
    escapeshellarg(
        $servidor
    ) .

    " --user=" .
    escapeshellarg(
        $usuarioBD
    );


/* =========================================
   CONTRASEÑA
========================================= */

if (
    $passwordBD !== ""
) {

    $comando .=
        " --password=" .
        escapeshellarg(
            $passwordBD
        );
}


/* =========================================
   BASE DE DATOS
========================================= */

$comando .=
    " --single-transaction" .
    " --routines" .
    " --triggers" .
    " " .
    escapeshellarg(
        $nombreBD
    ) .

    " > " .
    escapeshellarg(
        $rutaArchivo
    ) .

    " 2>&1";


/* =========================================
   EJECUTAR
========================================= */

exec(
    $comando,
    $salida,
    $codigoSalida
);


/* =========================================
   VALIDAR
========================================= */

if (
    $codigoSalida !== 0 ||
    !file_exists(
        $rutaArchivo
    ) ||
    filesize(
        $rutaArchivo
    ) === 0
) {

    if (
        file_exists(
            $rutaArchivo
        )
    ) {

        unlink(
            $rutaArchivo
        );
    }


    die(
        "No se pudo generar el respaldo. " .
        "Verifique la configuración de mysqldump."
    );
}


/* =========================================
   DESCARGAR
========================================= */

header(
    "Content-Type: application/sql"
);

header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo .
    '"'
);

header(
    "Content-Length: " .
    filesize(
        $rutaArchivo
    )
);


readfile(
    $rutaArchivo
);


/* =========================================
   BORRAR COPIA TEMPORAL
========================================= */

unlink(
    $rutaArchivo
);

exit();

?>