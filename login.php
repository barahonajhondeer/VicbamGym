<?php

session_start();

require_once("config/conexion.php");

$usuario = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios
        WHERE usuario='$usuario'
        AND password='$password'";

$resultado = mysqli_query($conexion, $sql);

if(mysqli_num_rows($resultado) > 0){

    // Guardar el usuario en la sesión
    $_SESSION['usuario'] = $usuario;

    // Redireccionar al Dashboard
    header("Location: dashboard.php");
    exit();

}else{
?>

<!DOCTYPE html>
<html>

<head>

    <title>Error</title>

    <link rel="stylesheet" href="assets/css/styles.css">

</head>

<body class="login-body">

<div class="login-container">

    <h1>VICBAMGYM</h1>

    <div class="error">

        Usuario o contraseña incorrectos

    </div>

    <br>

    <a href="index.php">

        <button>Volver</button>

    </a>

</div>

</body>

</html>

<?php
}
?>