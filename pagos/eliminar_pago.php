<?php

require_once("../config/verificar_sesion.php");

/* =========================================
   ESTE ARCHIVO YA NO ELIMINA PAGOS
========================================= */

header(
    "Location: pagos.php?tipo=advertencia&mensaje=" .
    urlencode(
        "Los pagos no pueden eliminarse. Utilice la opción Anular para conservar el historial."
    )
);

exit();
?>