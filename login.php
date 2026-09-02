<?php

/* =========================================
   CONFIGURACIÓN
========================================= */

require_once("config/errores.php");

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => false, // Cambiar a true cuando exista HTTPS
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

require_once("config/conexion.php");
require_once("config/csrf.php");


/* =========================================
   EVITAR CACHÉ
========================================= */

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header(
    "Cache-Control: post-check=0, pre-check=0",
    false
);

header(
    "Pragma: no-cache"
);

header(
    "Expires: 0"
);


/* =========================================
   SI YA EXISTE SESIÓN
========================================= */

if (
    isset($_SESSION["id_usuario"]) &&
    isset($_SESSION["usuario"]) &&
    isset($_SESSION["rol"])
) {

    header(
        "Location: /VicbamGym/dashboard.php"
    );

    exit();
}


/* =========================================
   VARIABLES
========================================= */

$error = "";

$usuarioIngresado = "";


/* =========================================
   IP DEL USUARIO
========================================= */

/*
|--------------------------------------------------------------------------
| Utilizamos REMOTE_ADDR.
|--------------------------------------------------------------------------
|
| No usamos directamente X-Forwarded-For porque puede ser manipulado
| si el servidor no está configurado detrás de un proxy de confianza.
|
*/

$ipUsuario =
    $_SERVER["REMOTE_ADDR"]
    ?? "desconocida";


/* =========================================
   PROCESAR LOGIN
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    /* =====================================
       VALIDAR CSRF
    ===================================== */

    $tokenRecibido =
        $_POST["csrf_token"]
        ?? "";

    $tokenSesion =
        $_SESSION["csrf_token"]
        ?? "";


    if (
        $tokenRecibido === "" ||
        $tokenSesion === "" ||
        !hash_equals(
            $tokenSesion,
            $tokenRecibido
        )
    ) {

        $error =
            "La solicitud no es válida. "
            . "Actualice la página e intente nuevamente.";

    } else {

        /* =================================
           RECIBIR DATOS
        ================================= */

        $usuarioIngresado =
            trim(
                $_POST["usuario"]
                ?? ""
            );

        $password =
            $_POST["password"]
            ?? "";


        /* =================================
           VALIDACIONES
        ================================= */

        if (
            $usuarioIngresado === "" ||
            $password === ""
        ) {

            $error =
                "Ingrese su usuario y contraseña.";

        } elseif (
            mb_strlen(
                $usuarioIngresado
            ) > 50
        ) {

            $error =
                "Credenciales incorrectas.";

        } elseif (
            strlen(
                $password
            ) > 100
        ) {

            $error =
                "Credenciales incorrectas.";

        } else {

            /* =================================
               COMPROBAR INTENTOS FALLIDOS
            ================================= */

            $sqlIntentos = "
                SELECT
                    COUNT(*) AS total
                FROM intentos_login
                WHERE
                    usuario = ?
                    AND ip = ?
                    AND exitoso = 0
                    AND fecha_intento >=
                        DATE_SUB(
                            NOW(),
                            INTERVAL 15 MINUTE
                        )
            ";


            $stmtIntentos =
                mysqli_prepare(
                    $conexion,
                    $sqlIntentos
                );


            if (!$stmtIntentos) {

                error_log(
                    "Error preparando control de intentos: "
                    . mysqli_error($conexion)
                );

                $error =
                    "No se pudo procesar el inicio de sesión.";

            } else {

                mysqli_stmt_bind_param(
                    $stmtIntentos,
                    "ss",
                    $usuarioIngresado,
                    $ipUsuario
                );


                if (
                    !mysqli_stmt_execute(
                        $stmtIntentos
                    )
                ) {

                    error_log(
                        "Error ejecutando control de intentos: "
                        . mysqli_stmt_error(
                            $stmtIntentos
                        )
                    );

                    $error =
                        "No se pudo procesar el inicio de sesión.";

                    mysqli_stmt_close(
                        $stmtIntentos
                    );

                } else {

                    $resultadoIntentos =
                        mysqli_stmt_get_result(
                            $stmtIntentos
                        );


                    $datosIntentos =
                        mysqli_fetch_assoc(
                            $resultadoIntentos
                        );


                    $totalIntentos =
                        (int)
                        (
                            $datosIntentos["total"]
                            ?? 0
                        );


                    mysqli_stmt_close(
                        $stmtIntentos
                    );


                    /* =============================
                       USUARIO BLOQUEADO
                    ============================= */

                    if (
                        $totalIntentos >= 5
                    ) {

                        $error =
                            "Demasiados intentos fallidos. "
                            . "Intente nuevamente en 15 minutos.";

                    } else {

                        /* =============================
                           CONSULTAR USUARIO
                        ============================= */

                        $sqlUsuario = "
                            SELECT
                                id_usuario,
                                nombre,
                                usuario,
                                password,
                                rol,
                                estado
                            FROM usuarios
                            WHERE usuario = ?
                            LIMIT 1
                        ";


                        $stmtUsuario =
                            mysqli_prepare(
                                $conexion,
                                $sqlUsuario
                            );


                        if (!$stmtUsuario) {

                            error_log(
                                "Error preparando login: "
                                . mysqli_error(
                                    $conexion
                                )
                            );

                            $error =
                                "No se pudo procesar el inicio de sesión.";

                        } else {

                            mysqli_stmt_bind_param(
                                $stmtUsuario,
                                "s",
                                $usuarioIngresado
                            );


                            if (
                                !mysqli_stmt_execute(
                                    $stmtUsuario
                                )
                            ) {

                                error_log(
                                    "Error ejecutando login: "
                                    . mysqli_stmt_error(
                                        $stmtUsuario
                                    )
                                );

                                $error =
                                    "No se pudo procesar el inicio de sesión.";

                                mysqli_stmt_close(
                                    $stmtUsuario
                                );

                            } else {

                                $resultadoUsuario =
                                    mysqli_stmt_get_result(
                                        $stmtUsuario
                                    );


                                $usuarioBD =
                                    mysqli_fetch_assoc(
                                        $resultadoUsuario
                                    );


                                mysqli_stmt_close(
                                    $stmtUsuario
                                );


                                /* =============================
                                   VALIDAR CREDENCIALES
                                ============================= */

                                $loginCorrecto =
                                    false;


                                if (
                                    $usuarioBD &&
                                    password_verify(
                                        $password,
                                        $usuarioBD["password"]
                                    )
                                ) {

                                    $loginCorrecto =
                                        true;
                                }


                                /* =============================
                                   LOGIN CORRECTO
                                ============================= */

                                if (
                                    $loginCorrecto &&
                                    $usuarioBD["estado"] === "Activo"
                                ) {

                                    /* =========================
                                       BORRAR INTENTOS FALLIDOS
                                    ========================= */

                                    $sqlLimpiar = "
                                        DELETE FROM intentos_login
                                        WHERE
                                            usuario = ?
                                            AND ip = ?
                                    ";


                                    $stmtLimpiar =
                                        mysqli_prepare(
                                            $conexion,
                                            $sqlLimpiar
                                        );


                                    if ($stmtLimpiar) {

                                        mysqli_stmt_bind_param(
                                            $stmtLimpiar,
                                            "ss",
                                            $usuarioIngresado,
                                            $ipUsuario
                                        );


                                        if (
                                            !mysqli_stmt_execute(
                                                $stmtLimpiar
                                            )
                                        ) {

                                            error_log(
                                                "No se pudieron limpiar "
                                                . "los intentos del login: "
                                                . mysqli_stmt_error(
                                                    $stmtLimpiar
                                                )
                                            );
                                        }


                                        mysqli_stmt_close(
                                            $stmtLimpiar
                                        );
                                        $sqlLimpiarAntiguos = "
                                            DELETE FROM intentos_login
                                                WHERE fecha_intento < DATE_SUB(
                                                    NOW(),
                                                    INTERVAL 1 DAY
                                                )
                                            ";

                                            if (
                                                !mysqli_query(
                                                    $conexion,
                                                    $sqlLimpiarAntiguos
                                                )
                                            ) {

                                                error_log(
                                                    "No se pudieron limpiar intentos antiguos: " .
                                                    mysqli_error($conexion)
                                                );
                                            }
                                    }


                                    /* =========================
                                       REGENERAR SESIÓN
                                    ========================= */

                                    session_regenerate_id(
                                        true
                                    );


                                    /* =========================
                                       CREAR SESIÓN
                                    ========================= */

                                    $_SESSION["id_usuario"] =
                                        (int)
                                        $usuarioBD["id_usuario"];


                                    $_SESSION["nombre"] =
                                        $usuarioBD["nombre"];


                                    $_SESSION["usuario"] =
                                        $usuarioBD["usuario"];


                                    $_SESSION["rol"] =
                                        $usuarioBD["rol"];


                                    $_SESSION["ultima_actividad"] =
                                        time();


                                    $_SESSION["ultima_regeneracion"] =
                                        time();


                                    /* =========================
                                       ACTUALIZAR HASH SI HACE FALTA
                                    ========================= */

                                    if (
                                        password_needs_rehash(
                                            $usuarioBD["password"],
                                            PASSWORD_DEFAULT
                                        )
                                    ) {

                                        $nuevoHash =
                                            password_hash(
                                                $password,
                                                PASSWORD_DEFAULT
                                            );


                                        $sqlRehash = "
                                            UPDATE usuarios
                                            SET password = ?
                                            WHERE id_usuario = ?
                                        ";


                                        $stmtRehash =
                                            mysqli_prepare(
                                                $conexion,
                                                $sqlRehash
                                            );


                                        if ($stmtRehash) {

                                            mysqli_stmt_bind_param(
                                                $stmtRehash,
                                                "si",
                                                $nuevoHash,
                                                $usuarioBD[
                                                    "id_usuario"
                                                ]
                                            );


                                            if (
                                                !mysqli_stmt_execute(
                                                    $stmtRehash
                                                )
                                            ) {

                                                error_log(
                                                    "No se pudo actualizar "
                                                    . "el hash del usuario: "
                                                    . mysqli_stmt_error(
                                                        $stmtRehash
                                                    )
                                                );
                                            }


                                            mysqli_stmt_close(
                                                $stmtRehash
                                            );
                                        }
                                    }


                                    /* =========================
                                       NUEVO TOKEN CSRF
                                    ========================= */

                                    $_SESSION["csrf_token"] =
                                        bin2hex(
                                            random_bytes(32)
                                        );


                                    /* =========================
                                       REDIRECCIONAR
                                    ========================= */

                                    header(
                                        "Location: /VicbamGym/dashboard.php"
                                    );

                                    exit();

                                } else {

                                    /* =========================
                                       REGISTRAR INTENTO FALLIDO
                                    ========================= */

                                    $sqlRegistrarIntento = "
                                        INSERT INTO intentos_login
                                        (
                                            usuario,
                                            ip,
                                            exitoso
                                        )
                                        VALUES
                                        (
                                            ?,
                                            ?,
                                            0
                                        )
                                    ";


                                    $stmtRegistrar =
                                        mysqli_prepare(
                                            $conexion,
                                            $sqlRegistrarIntento
                                        );


                                    if ($stmtRegistrar) {

                                        mysqli_stmt_bind_param(
                                            $stmtRegistrar,
                                            "ss",
                                            $usuarioIngresado,
                                            $ipUsuario
                                        );


                                        if (
                                            !mysqli_stmt_execute(
                                                $stmtRegistrar
                                            )
                                        ) {

                                            error_log(
                                                "Error registrando intento "
                                                . "fallido de login: "
                                                . mysqli_stmt_error(
                                                    $stmtRegistrar
                                                )
                                            );
                                        }


                                        mysqli_stmt_close(
                                            $stmtRegistrar
                                        );
                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | MENSAJE GENÉRICO
                                    |--------------------------------------------------------------------------
                                    |
                                    | No indicamos si falló el usuario,
                                    | la contraseña o si la cuenta está inactiva.
                                    |
                                    */

                                    $error =
                                        "Usuario o contraseña incorrectos.";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Iniciar sesión | VICBAMGYM
    </title>

    <link
        rel="stylesheet"
        href="assets/css/styles.css"
    >

</head>

<body class="login-body">

    <div class="login-container">

        <div class="login-card">

            <h1>
                VICBAMGYM
            </h1>

            <p>
                Iniciar sesión
            </p>


            <?php if ($error !== "") { ?>

                <div class="mensaje-error">

                    <?php
                    echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            <?php } ?>


            <form
                method="POST"
                action="login.php"
                autocomplete="on"
            >

                <?php
                echo csrf_field();
                ?>


                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        value="<?php
                            echo htmlspecialchars(
                                $usuarioIngresado,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        minlength="4"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        maxlength="100"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn-login"
                >
                    Ingresar
                </button>

            </form>

        </div>

    </div>

</body>

</html>