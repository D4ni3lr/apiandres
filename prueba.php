<?php

include_once "lib/paymentez/Paymentez.php";

echo Paymentez::ADD_CARD(
    '1234',
    'developer@developer.com',
    'http://localhost:63342/apiandres/success.html',
    'http://localhost:63342/apiandres/error.php');


echo "<h1>List Card</h1>";
var_dump(Paymentez::LIST_CARD( '1234')[0]);