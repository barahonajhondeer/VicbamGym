<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   GENERAR TOKEN CSRF
========================================= */

if (
    empty($_SESSION["csrf_token"])
) {

    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );
}


/* =========================================
   DEVOLVER TOKEN
========================================= */

function csrf_token()
{
    return $_SESSION["csrf_token"];
}


/* =========================================
   CAMPO HTML
========================================= */

function csrf_field()
{
    return
        '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(
            $_SESSION["csrf_token"],
            ENT_QUOTES,
            "UTF-8"
        ) .
        '">';
}


/* =========================================
   VALIDAR TOKEN
========================================= */

function verificar_csrf()
{

    $tokenRecibido =
        $_POST["csrf_token"] ?? "";

    $tokenSesion =
        $_SESSION["csrf_token"] ?? "";


    if (
        $tokenRecibido === "" ||
        $tokenSesion === "" ||
        !hash_equals(
            $tokenSesion,
            $tokenRecibido
        )
    ) {

        http_response_code(403);

        die(
            "Solicitud no válida. Token de seguridad incorrecto."
        );
    }
}