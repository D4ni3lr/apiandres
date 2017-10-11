<?php

/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";
$uid = 1234;
$referencia = $_GET["cr"];

$parametros = [
    'card_reference'					=> $referencia,		//Requerido
    'product_amount'					=> "100000.00",		//Requerido
    'product_description'				=> "Prueba desde libreria",		//Requerido
    'dev_reference'						=> "1",		//Requerido
    'vat'								=> "116000.00",		//Requerido
    'ip_address'						=> "127.0.0.1",		//Requerido

    /* Si se agrega este indice asegurar que no sea null!*/
    //'session_id'						=> "",		//Requerido

    /* Se ignora */
    //'application_code'					=> "",		//Requerido
    /* Se ignora */

    'uid'								=> $uid,		//Requerido
    'email'								=> "developer@developer.com",		//Requerido

    /* Hace fallar el api */
    //'auth_timestamp'					=> "",		//Requerido
    /* Hace fallar el api */

    /* Hace fallar el api */
    //'auth_token'						=> "",		//Requerido
    /* Hace fallar el api */

    'product_discount'					=> "0.00",		//Opcional
    'Installments'						=> "24",		//Opcional - Supongo que el numero de cuotas
    'buyer_fiscal_number'				=> "123456789",		//Requerido - Cuidado con este parametro no debe estar en el token.
    'buyer_phone'						=> "3165809787",		//Opcional

    /* No aplica para Colombia los siguientes campos */
    /*
    'installments_type'					=> "",		//Opcional
    'taxable_amount'					=> "",		//Opcional
    'tax_percentage'					=> "",		//Opcional
    */
    'seller_id'							=> "",		//Opcional
    'shipping_street'					=> "",		//Opcional
    'shipping_house_number'				=> "",		//Opcional
    'shipping_city'						=> "",		//Opcional
    'shipping_zip'						=> "",		//Opcional
    'shipping_state'					=> "",		//Opcional
    'shipping_country'					=> "",		//Opcional
    'shipping_district'					=> "",		//Opcional
    'shipping_additional_address_info'	=> "",		//Opcional
];

$resultado = Paymentez::DEBIT_CARD($parametros);

NOTA FALLÓ EL PROCESO

echo "Resultado: ".$resultado["status"];