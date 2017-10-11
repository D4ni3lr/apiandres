<?php

/**
 * Incluir el api
 */
include_once "lib/paymentez/Paymentez.php";
$uid = 1234;

$listado = Paymentez::LIST_CARD($uid);

?>
<h1>Listado de tarjetas</h1>
<table border="1">
    <tr>
        <th>Referencia</th>
        <th>Nombre</th>
        <th>Termina en</th>
        <th>Eliminar</th>
    </tr>
    <?php foreach ($listado as $tc): ?>
        <tr>
            <td> <?php echo $tc['card_reference'] ?> </td>
            <td> <?php echo $tc['name'] ?> </td>
            <td> <?php echo $tc['termination'] ?> </td>
            <td><a href="eliminartc.php?cr=<?php echo $tc['card_reference']; ?>">Eliminar</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="index.html">Regresar al inicio</a>