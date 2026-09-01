<?php

/* =========================================
   INICIAR SESIÓN DE FORMA SEGURA
========================================= */

if (session_status() === PHP_SESSION_NONE) {

    /* Solo podremos activar secure=true
       cuando el sistema tenga HTTPS.
    */

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => false,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}


/* =========================================
   VERIFICAR LOGIN
========================================= */

if (
    !isset($_SESSION["id_usuario"]) ||
    !isset($_SESSION["usuario"]) ||
    !isset($_SESSION["rol"])
) {

    session_unset();
    session_destroy();

    header(
        "Location: /VicbamGym/login.php?sesion=no"
    );

    exit();
}


/* =========================================
   TIEMPO MÁXIMO DE INACTIVIDAD
   30 MINUTOS
========================================= */

$tiempoMaximoInactividad = 30 * 60;


/* =========================================
   COMPROBAR INACTIVIDAD
========================================= */

if (isset($_SESSION["ultima_actividad"])) {

    $tiempoInactivo =
        time() -
        $_SESSION["ultima_actividad"];

    if (
        $tiempoInactivo >
        $tiempoMaximoInactividad
    ) {

        session_unset();
        session_destroy();

        header(
            "Location: /VicbamGym/login.php?sesion=expirada"
        );

        exit();
    }
}


/* =========================================
   ACTUALIZAR ACTIVIDAD
========================================= */

$_SESSION["ultima_actividad"] = time();


/* =========================================
   REGENERAR ID PERIÓDICAMENTE
========================================= */

$intervaloRegeneracion = 15 * 60;

if (
    !isset($_SESSION["ultima_regeneracion"])
) {

    $_SESSION["ultima_regeneracion"] =
        time();

} elseif (
    time() -
    $_SESSION["ultima_regeneracion"]
    >
    $intervaloRegeneracion
) {

    session_regenerate_id(true);

    $_SESSION["ultima_regeneracion"] =
        time();
}