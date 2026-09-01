<?php

require_once("../config/verificar_sesion.php");


/* =========================================
   ACTUALIZACIÓN DE PAGOS DESHABILITADA
========================================= */

header(
    "Location: pagos.php?tipo=advertencia&mensaje=" .
    urlencode(
        "Los pagos registrados no pueden modificarse. Si existe un error, anule el pago y registre uno nuevo."
    )
);

exit();
?>