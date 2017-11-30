<?php
class persona
{
	public $id_persona;
 	public $nombre;
  	public $mail;
  	public $sexo;
	private $password;
	public $foto;

  	public function BorrarPersona(){
		  $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		  $consulta =$objetoAccesoDato->RetornarConsulta("DELETE from personas WHERE id_persona=:id_persona");
		  $consulta->bindValue(':id_persona',$this->id_persona, PDO::PARAM_INT);
		  $consulta->execute();
		  
		  return $consulta->rowCount();
	}

	public static function BorrarPersonaPorMail($email){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE from personas WHERE mail=:mail");	
		$consulta->bindValue(':mail',$email, PDO::PARAM_STR);		
		$consulta->execute();
		
		return $consulta->rowCount();
	}
	 
	public function ModificarPersona(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 //modifica enlazando parametros de la instancia
		 $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE personas SET 
		 nombre=:nombre,
		 mail=:mail,
		 sexo=:sexo,
		 password=:password, 
		 foto=:foto
		 
		 WHERE id_persona=:id_persona");
		 
		 $consulta->bindValue(':id_persona',$this->id_persona, PDO::PARAM_INT);
		 $consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		 $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
		 $consulta->bindValue(':sexo', $this->sexo, PDO::PARAM_STR);
		 $consulta->bindValue(':password', $this->password, PDO::PARAM_STR);
		 $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
		 
		 return $consulta->execute();
	}
	 
	public function InsertarPersona(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 //inserta enlazando parametros dela instancia
		 $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO personas (nombre,sexo,mail,password,foto)
		 values(:nombre,:sexo,:mail,:password,:foto)");

		 $consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		 $consulta->bindValue(':sexo', $this->sexo, PDO::PARAM_STR);
		 $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
		 $consulta->bindValue(':password', $this->password, PDO::PARAM_STR);
		 $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
		 
		 return $consulta->execute(); 
	}
	
	public function GuardarPersona(){
        if(empty(persona::TraerUnaPersona($this->mail))){
            $this->InsertarPersona();
            echo "persona guardada";
        } else {
            $lapersona = persona::TraerUnaPersona($this->mail);
            $this->id_persona = $lapersona->id_persona;
            
            if ($this->ModificarPersona()) {
                echo "persona modificada";
            } else {
                echo "No modifico persona";
            }
        }
	}
	 
	public static function TraerTodasLasPersonas(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 $consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_persona, nombre, sexo, mail, foto FROM personas");
		 $consulta->execute();
		 
		 return $consulta->fetchAll(PDO::FETCH_CLASS, "persona");		
	}

	public static function TraerUnaPersona($mail){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_persona, nombre, sexo, mail, foto FROM personas where mail = :mail");
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);
        $consulta->execute();
		$personaBuscado= $consulta->fetchObject('persona');

		return $personaBuscado;
    }

	public static function TraerUnaPersonaPorId($id_persona){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_persona, nombre, sexo, mail, foto FROM personas where id_persona = :id_persona");
		$consulta->bindValue(':id_persona',$id_persona, PDO::PARAM_INT);
		$personaBuscado= $consulta->fetchObject('persona');
		
		return $personaBuscado;
	}

	public static function BorrarPersonaPorParametro($mail){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM personas WHERE mail=:mail");	
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);		
		$consulta->execute();

		return $consulta->rowCount();
    }

	public function mostrarDatos(){
	  	return "Metodo mostar:".$this->id_persona."  ".$this->nombre."  ".$this->sexo."  ".$this->mail."  ".$this->foto;
	}

	public function setPassword($pass){
		$this->password = $pass;
	}

}