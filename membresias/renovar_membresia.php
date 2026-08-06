<?php

require_once("../config/conexion.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: membresias.php");
    exit();
}

$id_membresia = (int) $_GET['id'];

$sql = "SELECT
            m.*,
            c.nombres,
            c.apellidos
        FROM membresias m
        INNER JOIN clientes c
            ON m.id_cliente = c.id_cliente
        WHERE m.id_membresia = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_membresia
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
$membresia = mysqli_fetch_assoc($resultado);

if (!$membresia) {

    echo "<script>
        alert('La membresía no existe.');
        window.location='membresias.php';
    </script>";

    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Renovar membresía | VICBAMGYM</title>

    <link
        rel="stylesheet"
        href="../assets/css/styles.css">

</head>

<body>

<nav class="navbar">

    <div class="logo-menu">
        <h2>VICBAMGYM</h2>
    </div>

    <ul class="menu">

        <li>
            <a href="../dashboard.php">
                🏠 Dashboard
            </a>
        </li>

        <li>
            <a href="../clientes/clientes.php">
                👥 Clientes
            </a>
        </li>

        <li>
            <a
                href="membresias.php"
                class="menu-activo">

                💳 Membresías

            </a>
        </li>

        <li>
            <a href="../pagos/pagos.php">
                💰 Pagos
            </a>
        </li>

        <li>
            <a href="../reportes/reportes.php">
                📊 Reportes
            </a>
        </li>

        <li>
            <a href="../logout.php">
                🚪 Salir
            </a>
        </li>

    </ul>

</nav>

<div class="contenedor-edicion">

    <div class="form-container">

        <h2>RENOVAR MEMBRESÍA</h2>

        <form
            action="procesar_renovacion.php"
            method="POST">

            <input
                type="hidden"
                name="id_membresia"
                value="<?php
                    echo $membresia['id_membresia'];
                ?>">

            <div class="form-group">

                <label>Cliente</label>

                <input
                    type="text"
                    value="<?php
                        echo htmlspecialchars(
                            $membresia['nombres'] .
                            ' ' .
                            $membresia['apellidos']
                        );
                    ?>"
                    readonly>

            </div>

            <div class="form-group">

                <label>Tipo de membresía</label>

                <select
                    name="tipo"
                    id="tipo_renovacion"
                    required>

                    <option
                        value="Mensual"
                        <?php
                        if (
                            $membresia['tipo'] === 'Mensual'
                        ) {
                            echo 'selected';
                        }
                        ?>>

                        Mensual

                    </option>

                    <option
                        value="Trimestral"
                        <?php
                        if (
                            $membresia['tipo'] === 'Trimestral'
                        ) {
                            echo 'selected';
                        }
                        ?>>

                        Trimestral

                    </option>

                    <option
                        value="Semestral"
                        <?php
                        if (
                            $membresia['tipo'] === 'Semestral'
                        ) {
                            echo 'selected';
                        }
                        ?>>

                        Semestral

                    </option>

                    <option
                        value="Anual"
                        <?php
                        if (
                            $membresia['tipo'] === 'Anual'
                        ) {
                            echo 'selected';
                        }
                        ?>>

                        Anual

                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Valor</label>

                <input
                    type="number"
                    name="valor"
                    id="valor_renovacion"
                    step="0.01"
                    min="0.01"
                    value="<?php
                        echo htmlspecialchars(
                            $membresia['valor']
                        );
                    ?>"
                    required>

            </div>

            <div class="form-group">

                <label>Nueva fecha de inicio</label>

                <input
                    type="date"
                    name="fecha_inicio"
                    value="<?php
                        echo date('Y-m-d');
                    ?>"
                    required>

            </div>

            <button
                type="submit"
                class="btn-guardar">

                Confirmar renovación

            </button>

            <a
                href="membresias.php"
                class="btn-cancelar">

                Cancelar

            </a>

        </form>

    </div>

</div>

<script>

const precios = {
    Mensual: 25,
    Trimestral: 65,
    Semestral: 120,
    Anual: 220
};

const tipo = document.getElementById("tipo_renovacion");
const valor = document.getElementById("valor_renovacion");

tipo.addEventListener("change", function () {

    valor.value = precios[this.value] || "";

});

</script>

</body>

</html>