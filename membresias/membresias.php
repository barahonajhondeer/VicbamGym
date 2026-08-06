<?php
require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");
/* Actualizar automáticamente las membresías vencidas */

$sqlActualizarEstados = "UPDATE membresias
                         SET estado = 'Vencida'
                         WHERE fecha_fin < CURDATE()
                         AND estado <> 'Vencida'";

mysqli_query($conexion, $sqlActualizarEstados);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Membresías</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>
    
<nav class="navbar">

<div class="logo-menu">
    <h2>VICBAMGYM</h2>
</div>

<ul class="menu">

    <li><a href="../dashboard.php">🏠 Dashboard</a></li>

    <li><a href="../clientes/clientes.php">👤 Clientes</a></li>

    <li><a href="../membresias/membresias.php">💳 Membresías</a></li>

    <li><a href="../pagos/pagos.php">💰 Pagos</a></li>

    <li><a href="../reportes/reportes.php">📊 Reportes</a></li>

    <li><a href="../usuarios/usuarios.php">👨‍💼 Usuarios</a></li>

    <li><a href="../logout.php">🚪 Salir</a></li>

</ul>

</nav>

<?php
require_once("../config/notificaciones.php");
?>

<div class="contenedor-principal">

<!-- ========================= -->
<!-- FORMULARIO -->
<!-- ========================= -->

<div class="form-container">

<h2>REGISTRO DE MEMBRESÍAS</h2>

<form action="guardar_membresia.php" method="POST">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" required>

<option value="">Seleccione un cliente</option>

<?php

$sql="SELECT id_cliente,nombres,apellidos
FROM clientes
ORDER BY nombres";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<option
value="<?php echo $fila['id_cliente']; ?>">

<?php
echo $fila['nombres']." ".$fila['apellidos'];
?>

</option>

<?php
}
?>

</select>

</div>

<div class="form-group">

<label>Tipo de Membresía</label>

<select name="tipo" required>

<option value="">Seleccione</option>

<option value="Mensual">Mensual</option>

<option value="Trimestral">Trimestral</option>

<option value="Semestral">Semestral</option>

<option value="Anual">Anual</option>

</select>

</div>

<div class="form-group">

<label>Fecha Inicio</label>

<input
type="date"
name="fecha_inicio"
required>

</div>

<button class="btn-guardar">

Guardar Membresía

</button>

</form>

</div>

<!-- ========================= -->
<!-- TABLA -->
<!-- ========================= -->

<div class="tabla-container">

<h2>MEMBRESÍAS REGISTRADAS</h2>

<table>

<tr>

<th>ID</th>

<th>Cliente</th>

<th>Tipo</th>

<th>Inicio</th>

<th>Fin</th>

<th>Estado</th>

<th>Días restantes</th>

<th>Acciones</th>

</tr>

<?php

$sql = "SELECT
            m.id_membresia,
            m.id_cliente,
            m.valor,
            c.nombres,
            c.apellidos,
            c.cedula,
            m.tipo,
            m.fecha_inicio,
            m.fecha_fin,
            m.estado,
            DATEDIFF(m.fecha_fin, CURDATE()) AS dias_restantes
        FROM membresias m
        INNER JOIN clientes c
            ON m.id_cliente = c.id_cliente
        ORDER BY m.id_membresia DESC";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die(
        "Error al consultar las membresías: " .
        mysqli_error($conexion)
    );
}

while ($fila = mysqli_fetch_assoc($resultado)) {

    $diasRestantes = (int) $fila['dias_restantes'];

    /* Definir color de la fila */

    $claseFila = "";

    if ($fila['estado'] === 'Vencida' || $diasRestantes < 0) {

        $claseFila = "fila-vencida";

    } elseif ($diasRestantes <= 5) {

        $claseFila = "fila-proxima";
    }

?>

<tr class="<?php echo $claseFila; ?>">

    <td>
        <?php echo $fila['id_membresia']; ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $fila['nombres'] . " " . $fila['apellidos']
        );
        ?>
    </td>

    <td>
        <?php echo htmlspecialchars($fila['tipo']); ?>
    </td>

    <td>
        <?php echo $fila['fecha_inicio']; ?>
    </td>

    <td>
        <?php echo $fila['fecha_fin']; ?>
    </td>

    <!-- ESTADO -->

    <td>

        <?php

        if ($fila['estado'] === 'Activa') {

            if ($diasRestantes <= 5) {

                echo "
                <span class='estado-proxima'>
                    ⚠ Próxima a vencer
                </span>
                ";

            } else {

                echo "
                <span class='estado-activa'>
                    Activa
                </span>
                ";
            }

        } else {

            echo "
            <span class='estado-vencida'>
                Vencida
            </span>
            ";
        }

        ?>

    </td>

    <!-- DÍAS RESTANTES -->

    <td>

        <?php

        if (
            $fila['estado'] === 'Vencida' ||
            $diasRestantes < 0
        ) {

            echo "
            <span class='dias-vencida'>
                Vencida hace " .
                abs($diasRestantes) .
                " días
            </span>
            ";

        } elseif ($diasRestantes === 0) {

            echo "
            <span class='dias-hoy'>
                Vence hoy
            </span>
            ";

        } elseif ($diasRestantes <= 5) {

            echo "
            <span class='dias-proximos'>
                Faltan $diasRestantes días
            </span>
            ";

        } else {

            echo "
            <span class='dias-normales'>
                Faltan $diasRestantes días
            </span>
            ";
        }

        ?>

    </td>

    <!-- ACCIONES -->

    <td class="acciones-membresia">

        <?php

        if (
            $fila['estado'] === 'Vencida' ||
            $diasRestantes <= 5
        ) {

        ?>

            <a
                href="renovar_membresia.php?id=<?php
                    echo $fila['id_membresia'];
                ?>"
                class="btn-renovar">

                Renovar

            </a>

        <?php } ?>

        <a
            class="btn-editar"
            href="editar_membresia.php?id=<?php
                echo $fila['id_membresia'];
            ?>">

            Editar

        </a>

        <a
            class="btn-eliminar"
            href="eliminar_membresia.php?id=<?php
                echo $fila['id_membresia'];
            ?>"
            onclick="return confirm(
                '¿Desea eliminar esta membresía?'
            )">

            Eliminar

        </a>

    </td>

</tr>

<?php

}

?>