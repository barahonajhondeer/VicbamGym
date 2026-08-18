<?php
require_once("../config/conexion.php");
require_once("../config/verificar_sesion.php");
?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<title>Pagos</title>

<link rel="stylesheet" href="../assets/css/styles.css">

</head>

<body>

<!-- MENÚ -->

<nav class="navbar">

<div class="logo-menu">
<h2>VICBAMGYM</h2>
</div>

<ul class="menu">

<li><a href="../dashboard.php">🏠 Dashboard</a></li>

<li><a href="../clientes/clientes.php">👤 Clientes</a></li>

<li><a href="../membresias/membresias.php">💳 Membresías</a></li>

<li><a href="pagos.php">💰 Pagos</a></li>

<li><a href="../reportes/reportes.php">📊 Reportes</a></li>

<li><a href="../usuarios/usuarios.php">👨‍💼 Usuarios</a></li>

<li><a href="../logout.php">🚪 Salir</a></li>

</ul>

</nav>

<?php
require_once("../config/notificaciones.php");
?>

<div class="contenedor-principal">

<!-- FORMULARIO -->

<div class="form-container">

<h2>REGISTRO DE PAGOS</h2>

<form action="guardar_pago.php" method="POST">

<div class="form-group">

<label>Cliente</label>

<select name="id_cliente" id="id_cliente"> required>

<option value="">Seleccione un cliente</option>

<?php

$sql="SELECT id_cliente,nombres,apellidos
FROM clientes
ORDER BY nombres";

$resultado=mysqli_query($conexion,$sql);

while($cliente=mysqli_fetch_assoc($resultado))
{

?>

<option value="<?php echo $cliente['id_cliente']; ?>">

<?php

echo $cliente['nombres']." ".$cliente['apellidos'];

?>

</option>

<?php


}

?>

</select>

</div>

<div class="form-group">

<label>Membresía</label>

<input
type="hidden"
name="id_membresia"
id="id_membresia">

<div class="form-group">

<label>Tipo de Membresía</label>

<input
type="text"
id="tipo"
readonly>

</div>

<div class="form-group">

<label>Fecha Inicio</label>

<input
type="text"
id="fecha_inicio"
readonly>

</div>

<div class="form-group">

<label>Fecha Fin</label>

<input
type="text"
id="fecha_fin"
readonly>

</div>

<div class="form-group">

<label>Estado</label>

<input
type="text"
id="estado"
readonly>

</div>

<div class="form-group">

    <label>Valor total de la membresía</label>

    <input
        type="number"
        id="valor_total"
        step="0.01"
        readonly>

</div>

<div class="form-group">

    <label>Total abonado</label>

    <input
        type="number"
        id="total_abonado"
        step="0.01"
        readonly>

</div>

<div class="form-group">

    <label>Saldo pendiente</label>

    <input
        type="number"
        id="saldo_pendiente"
        step="0.01"
        readonly>

</div>

<div class="form-group">

    <label>Fecha límite de pago</label>

    <input
        type="date"
        id="fecha_limite_pago"
        readonly>

</div>

<div class="form-group">

    <label>Valor del nuevo abono</label>

    <input
        type="number"
        name="valor"
        id="valor"
        step="0.01"
        min="0.01"
        placeholder="Ingrese el valor del abono"
        required>

</div>

</div>

<div class="form-group">

<label>Método de Pago</label>

<select name="metodo_pago" required>

<option value="">Seleccione</option>

<option>Efectivo</option>

<option>Transferencia</option>

</select>

</div>

<div class="form-group">

<label>Fecha del Pago</label>

<input
type="date"
name="fecha_pago"
value="<?php echo date('Y-m-d');?>"
required>

</div>

<button class="btn-guardar">

Registrar Pago

</button>

</form>

</div>

<!-- TABLA -->

<div class="tabla-container" data-tabla-buscable>

<h2>PAGOS REGISTRADOS</h2>

<div class="herramientas-tabla">

    <div class="buscador-tabla">

        <label for="buscarPagos">
            Buscar pago
        </label>

        <input
            type="search"
            id="buscarPagos"
            data-buscador
            placeholder="Cliente, membresía, método, valor o fecha"
            autocomplete="off">

    </div>

    <span
        class="contador-resultados"
        data-contador-resultados>
    </span>

</div>

<div class="tabla-responsive">

<table id="tablaPagos">

<thead>

<tr>

<th data-ordenable data-tipo="numero">ID</th>

<th data-ordenable>Cliente</th>

<th data-ordenable>Membresía</th>

<th data-ordenable>Valor</th>

<th data-ordenable>Método</th>

<th data-ordenable>Fecha</th>

<th data-ordenable>Acciones</th>

</tr>

</thead>

<tbody>

<?php

$sql="SELECT

p.id_pago,

c.nombres,

c.apellidos,

m.tipo,

p.valor,

p.metodo_pago,

p.fecha_pago

FROM pagos p

INNER JOIN clientes c

ON p.id_cliente=c.id_cliente

INNER JOIN membresias m

ON p.id_membresia=m.id_membresia

ORDER BY p.id_pago DESC";

$resultado=mysqli_query($conexion,$sql);

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<tr>

<td><?php echo $fila['id_pago']; ?></td>

<td><?php echo $fila['nombres']." ".$fila['apellidos']; ?></td>

<td><?php echo $fila['tipo']; ?></td>

<td data-orden="<?php echo $fila['valor']; ?>">
    $ <?php 
    echo number_format(
        (float) $fila['valor'],
        2
    );
     ?>
</td>

<td><?php echo $fila['metodo_pago']; ?></td>

<td data-orden="<?php echo $fila['fecha_pago']; ?>">

    <?php
    echo date(
        "d/m/Y",
        strtotime($fila['fecha_pago'])
    );
    ?>

</td>

<td>

<a
class="btn-editar"
href="editar_pago.php?id=<?php echo $fila['id_pago']; ?>">

Editar

</a>

<a
class="btn-eliminar"
href="eliminar_pago.php?id=<?php echo $fila['id_pago']; ?>"
onclick="return confirm('¿Desea eliminar este pago?')">

Eliminar

</a>

</td>

</tr>

<?php

}

?>

<tr
    data-sin-resultados
    class="fila-busqueda-vacia"
    style="display:none;">

    <td colspan="7">
        No se encontraron pagos.
    </td>

</tr>

</tbody>

</table>

</div>

</div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const cliente =
        document.getElementById("id_cliente");

    const idMembresia =
        document.getElementById("id_membresia");

    const tipo =
        document.getElementById("tipo");

    const fechaInicio =
        document.getElementById("fecha_inicio");

    const fechaFin =
        document.getElementById("fecha_fin");

    const estado =
        document.getElementById("estado");

    const valorTotal =
        document.getElementById("valor_total");

    const totalAbonado =
        document.getElementById("total_abonado");

    const saldoPendiente =
        document.getElementById("saldo_pendiente");

    const fechaLimite =
        document.getElementById("fecha_limite_pago");

    const valorAbono =
        document.getElementById("valor");

    const botonGuardar =
        document.querySelector(".btn-guardar");

    function limpiarCampos() {

        idMembresia.value = "";
        tipo.value = "";
        fechaInicio.value = "";
        fechaFin.value = "";
        estado.value = "";
        valorTotal.value = "";
        totalAbonado.value = "";
        saldoPendiente.value = "";
        fechaLimite.value = "";
        valorAbono.value = "";
        valorAbono.max = "";

    }

    cliente.addEventListener("change", function () {

        const idCliente = this.value;

        limpiarCampos();

        if (idCliente === "") {
            return;
        }

        fetch("obtener_membresia.php", {

            method: "POST",

            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },

            body:
                "id_cliente=" +
                encodeURIComponent(idCliente)

        })

        .then(function (response) {

            if (!response.ok) {
                throw new Error(
                    "No se pudo consultar la membresía."
                );
            }

            return response.json();

        })

        .then(function (data) {

            if (!data) {

                botonGuardar.disabled = true;

                alert(
                    "El cliente no posee una membresía activa."
                );

                return;
            }

            idMembresia.value =
                data.id_membresia;

            tipo.value =
                data.tipo;

            fechaInicio.value =
                data.fecha_inicio;

            fechaFin.value =
                data.fecha_fin;

            estado.value =
                data.estado;

            valorTotal.value =
                parseFloat(data.valor || 0).toFixed(2);

            totalAbonado.value =
                parseFloat(
                    data.total_abonado || 0
                ).toFixed(2);

            saldoPendiente.value =
                parseFloat(
                    data.saldo_pendiente || 0
                ).toFixed(2);

            fechaLimite.value =
                data.fecha_limite_pago ||
                data.fecha_fin;

            valorAbono.max =
                data.saldo_pendiente;

            const saldo =
                parseFloat(
                    data.saldo_pendiente || 0
                );

            if (saldo <= 0) {

                valorAbono.disabled = true;
                botonGuardar.disabled = true;

                botonGuardar.textContent =
                    "Membresía pagada";

            } else {

                valorAbono.disabled = false;
                botonGuardar.disabled = false;

                botonGuardar.textContent =
                    "Registrar abono";
            }

        })

        .catch(function (error) {

            limpiarCampos();

            botonGuardar.disabled = true;

            console.error(error);

            alert(
                "Ocurrió un error al obtener la membresía."
            );

        });

    });

    valorAbono.addEventListener(
        "input",
        function () {

            const saldo =
                parseFloat(
                    saldoPendiente.value
                ) || 0;

            const abono =
                parseFloat(this.value) || 0;

            if (abono <= 0) {

                this.setCustomValidity(
                    "El abono debe ser mayor a cero."
                );

            } else if (abono > saldo) {

                this.setCustomValidity(
                    "El abono no puede superar el saldo pendiente de $" +
                    saldo.toFixed(2)
                );

            } else {

                this.setCustomValidity("");
            }

        }
    );

});

</script>

<script src="../assets/js/tablas.js"></script>

</body>


</html>