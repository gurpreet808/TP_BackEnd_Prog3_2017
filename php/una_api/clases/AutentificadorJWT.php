<?php
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

class autentificadorJWT
{
    private static $claveSecreta = "abcd1234";
    private static $encript = 'HS256';

    static public function crearJWT($datos){
        $ahora = time();
        
        $payload = array(
            'iat' => $ahora, 
            'exp' => $ahora+60*2, 
            'data' => $datos
            );

        return JWT::encode($payload, self::$claveSecreta);
    }

    static public function decodificarToken($unToken){
        return JWT::decode($unToken, self::$claveSecreta, array(self::$encript));        
    }

    static public function dataDelToken($unToken){
        $decodificado = JWT::decode($unToken, self::$claveSecreta, array(self::$encript));
        return (array) $decodificado->data;      
    }

    static public function refrescarToken($unToken){
        //manejar excepcion de que expiró
        $decodificado = JWT::decode($unToken, self::$claveSecreta, array(self::$encript));
        return self::crearJWT((array) $decodificado->data);
    }
}

