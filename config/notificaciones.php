<?php

$tipoNotificacion = $_GET["tipo"] ?? "";
$mensajeNotificacion = $_GET["mensaje"] ?? "";

$tiposPermitidos = [
    "exito",
    "error",
    "advertencia",
    "info"
];

if (
    $mensajeNotificacion !== "" &&
    in_array(
        $tipoNotificacion,
        $tiposPermitidos,
        true
    )
) {
?>

    <div
        id="notificacion-toast"
        class="notificacion-toast <?php
            echo htmlspecialchars($tipoNotificacion);
        ?>">

        <div class="notificacion-icono">

            <?php

            switch ($tipoNotificacion) {

                case "exito":
                    echo "✓";
                    break;

                case "error":
                    echo "✕";
                    break;

                case "advertencia":
                    echo "⚠";
                    break;

                default:
                    echo "ℹ";
            }

            ?>

        </div>

        <div class="notificacion-contenido">

            <strong>

                <?php

                switch ($tipoNotificacion) {

                    case "exito":
                        echo "Operación exitosa";
                        break;

                    case "error":
                        echo "Ocurrió un error";
                        break;

                    case "advertencia":
                        echo "Advertencia";
                        break;

                    default:
                        echo "Información";
                }

                ?>

            </strong>

            <span>
                <?php
                echo htmlspecialchars($mensajeNotificacion);
                ?>
            </span>

        </div>

        <button
            type="button"
            class="notificacion-cerrar"
            id="cerrar-notificacion"
            aria-label="Cerrar">

            ×

        </button>

        <div class="notificacion-progreso"></div>

    </div>

    <script>

    document.addEventListener(
        "DOMContentLoaded",
        function () {

            const toast =
                document.getElementById(
                    "notificacion-toast"
                );

            const cerrar =
                document.getElementById(
                    "cerrar-notificacion"
                );

            if (!toast) {
                return;
            }

            requestAnimationFrame(function () {
                toast.classList.add("mostrar");
            });

            function cerrarToast() {

                toast.classList.remove("mostrar");
                toast.classList.add("ocultar");

                setTimeout(function () {
                    toast.remove();
                }, 350);
            }

            cerrar.addEventListener(
                "click",
                cerrarToast
            );

            setTimeout(
                cerrarToast,
                4500
            );

            /*
            Quitar los parámetros de notificación
            de la URL sin recargar la página.
            */

            const url =
                new URL(window.location.href);

            url.searchParams.delete("tipo");
            url.searchParams.delete("mensaje");

            window.history.replaceState(
                {},
                document.title,
                url.pathname +
                url.search +
                url.hash
            );

        }
    );

    </script>

<?php
}
?>