<?php

session_start();

require_once("config/conexion.php");


/* =========================================
   SI YA HAY SESIÓN, IR AL DASHBOARD
========================================= */

if (
    isset($_SESSION["id_usuario"]) &&
    isset($_SESSION["usuario"])
) {

    header(
        "Location: /VicbamGym/dashboard.php"
    );

    exit();
}


/* =========================================
   MENSAJE DE ERROR
========================================= */

$error = "";


/* =========================================
   PROCESAR LOGIN
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario =
        trim(
            $_POST["usuario"] ?? ""
        );

    $password =
        $_POST["password"] ?? "";


    /* =====================================
       VALIDAR CAMPOS
    ====================================== */

    if (
        $usuario === "" ||
        $password === ""
    ) {

        $error =
            "Ingrese usuario y contraseña.";

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

            $error =
                "Ocurrió un error al procesar el inicio de sesión.";

        } else {


            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $usuario
            );


            mysqli_stmt_execute(
                $stmt
            );


            $resultado =
                mysqli_stmt_get_result(
                    $stmt
                );


            $fila =
                mysqli_fetch_assoc(
                    $resultado
                );


            /* =============================
               VALIDAR USUARIO
            ============================== */

            if (!$fila) {

                $error =
                    "Usuario o contraseña incorrectos.";

            } elseif (
                $fila["estado"] !== "Activo"
            ) {

                $error =
                    "Este usuario se encuentra inactivo.";

            } elseif (
                !password_verify(
                    $password,
                    $fila["password"]
                )
            ) {

                $error =
                    "Usuario o contraseña incorrectos.";

            } else {


                /* =========================
                   LOGIN CORRECTO
                ========================== */

                session_regenerate_id(
                    true
                );


                $_SESSION["id_usuario"] =
                    (int)
                    $fila["id_usuario"];


                $_SESSION["nombre"] =
                    $fila["nombre"];


                $_SESSION["usuario"] =
                    $fila["usuario"];


                $_SESSION["rol"] =
                    $fila["rol"];


                /* =========================
                   REDIRECCIÓN CORRECTA
                ========================== */

                header(
                    "Location: /VicbamGym/dashboard.php"
                );

                exit();

            }


            mysqli_stmt_close(
                $stmt
            );

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
                    $error
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


            <!-- USUARIO -->

            <div class="form-group">

                <label for="usuario">

                    Usuario

                </label>


                <input
                    type="text"
                    name="usuario"
                    id="usuario"
                    maxlength="50"
                    required
                    autofocus
                    value="<?php

                        echo isset($_POST["usuario"])
                            ? htmlspecialchars(
                                $_POST["usuario"]
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