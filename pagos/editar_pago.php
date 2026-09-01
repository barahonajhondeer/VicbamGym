<?php

require_once("../config/verificar_sesion.php");

/* =========================================
   EDICIÓN DE PAGOS DESHABILITADA
========================================= */

header(
    "Location: pagos.php?tipo=advertencia&mensaje=" .
    urlencode(
        "Los pagos registrados no pueden editarse. Si existe un error, anule el pago y registre uno nuevo."
    )
);

exit();
?>