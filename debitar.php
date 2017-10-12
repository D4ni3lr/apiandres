<?php

/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";
$uid = 1234;
$referencia = $_GET["cr"];

$parametrosRequeridos = [
    'card_reference'        => $referencia,        //Requerido
    'product_amount'        => "58000.00",        //Requerido - Hasta el momento transacciones mayores a $100.000 fallan
    'product_description'   => "Prueba desde libreria",        //Requerido
    'dev_reference'         => time(),        //Requerido
    'vat'                   => "8000.00",        //Requerido
    'ip_address'            => "192.168.94.10",        //Requerido

    /* Si se agrega este indice asegurar que no sea null!*/
    //'session_id'			=> "",		//Requerido

    /* Se ignora */
    //'application_code'	=> "",		//Requerido

    'uid'                   => $uid,        //Requerido
    'email'                 => "developer@developer.com",        //Requerido

    /* Hace fallar la librería no enviar ya que se genera automatico */
    //'auth_timestamp'		=> "",		//Requerido
    //'auth_token'			=> "",		//Requerido

    'buyer_fiscal_number'   => "123456789",        //Requerido - Cuidado con este parametro no debe estar en el token.

    /* aunque son opcionales la documentación indica que si se envian deben ser incluidos en el token de seguridad */
    'seller_id'                         => "",        //Opcional
    'shipping_street'                   => "",        //Opcional
    'shipping_house_number'             => "",        //Opcional
    'shipping_city'                     => "",        //Opcional
    'shipping_zip'                      => "",        //Opcional
    'shipping_state'                    => "",        //Opcional
    'shipping_country'                  => "",        //Opcional
    'shipping_district'                 => "",        //Opcional
    'shipping_additional_address_info'  => "",        //Opcional
/**/

];

$parametrosOpcionales = [

    'product_discount'                  => "0.00",        //Opcional
    'Installments'                      => "24",        //Opcional - Al parecer no son el numero de cuotas
    'buyer_phone'                       => "3165809787",        //Opcional

    /* No aplica para Colombia los siguientes campos */
    /*
    'installments_type'					=> "",		//Opcional
    'taxable_amount'					=> "",		//Opcional
    'tax_percentage'					=> "",		//Opcional
    */
];

$resultado = Paymentez::DEBIT_CARD($parametrosRequeridos, $parametrosOpcionales);
echo "<h1>Debitando desde tarjeta: $referencia</h1>";
echo "<p>Respuesta desde el api</p><br>";
print_r($resultado);

?>

<br>
<a href="listaelimina.php">Regresar al listado</a>
<br>
<a href="index.html">Regresar al inicio</a>