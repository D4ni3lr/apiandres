<?php

/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";
$uid = 1234;
$referencia = $_GET["cr"];

if( $referencia === "" ){
    echo  "Bad request";
    exit;
}

$result = Paymentez::DELETE_CARD($uid, $referencia);

if ($result === true){
    echo "Se ha eliminado correctamente la TC";
}else{
    echo "Error eliminando la TC";
}

?>

<a href="listaelimina.php">Regresar al listado</a>
<a href="index.html">Regresar al inicio</a>