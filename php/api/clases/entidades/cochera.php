<?php

class Cochera
{
    public $id_cochera;
    public $nombre;
    public $especial;

    public static function BorrarCochera($id){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM cocheras WHERE id_cochera=:id_cochera");
        $consulta->bindValue(':id_cochera',$id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->rowCount();
    }
    
    public function ModificarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE cocheras SET 
        nombre=:nombre, 
        especial=:especial
        WHERE id_cochera=:id_cochera");

		$consulta->bindValue(':id_cochera',$this->id_cochera, PDO::PARAM_INT);
		$consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		$consulta->bindValue(':especial', $this->especial, PDO::PARAM_STR);

		return $consulta->execute();
    }

    public function InsertarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO cocheras 
        (nombre, especial)
        VALUES (:nombre, :especial)");
        
        $consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':especial',$this->especial, PDO::PARAM_STR);

		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public static function TraerTodasLasCocheras(){
      $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
      $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM cocheras");
      $consulta->execute();			
      return $consulta->fetchAll(PDO::FETCH_CLASS, "cochera");
    }
    
    public static function TraerUnaCochera($id_cochera){
      $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
      $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM cocheras WHERE id_cochera = :id_cochera");
      $consulta->bindValue(':id_cochera',$id_cochera, PDO::PARAM_STR);
      $consulta->execute();
      $cocheraBuscado= $consulta->fetchObject('cochera');
      
      return $cocheraBuscado;
    }

    public static function CocherasMasUsadas(){
      $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
      $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM `cocheras` WHERE `usos`= (SELECT MAX(`usos`) FROM `cocheras`);");
      $consulta->execute();			
      
      return $consulta->fetchAll(PDO::FETCH_CLASS, "cochera");
    }

    public static function CocherasMenosUsadas(){
      $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
      $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM `cocheras` WHERE `usos` = ( SELECT MIN(`usos`) FROM  `cocheras` WHERE  `usos` > 0 )");
      $consulta->execute();			
      return $consulta->fetchAll(PDO::FETCH_CLASS, "cochera");
    }

    public static function CocherasSinUso(){
      $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
      $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM `cocheras` WHERE `usos` = 0");
      $consulta->execute();			
      return $consulta->fetchAll(PDO::FETCH_CLASS, "cochera");
    }
  }
?>