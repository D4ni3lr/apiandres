<?php

/**
 * Datos de Paymentez
 */
defined('APPLICATION_CODE') or define('APPLICATION_CODE', 'HAPPY-CO');
defined('DEV_APPLICATION_KEY') or define('DEV_APPLICATION_KEY', 'Vx6v1nGa0GPjv6fmOWCa3IGsa1T45x');
defined('PROD_APPLICATION_KEY') or define('PROD_APPLICATION_KEY', 'NO TENGO ESTE DATO');

/**
 * Modo de aplicaicón
 * - dev: desarrollo
 * - prod: producción
 * - cualquier otro valor se toma como dev.
 */
defined('APPLICATION_MODE') or define('APPLICATION_MODE', 'dev');

/**
 * Urls del api
 */
defined('DEV_HOST') or define('DEV_HOST', 'https://ccapi-stg.paymentez.com');
defined('PROD_HOST') or define('PROD_HOST', 'https://ccapi.paymentez.com');

class Paymentez
{
    const VERIFY_BY_AMOUNT = "BY_AMOUNT";
    const VERIFY_BY_AUTH_CODE = "BY_AUTH_CODE";

    private static $ENDPOINTS = [
        'addCard' => '/api/cc/add/',
        'listCard' => '/api/cc/list/',
        'debitCard' => '/api/cc/debit/',
        'deleteCard' => '/api/cc/delete/',
        'verify' => '/api/cc/verify/',
        'refund' => '/api/cc/refund/',
        'debitCardFrame' => '/api/cc/pay',
    ];

    /**
     * Registra una TC
     * Este metodo retorna una URL a la cual se le debe hacer GET.
     * Notas:
     * - La documentación no indica que el $buyer_phone no se debe usar para generar el auth_token
     * @param $uid - Identificador unico del usuario en la aplicación
     * @param $email - Email del usuario con sesion iniciada
     * @param null $session_id - Código unico de la sesión, si se manda NULL se genera uno automaticamente.
     *                           Se puede generar uno llamado a ::GENERATE_SESSION_ID
     * @param null $buyer_phone - Telefono del comprador, opcional.
     * @return string - url a donde se debe hacer el GET.
     */
    public static function ADD_CARD($uid, $email, $success_url, $failure_url, $session_id = null, $buyer_phone = null)
    {
        $session_id = is_null($session_id) ? self::GENERATE_SESSION_ID() : $session_id;
        $timestamp = time();
        $responseType = "redirect";

        $params = [
            'application_code' => APPLICATION_CODE,
            'uid' => $uid,
            'email' => $email,
            'session_id' => $session_id,
            'response_type' => $responseType,
            'success_url' => $success_url,
            'failure_url' => $failure_url
        ];

        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['buyer_phone'] = $buyer_phone;
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        return self::GENERATE_URL(self::$ENDPOINTS["addCard"], $params);
    }

    /**
     * Genera el listado de TC disponibles par aun usuario determinado.
     * - Ver sección 2.2 tabla 6 para detalle de los atributos de la respuesta.
     * @param $uid
     * @return array
     */
    public static function LIST_CARD($uid)
    {
        $timestamp = time();
        $params = [
            'application_code' => APPLICATION_CODE,
            'uid' => $uid,
        ];
        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        $url = self::GENERATE_URL(self::$ENDPOINTS["listCard"], $params);

        $rawOutput = file_get_contents($url);
        return (json_decode($rawOutput, true));
    }

    /**
     * @param $parametrosRequeridos - Son los parametros necesarios para generar el token.
     * Nota: Ninguno de los sigueintes parametros debe estar presente en el array ya que hacen fallar el api
     * - auth_timestamp
     * - auth_token
     * - application_code (Se puede enviar pero será ignorado)
     * Listado de parametros posibles:
     * - card_reference
     * - product_amount
     * - product_description
     * - dev_reference
     * - vat
     * - ip_address
     * - session_id
     * - uid
     * - email
     * - buyer_fiscal_number
     * - seller_id
     * - shipping_street
     * - shipping_house_number
     * - shipping_city
     * - shipping_zip
     * - shipping_state
     * - shipping_country
     * - shipping_district
     * - shipping_additional_address_info
     *
     * @param array $parametrosOpcionales - Son los demás parametros que recibe el api
     * Listado de parametros posibles:
     * - product_discount
     * - Installments
     * - buyer_phone
     *
     * @return array - Array asociativo con la respuesta del api
     */
    public static function DEBIT_CARD($parametrosRequeridos, $parametrosOpcionales = [])
    {
        if (is_null($parametrosOpcionales)) {
            $parametrosOpcionales = [];
        }

        if (!array_key_exists("session_id", $parametrosRequeridos)) {
            $parametrosRequeridos["session_id"] = self::GENERATE_SESSION_ID();
        }

        $parametrosRequeridos['application_code'] = APPLICATION_CODE;

        $buyer_fiscal_number = $parametrosRequeridos['buyer_fiscal_number'];
        unset($parametrosRequeridos['buyer_fiscal_number']);

        $timestamp = time();
        $auth_token = self::GENERATE_AUTH_TOKEN($parametrosRequeridos, $timestamp);

        $parametrosRequeridos['auth_timestamp'] = $timestamp;
        $parametrosRequeridos['auth_token'] = $auth_token;
        $parametrosRequeridos['buyer_fiscal_number'] = $buyer_fiscal_number;

        $host = self::GET_HOST() . self::$ENDPOINTS["debitCard"];
        $query = self::BUILD_QUERY(array_merge($parametrosRequeridos, $parametrosOpcionales));

        return self::DO_POST_CURL($host, $query);
    }

    /**
     * Elimina una TC no retorna el output del server solo indica si el status code es OK.
     * @param $uid
     * @param $card_reference
     * @return bool
     */
    public static function DELETE_CARD($uid, $card_reference)
    {
        $timestamp = time();
        $params = [
            'application_code' => APPLICATION_CODE,
            'uid' => $uid,
            'card_reference' => $card_reference
        ];
        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        $host = self::GET_HOST() . self::$ENDPOINTS["deleteCard"];
        $query = self::BUILD_QUERY($params);

        $respuesta = self::DO_POST_CURL($host, $query);

        return $respuesta["reponse_code"] === 200;
    }

    /**
     * Varifica las transacciones, ver la doc del api para mas info.
     * @param $uid
     * @param $transaction_id
     * @param $type - Siempre usar: Paymentes::VERIFY_BY_AMOUNT o Paymentez::VERIFY_BY_AUTH_CODE
     * @param $value
     * @return array - - Array asociativo con la respuesta del api
     */
    public static function VERIFY($uid, $transaction_id, $type, $value)
    {
        $timestamp = time();

        $params = [
            'application_code' => APPLICATION_CODE,
            'uid' => $uid,
            'transaction_id' => $transaction_id,
            'type' => $type,
            'value' => $value
        ];

        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        $host = self::GET_HOST() . self::$ENDPOINTS["verify"];
        $query = self::BUILD_QUERY($params);

        return self::DO_POST_CURL($host, $query);
    }

    /**
     * @param $transaction_id
     * @return array - Array asociativo con la respuesta del api
     */
    public static function REFUND($transaction_id)
    {
        $timestamp = time();

        $params = [
            'application_code' => APPLICATION_CODE,
            'transaction_id' => $transaction_id,
        ];

        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        $host = self::GET_HOST() . self::$ENDPOINTS["refund"];
        $query = self::BUILD_QUERY($params);

        return self::DO_POST_CURL($host, $query);
    }

    /**
     * Retorna la URL del formulario de pagos directo.
     * @param $params
     * Parametros disponibles:
     * - uid
     * - dev_reference
     * - product_description
     * - product_code
     * - product_amount
     * - success_url
     * - failure_url
     * - review_url
     * @return string
     */
    public static function DEBIT_CARD_FRAME($params)
    {
        $params['application_code'] = APPLICATION_CODE;

        $timestamp = time();
        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        return self::GENERATE_URL(self::$ENDPOINTS["debitCardFrame"], $params);
    }

    /**
     * Genera un código unico de sesión evitando coliciones.
     * @return bool|string
     */
    public static function GENERATE_SESSION_ID()
    {
        return self::UUID_SECURE();
    }

    /**
     * Hace una petición post del tipo x-www-form-urlencoded
     * @param $host
     * @param $query
     * @return array
     */
    private static function DO_POST_CURL($host, $query)
    {
        $ch = curl_init();

        if (FALSE === $ch)
            return ["Error" => "No fue posible inicializar CURL"];

        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (APPLICATION_MODE === 'dev') {
            //¡Advertencia! se salta certificados self-signed
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (FALSE === $server_output)
            return ["Error" => "Code: " . curl_errno($ch) . " Mensaje: " . curl_error($ch)];

        return ['reponse_code' => $httpcode, 'data' => json_decode($server_output, true)];
    }

    /**
     * Genera las direcciones url para hacer las solicitudes
     * @param $endpoint
     * @param $params
     * @return string
     */
    private static function GENERATE_URL($endpoint, $params)
    {
        $query = self::BUILD_QUERY($params);
        return self::GET_HOST() . $endpoint . '?' . $query;
    }

    /**
     * @return string - Host de prod o desarrollo
     */
    private static function GET_HOST()
    {
        return APPLICATION_MODE == 'prod' ? PROD_HOST : DEV_HOST;
    }

    /**
     * @return string - Key de prod o desarrollo
     */
    public static function GET_KEY(){
        return APPLICATION_MODE == 'prod' ? PROD_APPLICATION_KEY : DEV_APPLICATION_KEY;
    }

    /**
     * @param $params - Parametros de la solicitud
     * @param $timestamp - Timestamp de la solicitud
     * @return string - Retorna el token de seguridad
     */
    public static function GENERATE_AUTH_TOKEN($params, $timestamp)
    {
        ksort($params);
        $query = self::BUILD_QUERY($params) . '&' . $timestamp . '&' . self::GET_KEY();
        $token = hash('sha256', $query);
        return $token;
    }

    /**
     * Funcion que se encarga de facilitar la lectura, y centraliza el uso del RFC
     * @param array $params
     * @return string
     */
    private static function BUILD_QUERY(array $params)
    {
        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Genera un UUID que evita coliciones.
     *
     * Tomado desde: http://php.net/manual/es/function.uniqid.php#120123
     * @param int $lenght
     * @return bool|string
     * @throws Exception
     */
    private static function UUID_SECURE($lenght = 32)
    {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }
}


/*
DATOS API
application_code: HAPPY-CO
key: Vx6v1nGa0GPjv6fmOWCa3IGsa1T45x
Plataforma: https://paymentez.herokuapp.com/
USUARIO: andresbanguera@happyhappyinc.com
CONTRASEÑA: Suzuki069*

RUTA CALLBACK
https://happyhappyinc.com/happy1/app/api/paymentez/data
 */