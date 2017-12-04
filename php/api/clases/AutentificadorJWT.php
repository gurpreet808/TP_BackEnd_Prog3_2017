<?php
require_once './vendor/autoload.php';
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
            'aud' => self::Aud(), 
            'data' => $datos
            );

        return JWT::encode($payload, self::$claveSecreta);
    }

    static public function decodificarToken($unToken){
        return JWT::decode($unToken, self::$claveSecreta, array(self::$encript));        
    }

    static public function dataDelToken($unToken){
        $decodificado = self::decodificarToken($unToken);
        return (array) $decodificado->data;      
    }

    static public function refrescarToken($unToken){
        //manejar excepcion de que expiró
        $datos = self::dataDelToken($unToken);
        return self::crearJWT($datos);
    }

    static public function verificarToken($unToken){
        //manejar excepcion de que expiró

        if(empty($token)|| $token==""){
            throw new Exception("El token esta vacio.");
            return false;
        } 
        // las siguientes lineas lanzan una excepcion, de no ser correcto o de haberse terminado el tiempo       
        try {
            $decodificado = JWT::decode($token,self::$claveSecreta,self::$tipoEncriptacion);
        } catch (Exception $e) {           
           throw new Exception("Token no valido --".$e->getMessage());
           return false;
        }
        
        // si no da error,  verifico los datos de AUD que uso para saber de que lugar viene  
        if($decodificado->aud !== self::Aud()){
            throw new Exception("No es el usuario valido");
            return false;
        }
        
        return true;
    }

    private static function Aud(){
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }    
}

