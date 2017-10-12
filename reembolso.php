<?php
/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";

$referencia = 'CB-79664';
$resultado = Paymentez::REFUND($referencia);

echo "<h1>Reembolso de transacción: $referencia</h1>";
echo "<p>Respuesta desde el api</p><br>";
print_r($resultado);

?>

<br>
<br>
<a href="index.html">Regresar al inicio</a>