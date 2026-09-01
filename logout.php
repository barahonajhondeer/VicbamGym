<?php

session_start();

/* Eliminar variables */
$_SESSION = [];


/* Eliminar cookie de sesión */
if (
    ini_get("session.use_cookies")
) {

    $parametros =
        session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}


/* Destruir sesión */
session_destroy();


/* Volver al login */
header(
    "Location: /VicbamGym/login.php"
);

exit();

