<?php

/* =========================================
   CONFIGURACIÓN DE ERRORES
========================================= */

/*
|--------------------------------------------------------------------------
| PRODUCCIÓN
|--------------------------------------------------------------------------
|
| No mostrar errores técnicos al usuario.
| Sí guardarlos en un archivo de registro.
|
*/

ini_set(
    "display_errors",
    "0"
);

ini_set(
    "display_startup_errors",
    "0"
);

error_reporting(
    E_ALL
);


/* =========================================
   ACTIVAR LOG DE ERRORES
========================================= */

ini_set(
    "log_errors",
    "1"
);


/* =========================================
   ARCHIVO DE LOG
========================================= */

$rutaLogs =
    dirname(__DIR__) .
    "/logs";


/* Crear carpeta si no existe */

if (
    !is_dir(
        $rutaLogs
    )
) {

    mkdir(
        $rutaLogs,
        0755,
        true
    );
}


/* Archivo donde se guardarán */

ini_set(
    "error_log",
    $rutaLogs .
    "/php_errors.log"
);

?>