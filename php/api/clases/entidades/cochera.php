<?php

class Cochera
{
    public $id_cochera;
    public $piso;
    public $especial;

    public function BorrarCochera(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM cocheras WHERE id_cochera=:id_cochera");
        $consulta->bindValue(':id_cochera',$this->id_cochera, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
    
    public function ModificarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE cocheras SET 
        piso=:piso, 
        especial=:especial
        WHERE id_cochera=:id_cochera");

		$consulta->bindValue(':id_cochera',$this->id_cochera, PDO::PARAM_INT);
		$consulta->bindValue(':piso',$this->piso, PDO::PARAM_STR);
		$consulta->bindValue(':especial', $this->especial, PDO::PARAM_STR);

		return $consulta->execute();
    }

    public function InsertarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO cocheras 
        (piso, especial)
        VALUES (:piso, :especial)");
        
        $consulta->bindValue(':piso',$this->piso, PDO::PARAM_STR);
        $consulta->bindValue(':especial',$this->especial, PDO::PARAM_STR);

		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public static function TraerTodosLasCocheras(){
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
}
?>