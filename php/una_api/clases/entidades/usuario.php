<?php

class Usuario{
    public $id = null;
    public $nombre = null;
    public $apellido = null;
    public $sexo = null;    
    public $correo = null;    
    private $clave = null;
    public $nivel = null;

    public function ToJSON(){
        //Se puede poner en private los atributos que no quiero que salgan en el JSON
        //return '{"id":'.$this->id.',"nombre":"'.$this->nombre.'","apellido":"'.$this->apellido.'","sexo":"'.$this->sexo.'","correo":"'.$this->correo.'","nivel":'.$this->nivel.'}';
        return json_encode($this);
    }

    public function getClave(){
        return $this->clave;
    }

    public function setClave($pass){
        $this->clave = $pass;
    }

    public static function TraerTodosLosUsuariosJSON(){
        $usuarios = self::TraerTodosLosUsuarios();

        $stringArrayUsuarios = "[";
        for ($i=0; $i < count($usuarios); $i++) { 
            $stringArrayUsuarios = $stringArrayUsuarios.$usuarios[$i]->toJSON().",";
        }
        $stringArrayUsuarios = substr($stringArrayUsuarios,0,-1) ."]";

        return $stringArrayUsuarios;
    }
    
    public function BorrarUsuario(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM usuarios WHERE id_usuario=:id");
        $consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
    
    public function ModificarUsuario(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //modifica enlazando parametros de la instncia
		$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE usuarios SET 
        nombre=:nombre, 
        apellido=:apellido,
        sexo=:sexo,
        correo=:correo,
        clave=:clave, 
        nivel=:nivel
        WHERE id_usuario=:id");

		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
		$consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		$consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':sexo', $this->sexo, PDO::PARAM_STR);
        $consulta->bindValue(':correo', $this->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':nivel', $this->nivel, PDO::PARAM_INT);

		return $consulta->execute();
    }
    
    public function InsertarUsuario(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //inserta enlazando parametros dela instancia
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO usuarios (nombre,apellido,sexo,correo,clave,nivel)
        values(:nombre,:apellido,:sexo,:correo,:clave,:nivel)");
        
		$consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		$consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':sexo', $this->sexo, PDO::PARAM_STR);
        $consulta->bindValue(':correo', $this->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':nivel', $this->nivel, PDO::PARAM_INT);
		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public function GuardarUsuario(){
        if(empty(Usuario::TraerUnUsuario($this->correo))){
            $this->InsertarUsuario();
            echo "Usuario guardado";
        } else {
            $elUsuario = Usuario::TraerUnUsuario($this->correo);
            $this->id = $elUsuario->id;
            
            if ($this->clave==null) {
                $this->clave = $elUsuario->getClave();
            }
            
            if ($this->ModificarUsuario()) {
                echo "Usuario modificado";
            } else {
                echo "No modifico Usuario";
            }
        }
    }
    
    public static function TraerTodosLosUsuarios(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_usuario AS id, nombre, apellido, sexo, correo, clave, nivel FROM usuarios");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");
    }
    
    public static function TraerUnUsuario($mail){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_usuario AS id, nombre, apellido, sexo, correo, clave, nivel FROM usuarios where correo = :mail");
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);
        $consulta->execute();
		$UsuarioBuscado= $consulta->fetchObject('Usuario');
		return $UsuarioBuscado;
    }

    public static function TraerUnUsuarioPorId($id){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_usuario AS id, nombre, apellido, sexo, correo, clave, nivel FROM usuarios where id_usuario = :id");
		$consulta->bindValue(':id',$id, PDO::PARAM_STR);
        $consulta->execute();
		$UsuarioBuscado= $consulta->fetchObject('Usuario');
		return $UsuarioBuscado;
    }

    public static function BorrarUsuarioPorParametro($mail){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM usuarios WHERE correo=:mail");	
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);		
		$consulta->execute();
		return $consulta->rowCount();
    }

    public static function VerificarClave($mail, $pass){
        if(empty(Usuario::TraerUnUsuario($mail))){
            return "NOMAIL";
        } else {
            $unUsuario = self::TraerUnUsuario($mail);
            
            if ($unUsuario->clave == $pass) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function TraerUsuarioJSON($mail){
        if(empty(Usuario::TraerUnUsuario($mail))){
            return "Mail no registrado";
        } else {
            $unUsuario = self::TraerUnUsuario($mail);
            return $unUsuario->ToJSON();
        }
    }
}
?>