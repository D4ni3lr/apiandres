<?php

/* Definición de constantes */

/**
 * Datos de Paymentez
 */
//defined('APPLICATION_CODE') or define('APPLICATION_CODE', 'CiColApp');
defined('APPLICATION_CODE') or define('APPLICATION_CODE', 'HAPPY-CO');
defined('APPLICATION_KEY') or define('APPLICATION_KEY', 'Vx6v1nGa0GPjv6fmOWCa3IGsa1T45x');
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
     * Este metodo retorna una URL a la cual se le debe hacer un GET
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
            'buyer_phone' => $buyer_phone,
            'response_type' => $responseType,
            'success_url' => $success_url,
            'failure_url' => $failure_url
        ];

        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
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
     * Recibe todos los parametros que la documentación indica, excepto:
     * (Ninguno de los sigueintes parametros debe estar presente en el array ya que hacen fallar el api)
     * - auth_timestamp
     * - auth_token
     * - application_code (Se puede enviar pero será ignorado)
     * @param $params
     */
    public static function DEBIT_CARD($params)
    {
        if (!array_key_exists("session_id", $params)) {
            $params["session_id"] = self::GENERATE_SESSION_ID();
        }
        $params['application_code'] = APPLICATION_CODE;

        $timestamp = time();
        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        $host = self::GET_HOST() . self::$ENDPOINTS["debitCard"];
        $query = self::BUILD_QUERY($params);

        return self::DO_POST($host, $query);
    }

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

        $headers = self::DO_POST($host, $query, false);

        /* En este caso nos interesa que sea el estatus code 200 */
        /* cuando se utiliza file_get_contents en el scope local se define la variable $http_response_header */
        $respuesta = self::PARSE_FILE_GET_CONTENTS_HEADERS($headers);

        return $respuesta["reponse_code"] === 200;
    }

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

        $host = self::GET_HOST() . self::$ENDPOINTS["debitCard"];
        $query = self::BUILD_QUERY($params);

        return self::DO_POST($host, $query);
    }

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

        return self::DO_POST($host, $query);
    }

    public static function DEBIT_CARD_FRAME($params)
    {
        $params['application_code'] = APPLICATION_CODE;

        $timestamp = time();
        $auth_token = self::GENERATE_AUTH_TOKEN($params, $timestamp);
        $params['auth_timestamp'] = $timestamp;
        $params['auth_token'] = $auth_token;

        return self::GENERATE_URL(self::$ENDPOINTS["debitCardFrame"], $params);
    }

    public static function GENERATE_SESSION_ID()
    {
        return self::UUID_SECURE();
    }

    private static function DO_POST($host, $query, $output = true)
    {
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $query
            )
        );
        $context = stream_context_create($opts);

        $rawOutput = file_get_contents($host, false, $context);
        if ($output)
            return json_decode($rawOutput, true);
        else {
            return $http_response_header;
        }
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

    private static function GET_HOST()
    {
        return APPLICATION_MODE == 'prod' ? PROD_HOST : DEV_HOST;
    }

    /**
     * @param $params - Parametros de la solicitud
     * @param $timestamp - Timestamp de la solicitud
     * @return string - Retorna el token de seguridad
     */
    private static function GENERATE_AUTH_TOKEN($params, $timestamp)
    {
        ksort($params);
        $query = self::BUILD_QUERY($params) . '&' . $timestamp . '&' . APPLICATION_KEY;
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

    /**
     * Convierte la respuesta de los encabezados de file_get_contents en un array más facil de leer
     * fuente: http://php.net/manual/en/reserved.variables.httpresponseheader.php#117203
     * @param $headers
     * @return array
     */
    private static function PARSE_FILE_GET_CONTENTS_HEADERS($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1]))
                $head[trim($t[0])] = trim($t[1]);
            else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out))
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }

    /**
     * Esta funcion convierte el uuid de php en su representación numerica.
     * Fue tomada desde: http://php.net/manual/es/function.uniqid.php#96898
     * @param $in - valor del UUID
     * @param bool $to_num - true: Convierte en numero, false: de numero a string
     * @param bool $pad_up - limita la salida a 'n' cantidad de caracteres
     * @param null $passKey - Posible password para pasar a numero|string
     * @return bool|float|int|string
     * @deprecated  - El uid no es solo numerico.
     */
    private
    static function UUID_TO_STRING($in, $to_num = false, $pad_up = false, $passKey = null)
    {
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for ($n = 0; $n < strlen($index); $n++) {
                $i[] = substr($index, $n, 1);
            }

            $passhash = hash('sha256', $passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512', $passKey)
                : $passhash;

            for ($n = 0; $n < strlen($index); $n++) {
                $p[] = substr($passhash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    }
}


/*

DATOS API
application_code: HAPPY-CO
key: Vx6v1nGa0GPjv6fmOWCa3IGsa1T45x
USUARIO: andresbanguera@happyhappyinc.com
CONTRASEÑA: Suzuki069*

RUTA CALLBACK
https://happyhappyinc.com/happy1/app/api/paymentez/data


 */