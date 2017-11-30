<?php

class Cochera
{
    public $idCochera;
    public $piso;
    public $patente;
    public $contUsos = 0;

    public function BorrarCochera(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM cocheras WHERE idCochera=:idCochera");
        $consulta->bindValue(':idCochera',$this->idCochera, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
    
    public function ModificarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE cocheras SET 
        piso=:piso, 
        patente=:patente,
        contUsos=:contUsos
        WHERE idCochera=:idCochera");

		$consulta->bindValue(':idCochera',$this->idCochera, PDO::PARAM_INT);
		$consulta->bindValue(':piso',$this->piso, PDO::PARAM_STR);
		$consulta->bindValue(':patente', $this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':contUsos', $this->contUsos, PDO::PARAM_INT);

		return $consulta->execute();
    }

    public function InsertarCochera(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO cocheras 
        (piso)
        VALUES (:piso)");
        
		$consulta->bindValue(':piso',$this->piso, PDO::PARAM_STR);
		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public static function TraerTodosLasCocheras(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT idCochera, piso, patente, contUsos FROM cocheras");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "cochera");
    }
    
    public static function TraerUnaCochera($piso){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT idCochera, piso, patente, contUsos FROM cocheras WHERE piso = :piso");
		$consulta->bindValue(':piso',$piso, PDO::PARAM_STR);
        $consulta->execute();
		$cocheraBuscado= $consulta->fetchObject('cochera');
		return $cocheraBuscado;
    }

    public static function BorrarCocheraPorParametro($piso){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM cocheras WHERE piso = :piso");	
		$consulta->bindValue(':piso',$piso, PDO::PARAM_STR);		
		$consulta->execute();
		return $consulta->rowCount();
    }
    
    public static function CargarVehiculoEnCochera($quePiso, $unaPatente){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $unaCochera = Cochera::TraerUnaCochera($quePiso);

        $unaCochera->patente = $unaPatente;
        $unaCochera->contUsos++;
        
		return $unaCochera->ModificarCochera();
    }
}
?>