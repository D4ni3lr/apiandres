<?php
/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";

$uid = 1234;
$referencia = 'CB-79664';
$resultado = Paymentez::VERIFY($uid, $referencia, Paymentez::VERIFY_BY_AUTH_CODE, '1234');

echo "<h1>Verificando transacción: $referencia</h1>";
echo "<p>Respuesta desde el api</p><br>";
print_r($resultado);

?>

<br>
<br>
<a href="index.html">Regresar al inicio</a>