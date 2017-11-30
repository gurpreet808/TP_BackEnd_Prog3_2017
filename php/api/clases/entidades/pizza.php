<?php
class pizza{
	public $id_pizza;
 	public $sabor;
	public $tipo;
  	public $cantidad;
	public $foto;

  	public function BorrarPizza(){
		  $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		  $consulta =$objetoAccesoDato->RetornarConsulta("DELETE from pizzas WHERE id_pizza=:id_pizza");
		  $consulta->bindValue(':id_pizza',$this->id_pizza, PDO::PARAM_INT);
		  $consulta->execute();
		  
		  return $consulta->rowCount();
	}

	public static function BorrarPizzaPorId($id){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE from pizzas WHERE id_pizza=:id_pizza");	
		$consulta->bindValue(':id_pizza',$id, PDO::PARAM_INT);		
		$consulta->execute();
		
		return $consulta->rowCount();
	}
	 
	public function ModificarPizza(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 //modifica enlazando parametros de la instancia
		 $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE pizzas SET 
		 sabor=:sabor,
		 cantidad=:cantidad,
		 tipo=:tipo,
		 foto=:foto
		 
		 WHERE id_pizza=:id_pizza");
		 
		 $consulta->bindValue(':id_pizza',$this->id_pizza, PDO::PARAM_INT);
		 $consulta->bindValue(':sabor',$this->sabor, PDO::PARAM_STR);
		 $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
		 $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
		 $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
		 
		 return $consulta->execute();
	}
	 
	public function InsertarPizza(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 //inserta enlazando parametros dela instancia
		 $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO pizzas (sabor,tipo,cantidad,foto)
		 values(:sabor,:tipo,:cantidad,:foto)");

		 $consulta->bindValue(':sabor',$this->sabor, PDO::PARAM_STR);
		 $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
		 $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
		 $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
		 
		 return $consulta->execute(); 
	}
	
	public function GuardarPizza(){
        if(empty(pizza::TraerUnaPizza($this->id_pizza))){
            $this->InsertarPizza();
            echo "pizza guardada";
        } else {            
            if ($this->ModificarPizza()) {
                echo "pizza modificada";
            } else {
                echo "No modifico pizza";
            }
        }
	}
	 
	public static function TraerTodasLasPizzas(){
		 $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		 $consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_pizza, sabor, tipo, cantidad, foto FROM pizzas");
		 $consulta->execute();
		 
		 return $consulta->fetchAll(PDO::FETCH_CLASS, "pizza");		
	}

	public static function TraerUnaPizza($id){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT id_pizza, sabor, tipo, cantidad, foto FROM pizzas where id_pizza = :id_pizza");
		$consulta->bindValue(':id_pizza',$id, PDO::PARAM_INT);
        $consulta->execute();
		$pizzaBuscado= $consulta->fetchObject('pizza');

		return $pizzaBuscado;
    }

	public function mostrarDatos(){
	  	return "Metodo mostar:".$this->id_pizza."  ".$this->sabor."  ".$this->tipo."  ".$this->cantidad."  ".$this->foto;
	}
}