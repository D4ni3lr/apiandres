<?php


/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";

/* calculo las url de error y success esto no hace parte del api es solo para el ejemplo */
$partes = explode("/", $_SERVER['REQUEST_URI']);
$dir = $_SERVER['HTTP_HOST'];
for ($i = 0; $i < count($partes) - 1; $i++) {
    $dir .= $partes[$i] . "/";
}

$urlSuccess = "http://".$dir."success.html";
$urlError = "http://".$dir."error.php";

$sessionID = null; //Si la aplicación maneja id de sesiones enviarla, se puede enviar una la libreria la genera.
$uid = 1234;
$url = Paymentez::ADD_CARD($uid, 'developer@developer.com', $urlSuccess, $urlError, $sessionID, '3146785432');

?>
<h1>Agregar Tarjeta</h1>
<a href="<?php echo $url; ?>">Clic aqui para agregar tarjeta</a>
<br>
<a href="index.html">Regresar al inicio</a>