
<?php
require_once(__DIR__ . "/errores.php");

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
   COMPROBAR QUE EL USUARIO SIGUE ACTIVO
========================================= */

if (!isset($conexion)) {

    require_once(__DIR__ . "/conexion.php");
}


$sqlUsuarioSesion = "
    SELECT
        usuario,
        rol,
        estado
    FROM usuarios
    WHERE id_usuario = ?
    LIMIT 1
";


$stmtUsuarioSesion =
    mysqli_prepare(
        $conexion,
        $sqlUsuarioSesion
    );


if (!$stmtUsuarioSesion) {

    error_log(
        "Error verificando usuario de sesión: " .
        mysqli_error($conexion)
    );

    session_unset();
    session_destroy();

    header(
        "Location: /VicbamGym/login.php?sesion=error"
    );

    exit();
}


$idUsuarioSesion =
    (int)
    $_SESSION["id_usuario"];


mysqli_stmt_bind_param(
    $stmtUsuarioSesion,
    "i",
    $idUsuarioSesion
);


if (
    !mysqli_stmt_execute(
        $stmtUsuarioSesion
    )
) {

    error_log(
        "Error ejecutando verificación de sesión: " .
        mysqli_stmt_error(
            $stmtUsuarioSesion
        )
    );

    mysqli_stmt_close(
        $stmtUsuarioSesion
    );

    session_unset();
    session_destroy();

    header(
        "Location: /VicbamGym/login.php?sesion=error"
    );

    exit();
}


$resultadoUsuarioSesion =
    mysqli_stmt_get_result(
        $stmtUsuarioSesion
    );


$usuarioSesion =
    mysqli_fetch_assoc(
        $resultadoUsuarioSesion
    );


mysqli_stmt_close(
    $stmtUsuarioSesion
);


/* =========================================
   SESIÓN YA NO VÁLIDA
========================================= */

if (
    !$usuarioSesion ||
    $usuarioSesion["estado"] !== "Activo"
) {

    session_unset();
    session_destroy();

    header(
        "Location: /VicbamGym/login.php?sesion=desactivada"
    );

    exit();
}


/* =========================================
   SINCRONIZAR DATOS DE SESIÓN
========================================= */

$_SESSION["usuario"] =
    $usuarioSesion["usuario"];


$_SESSION["rol"] =
    $usuarioSesion["rol"];


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