<?php


/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";

$urlSuccess = "http://localhost:63342/apiandres/success.html";
$urlError = "http://localhost:63342/apiandres/error.php";
$sessionID = null; //Si la aplicación maneja id de sesiones enviarla, se puede enviar una la libreria la genera.
$uid = 1234;
$url = Paymentez::ADD_CARD($uid, 'developer@developer.com', $urlSuccess, $urlError, $sessionID);

?>
<h1>Agregar Tarjeta</h1>
<a href="<?php echo $url; ?>">Clic aqui para agregar tarjeta</a>
<br>
<a href="index.html">Regresar al inicio</a>