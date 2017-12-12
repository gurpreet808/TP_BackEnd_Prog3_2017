<?php

class Operacion{
    public $patente = null;
    public $color = null;
    public $marca = null;
    public $cochera = null;
    public $foto = null;// Agregar path de foto por defecto   
    public $id_empleado_ingreso = null;
    public $fecha_hora_ingreso = null;
    public $id_empleado_salida = null;
    public $fecha_hora_salida = null;
    public $tiempo = null;
    public $importe = null;

    public function ToJSON(){
        //Se puede poner en private los atributos que no quiero que salgan en el JSON
        return json_encode($this);
    }

    public static function TraerTodasLasOperacionesJSON(){
        $Operaciones = self::TraerTodasLasOperaciones();

        $stringArrayOperaciones = "[";
        for ($i=0; $i < count($Operaciones); $i++) { 
            $stringArrayOperaciones = $stringArrayOperaciones.$Operaciones[$i]->toJSON().",";
        }
        $stringArrayOperaciones = substr($stringArrayOperaciones,0,-1) ."]";

        return $stringArrayOperaciones;
    }
    
    public function BorrarOperacion(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM operaciones WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");
        $consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$this->fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->rowCount();
    }

    public static function CocherasLibres($opt){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        /*$consulta = $objetoAccesoDato->RetornarConsulta("SELECT `id_cochera` FROM `cocheras` 
        WHERE `id_cochera` NOT IN (
            SELECT  `cochera`
            FROM    `operaciones`
            WHERE   `fecha_hora_salida` IS NULL
        ) AND `especial` IS NULL");*/
        $consultaSQL = "SELECT `id_cochera` FROM `cocheras` 
        WHERE `id_cochera` NOT IN (
            SELECT  `cochera`
            FROM    `operaciones`
            WHERE   `fecha_hora_salida` IS NULL
        ) ";
        switch ($opt) {
            case 'no':
                $consultaSQL = $consultaSQL."AND `especial` IS NULL";
                break;
            
            case 'si':
                $consultaSQL = $consultaSQL."AND `especial` = 'si'";
                break;

            default:
                # code...
                break;
        }
        $consulta = $objetoAccesoDato->RetornarConsulta($consultaSQL);

        $consulta->execute();
        
        $cocheras = array();
        $rdo = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rdo as $key => $value) {
            array_push($cocheras, $value["id_cochera"]);
        }

        //var_dump($cocheras);

        return $cocheras;
    }

    public static function VehiculoEstacionado($patente){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM operaciones WHERE patente=:patente AND fecha_hora_salida IS NULL");
        $consulta->bindValue(':patente', $patente, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Operacion");
    }

    public function EstacionarVehiculo(){        
        
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //inserta enlazando parametros dela instancia
		$consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO operaciones (
            patente,
            color,
            marca,
            cochera,
            foto,
            id_empleado_ingreso,
            fecha_hora_ingreso
            )values(
            :patente,
            :color,
            :marca,
            :cochera,
            :foto,
            :id_empleado_ingreso,
            :fecha_hora_ingreso
            )");
        
		$consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':cochera', $this->cochera, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':id_empleado_ingreso', $this->id_empleado_ingreso, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_hora_ingreso', $this->fecha_hora_ingreso, PDO::PARAM_STR);
        
        return $consulta->execute();
    }
    
    public function SacarVehiculo(){

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE operaciones SET 
        id_empleado_salida=:id_empleado_salida,
        fecha_hora_salida=:fecha_hora_salida,
        tiempo=:tiempo,
        importe=:importe  
        WHERE patente=:patente AND fecha_hora_salida IS NULL");
        
        $consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':id_empleado_salida',$this->id_empleado_salida, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_hora_salida',$this->fecha_hora_salida, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo',$this->tiempo, PDO::PARAM_INT);
        $consulta->bindValue(':importe',$this->importe, PDO::PARAM_STR);
        
        return $consulta->execute();
    }

    public static function TraerOperacionesDeUnVehiculo($patente){
        
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM operaciones WHERE patente=:patente");
        $consulta->bindValue(':patente', $patente, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Operacion");
    }
    
    public static function TraerTodasLasOperaciones(){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM operaciones");
        $consulta->execute();			
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Operacion");
    }

    public static function TraerUnaOperacion($patente, $fecha_hora_ingreso){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM operaciones WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");
        $consulta->bindValue(':patente',$patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
		$OperacionBuscada = $consulta->fetchObject('Operacion');
        
        return $OperacionBuscada;
    }

    public static function NombreCochera($id_cochera){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT piso FROM cocheras WHERE id_cochera = :id_cochera");
		$consulta->bindValue(':id_cochera',$id_cochera, PDO::PARAM_STR);
        $consulta->execute();
        $rdo_consulta = $consulta->fetchAll(PDO::FETCH_ASSOC);      

        $rdo_consulta = str_split($rdo_consulta[0]["piso"]);

        return "Piso ".$rdo_consulta[0]." Sección ".$rdo_consulta[1];
    }

    public function CalcularHoras(){
        if ($this->fecha_hora_salida==null or $this->fecha_hora_ingreso == null) {
            return false;
        } else {
            $salida = new DateTime($this->fecha_hora_salida);
            $entrada = new DateTime($this->fecha_hora_ingreso);
            $diferencia = $salida->diff($entrada);
    
            //var_dump($diferencia);
            
            $d = $diferencia->days;
            $h = $diferencia->h;
            $m = $diferencia->i;
            $s = $diferencia->s;
            
            if ($s>0) {
                $m++;
            }
            if ($m>0) {
                $h++;
            }
            if ($d>0) {
                $h+=$d*24;
            }
            if ($d>0) {
                $h+=$d*24;
            }

            $this->tiempo = $h;
            return true;
        }        
    }

    public function CalcularImporte(){
        if($this->CalcularHoras()){
            $hs = $this->tiempo;
            $importe=0;
            
            if($hs<12){
                $importe = $hs * 10;
            }elseif ($hs>=24) {
                $importe = $hs/24 * 170;
            }else {
                $importe = $hs/12 * 90;
            }
            
            $this->importe=$importe;
            
            return true;
        } else {
            return false;
        }
    }
}
?>