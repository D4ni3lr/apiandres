<?php

/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";
$uid = 1234;

/* calculo las url de error y success esto no hace parte del api es solo para el ejemplo */
$partes = explode("/", $_SERVER['REQUEST_URI']);
$dir = $_SERVER['HTTP_HOST'];
for ($i = 0; $i < count($partes) - 1; $i++) {
    $dir .= $partes[$i] . "/";
}

$urlSuccess = "http://".$dir."success.html";
$urlError = "http://".$dir."error.php";
$urlReview = "http://".$dir."review.php";

$parametros = [
    'uid'                   => $uid,        //Requerido
    'dev_reference'         => time(),        //Requerido
    'product_description'   => "Prueba desde libreria",        //Requerido
    'product_code'          => '500',        //Requerido
    'product_amount'        => "58000.00",        //Requerido - Hasta el momento transacciones mayores a $100.000 fallan

    'success_url'           => $urlSuccess,
    'failure_url'           => $urlError,
    'review_url'            => $urlReview,

    /* No disponibles para Colombia */
    /*
    'installments_type'     => '',
    'taxable_amount'        => '',
    'tax_percentage'        => '',
    */
];

$resultado = Paymentez::DEBIT_CARD_FRAME($parametros);
echo "<h1>Debitando desde frame</h1>";
echo "<a href='$resultado'>Clic aqui para probar el api.</a>";

?>

<br>
<a href="listaelimina.php">Regresar al listado</a>
<br>
<a href="index.html">Regresar al inicio</a>