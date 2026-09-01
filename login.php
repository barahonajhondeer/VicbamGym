<?php

session_start();

require_once("config/conexion.php");
require_once("config/csrf.php");


/* =========================================
   EVITAR CACHÉ DEL LOGIN
========================================= */

header(
    "Cache-Control: no-store, no-cache, must-revalidate, max-age=0"
);

header(
    "Cache-Control: post-check=0, pre-check=0",
    false
);

header("Pragma: no-cache");
header("Expires: 0");


/* =========================================
   SI YA HAY SESIÓN
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
   MENSAJE
========================================= */

$error = "";


/* =========================================
   PROCESAR LOGIN
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =====================================
       VALIDAR CSRF
    ====================================== */

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

        $error =
            "La solicitud no es válida.";

    } else {


        /* =================================
           RECIBIR DATOS
        ================================== */

        $usuario =
            trim(
                $_POST["usuario"] ?? ""
            );


        $password =
            $_POST["password"] ?? "";


        /* =================================
           VALIDAR CAMPOS
        ================================== */

        if (
            $usuario === "" ||
            $password === ""
        ) {

            $error =
                "Ingrese usuario y contraseña.";

        } elseif (
            mb_strlen($usuario) > 50 ||
            strlen($password) > 100
        ) {

            $error =
                "Usuario o contraseña incorrectos.";

        } else {


            /* =================================
               BUSCAR USUARIO
            ================================== */

            $sql = "
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


            $stmt =
                mysqli_prepare(
                    $conexion,
                    $sql
                );


            if (!$stmt) {

                error_log(
                    "Error preparando login: " .
                    mysqli_error($conexion)
                );

                $error =
                    "Ocurrió un error al procesar el inicio de sesión.";

            } else {


                mysqli_stmt_bind_param(
                    $stmt,
                    "s",
                    $usuario
                );


                /* =================================
                   EJECUTAR CONSULTA
                ================================== */

                if (
                    !mysqli_stmt_execute(
                        $stmt
                    )
                ) {

                    error_log(
                        "Error ejecutando login: " .
                        mysqli_stmt_error($stmt)
                    );

                    $error =
                        "Ocurrió un error al procesar el inicio de sesión.";

                } else {


                    $resultado =
                        mysqli_stmt_get_result(
                            $stmt
                        );


                    $fila =
                        mysqli_fetch_assoc(
                            $resultado
                        );


                    /* =================================
                       VALIDAR CREDENCIALES
                    ================================== */

                    if (
                        !$fila ||
                        !password_verify(
                            $password,
                            $fila["password"] ?? ""
                        )
                    ) {

                        $error =
                            "Usuario o contraseña incorrectos.";

                    } elseif (
                        $fila["estado"] !== "Activo"
                    ) {

                        $error =
                            "Este usuario se encuentra inactivo.";

                    } else {


                        /* =================================
                           ACTUALIZAR HASH SI ES NECESARIO
                        ================================== */

                        if (
                            password_needs_rehash(
                                $fila["password"],
                                PASSWORD_DEFAULT
                            )
                        ) {

                            $nuevoHash =
                                password_hash(
                                    $password,
                                    PASSWORD_DEFAULT
                                );


                            if ($nuevoHash !== false) {

                                $sqlHash = "
                                    UPDATE usuarios
                                    SET password = ?
                                    WHERE id_usuario = ?
                                ";


                                $stmtHash =
                                    mysqli_prepare(
                                        $conexion,
                                        $sqlHash
                                    );


                                if ($stmtHash) {

                                    $idActualizar =
                                        (int)
                                        $fila["id_usuario"];


                                    mysqli_stmt_bind_param(
                                        $stmtHash,
                                        "si",
                                        $nuevoHash,
                                        $idActualizar
                                    );


                                    if (
                                        !mysqli_stmt_execute(
                                            $stmtHash
                                        )
                                    ) {

                                        error_log(
                                            "No se pudo actualizar el hash del usuario ID " .
                                            $idActualizar
                                        );
                                    }


                                    mysqli_stmt_close(
                                        $stmtHash
                                    );
                                }
                            }
                        }


                        /* =================================
                           LOGIN CORRECTO
                        ================================== */

                        session_regenerate_id(
                            true
                        );


                        $_SESSION["id_usuario"] =
                            (int)
                            $fila["id_usuario"];


                        $_SESSION["nombre"] =
                            $fila["nombre"] ?? "";


                        $_SESSION["usuario"] =
                            $fila["usuario"];


                        $_SESSION["rol"] =
                            $fila["rol"];


                        $_SESSION["ultima_actividad"] =
                            time();


                        $_SESSION["ultima_regeneracion"] =
                            time();


                        /* =================================
                           RENOVAR TOKEN CSRF
                        ================================== */

                        $_SESSION["csrf_token"] =
                            bin2hex(
                                random_bytes(32)
                            );


                        /* =================================
                           REDIRECCIÓN
                        ================================== */

                        mysqli_stmt_close(
                            $stmt
                        );


                        header(
                            "Location: /VicbamGym/dashboard.php"
                        );

                        exit();
                    }
                }


                mysqli_stmt_close(
                    $stmt
                );
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


        <h2>
            Iniciar sesión
        </h2>


        <?php

        if ($error !== "") {

        ?>

            <div class="mensaje-error">

                <?php

                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                );

                ?>

            </div>

        <?php

        }

        ?>


        <form
            action=""
            method="POST"
            autocomplete="off"
        >


            <?php
            echo csrf_field();
            ?>


            <!-- USUARIO -->

            <div class="form-group">

                <label for="usuario">
                    Usuario
                </label>


                <input
                    type="text"
                    name="usuario"
                    id="usuario"

                    minlength="4"
                    maxlength="50"

                    autocomplete="username"
                    required
                    autofocus

                    value="<?php

                        echo isset(
                            $_POST["usuario"]
                        )
                            ? htmlspecialchars(
                                $_POST["usuario"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                            : "";

                    ?>"
                >

            </div>


            <!-- CONTRASEÑA -->

            <div class="form-group">

                <label for="password">
                    Contraseña
                </label>


                <input
                    type="password"
                    name="password"
                    id="password"

                    maxlength="100"

                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- BOTÓN -->

            <button
                type="submit"
                class="btn-guardar"
            >

                Ingresar

            </button>


        </form>


    </div>


</div>


</body>

</html>