<?php

session_start();

session_unset();
session_destroy();

header(
    "Location: index.php?tipo=info&mensaje=" .
    urlencode("La sesión se cerró correctamente.")
);

exit();

?>