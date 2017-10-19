<?php
include_once("Paymentez.php");

class PaymentezCallback
{

    /**
     * @return bool | array
     */
    public static function validateRequest()
    {
        if (empty($_POST) || !isset($_POST["stoken"])) {
            PaymentezCallback::tokenError();
            return false;
        }
        /* Se debe validar el token */
        $token = $_POST["stoken"];
        $transaction_id = $_POST["transaction_id"];
        $application_code = APPLICATION_CODE;
        $user_id = $_POST["user_id"];
        $app_key = Paymentez::GET_KEY();

        $tokenDianmico = md5($transaction_id . "_" . $application_code . "_" . $user_id . "_" . $app_key);

        if (strtolower($tokenDianmico) !== strtolower($token)) {
            PaymentezCallback::tokenError();
            return false;
        }

        //La petición es correcta.
        $result = array();

        $result['date'] = isset($_POST['date']) ? $_POST['date'] : null;
        $result['paid_date'] = isset($_POST['paid_date']) ? $_POST['paid_date'] : null;
        $result['application_code'] = isset($_POST['application_code']) ? $_POST['application_code'] : null;
        $result['user_id'] = isset($_POST['user_id']) ? $_POST['user_id'] : null;
        $result['transaction_id'] = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : null;
        $result['recurrent_transaction_id'] = isset($_POST['recurrent_transaction_id']) ? $_POST['recurrent_transaction_id'] : null;
        $result['product_id'] = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        $result['token'] = isset($_POST['token']) ? $_POST['token'] : null;
        $result['stoken'] = isset($_POST['stoken']) ? $_POST['stoken'] : null;
        $result['currency'] = isset($_POST['currency']) ? $_POST['currency'] : null;
        $result['gross_value'] = isset($_POST['gross_value']) ? $_POST['gross_value'] : null;
        $result['num_coins'] = isset($_POST['num_coins']) ? $_POST['num_coins'] : null;
        $result['product_description'] = isset($_POST['product_description']) ? $_POST['product_description'] : null;
        $result['carrier'] = isset($_POST['carrier']) ? $_POST['carrier'] : null;
        $result['payment_method'] = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
        $result['dev_reference'] = isset($_POST['dev_reference']) ? $_POST['dev_reference'] : null;
        $result['status'] = isset($_POST['status']) ? $_POST['status'] : null;
        $result['test_mode'] = isset($_POST['test_mode']) ? $_POST['test_mode'] : null;
        $result['buyer_first_name'] = isset($_POST['buyer_first_name']) ? $_POST['buyer_first_name'] : null;
        $result['buyer_last_name'] = isset($_POST['buyer_last_name']) ? $_POST['buyer_last_name'] : null;
        $result['buyer_phone'] = isset($_POST['buyer_phone']) ? $_POST['buyer_phone'] : null;
        $result['buyer_ip'] = isset($_POST['buyer_ip']) ? $_POST['buyer_ip'] : null;
        $result['buyer_email'] = isset($_POST['buyer_email']) ? $_POST['buyer_email'] : null;
        $result['buyer_street'] = isset($_POST['buyer_street']) ? $_POST['buyer_street'] : null;
        $result['buyer_number'] = isset($_POST['buyer_number']) ? $_POST['buyer_number'] : null;
        $result['buyer_complement'] = isset($_POST['buyer_complement']) ? $_POST['buyer_complement'] : null;
        $result['information.        '] = isset($_POST['information.        ']) ? $_POST['information.        '] : null;
        $result['buyer_district'] = isset($_POST['buyer_district']) ? $_POST['buyer_district'] : null;
        $result['buyer_city'] = isset($_POST['buyer_city']) ? $_POST['buyer_city'] : null;
        $result['buyer_state'] = isset($_POST['buyer_state']) ? $_POST['buyer_state'] : null;
        $result['buyer_zip_code'] = isset($_POST['buyer_zip_code']) ? $_POST['buyer_zip_code'] : null;
        $result['buyer_country'] = isset($_POST['buyer_country']) ? $_POST['buyer_country'] : null;
        $result['pm_user_id'] = isset($_POST['pm_user_id']) ? $_POST['pm_user_id'] : null;
        $result['usd_amount'] = isset($_POST['usd_amount']) ? $_POST['usd_amount'] : null;

        return $result;
    }

    public static function success()
    {
        header('X-PHP-Response-Success: 200', true, 200);
    }

    public static function productIdError()
    {
        header('X-PHP-Response-ProductId-Error: 201', true, 201);
    }

    public static function userIdError()
    {
        header('X-PHP-Response-UserId-Error: 202', true, 202);
    }

    public static function tokenError()
    {
        header('X-PHP-Response-Token-Error: 203', true, 203);
    }

    public static function transactionIdAlreadyReceived()
    {
        header('X-PHP-Response-TransactionIdAlreadyReceived: 204', true, 204);
    }
}

//Se valida la transacción
$resultado = PaymentezCallback::validateRequest();

if ($resultado !== false) {
    //Se debe realizar la logica de la aplicación
    /* Se valida el estatus */

    switch ($resultado["status"]){
        case "1":
            //success
            echo "Transacción aprobada";
            logica();
            break;
        case "2":
            //cancelado
            echo "Transacción cancelada";
            logica();
            break;
        case "4":
            //rechazado
            echo "Transacción rechazada";
            break;
    }
}

function logica(){
    /* Se debe validar lo siguiente */

    //1) Si el product id está malo llamar a
    //   PaymentezCallback::productIdError();
    //   y Salir

    //2) Si el user id está malo llamar a
    //   PaymentezCallback::userIdError();
    //   y Salir

    //3) Si ya se habia procesado el numero de transacción llamar a
    //   PaymentezCallback::transactionIdAlreadyReceived();
    //   y Salir

    //4) si la transacción es cancelada hacer la logica correspondiente  llamar a
    //   PaymentezCallback::transactionIdAlreadyReceived();
    //   y Salir

    //5) si todo está ok llamar a
    //   PaymentezCallback::success();
    //   y Salir

}
//En caso contrario ya retorna 203.