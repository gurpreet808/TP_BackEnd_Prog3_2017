<?php

class Empleado{
    public $id = null;
    public $nombre = null;
    public $apellido = null;
    private $clave = null;
    public $mail = null;    
    public $turno = null;
    public $perfil = null;
    public $fecha_creacion = null;
    public $foto = null;// Agregar path de foto por defecto    

    public function ToJSON(){
        //Se puede poner en private los atributos que no quiero que salgan en el JSON
        return json_encode($this);
    }

    public function getClave(){
        return $this->clave;
    }

    public function setClave($clave){
        $this->clave = $clave;
    }

    public static function TraerTodosLosEmpleadosJSON(){
        $Empleados = self::TraerTodosLosEmpleados();

        $stringArrayEmpleados = "[";
        for ($i=0; $i < count($Empleados); $i++) { 
            $stringArrayEmpleados = $stringArrayEmpleados.$Empleados[$i]->toJSON().",";
        }
        $stringArrayEmpleados = substr($stringArrayEmpleados,0,-1) ."]";

        return $stringArrayEmpleados;
    }
    
    public function BorrarEmpleado(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM Empleados WHERE id=:id");
        $consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
        $consulta->execute();
        
        return $consulta->rowCount();
    }
    
    public function ModificarEmpleado(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //modifica enlazando parametros de la instncia
		$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE Empleados SET 
        nombre=:nombre, 
        apellido=:apellido,
        clave=:clave, 
        mail=:mail,
        turno=:turno,
        perfil=:perfil,
        fecha_creacion=:fecha_creacion,
        foto=:foto
        WHERE id=:id");

		$consulta->bindValue(':id',$this->id, PDO::PARAM_INT);
		$consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		$consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':turno', $this->turno, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);

		return $consulta->execute();
    }
    
    public function InsertarEmpleado(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //inserta enlazando parametros dela instancia
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO Empleados (
            nombre,
            apellido,
            clave,
            mail,
            turno,
            perfil,
            fecha_creacion,
            foto
            )values(
            :nombre,
            :apellido,
            :clave,
            :mail,
            :turno
            :perfil,
            :fecha_creacion,
            :foto
            )");
        
		$consulta->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
		$consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':turno', $this->turno, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_creacion', $this->fecha_creacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public function GuardarEmpleado(){
        if(empty(Empleado::TraerUnEmpleado($this->mail))){
            $this->InsertarEmpleado();
            echo "Empleado guardado";
        } else {
            $elEmpleado = Empleado::TraerUnEmpleado($this->mail);
            $this->id = $elEmpleado->id;
            
            //un For que traiga todos los datos si están en NULL que no debería ser
            if ($this->clave==null) {
                $this->clave = $elEmpleado->getClave();
            }
            
            if ($this->ModificarEmpleado()) {
                echo "Empleado modificado";
            } else {
                echo "No modifico Empleado";
            }
        }
    }
    
    public static function TraerTodosLosEmpleados(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, clave, mail, turno, perfil, fecha_creacion, foto FROM Empleados");
		$consulta->execute();			
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Empleado");
    }
    
    public static function TraerUnEmpleado($mail){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, clave, mail, turno, perfil, fecha_creacion, foto FROM Empleados where mail = :mail");
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);
        $consulta->execute();
		$EmpleadoBuscado= $consulta->fetchObject('Empleado');
        
        return $EmpleadoBuscado;
    }

    public static function TraerUnEmpleadoPorId($id){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, clave, mail, turno, perfil, fecha_creacion, foto FROM Empleados where id = :id");
		$consulta->bindValue(':id',$id, PDO::PARAM_INT);
        $consulta->execute();
		$EmpleadoBuscado= $consulta->fetchObject('Empleado');
        
        return $EmpleadoBuscado;
    }

    public static function BorrarEmpleadoPorParametro($mail){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM Empleados WHERE mail=:mail");	
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);		
		$consulta->execute();
        
        return $consulta->rowCount();
    }

    public static function VerificarClave($mail, $clave){
        if(empty(Empleado::TraerUnEmpleado($mail))){
            return "NOMAIL";
        } else {
            $unEmpleado = self::TraerUnEmpleado($mail);
            
            if ($unEmpleado->clave == $clave) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function TraerEmpleadoJSON($mail){
        if(empty(Empleado::TraerUnEmpleado($mail))){
            return "Mail no registrado";
        } else {
            $unEmpleado = self::TraerUnEmpleado($mail);
            return $unEmpleado->ToJSON();
        }
    }
}
?>