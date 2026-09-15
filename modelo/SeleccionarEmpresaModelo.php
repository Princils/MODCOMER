<?php 

class SeleccionarEmpresaModelo extends Conexion{


	static public function BusacarCompanysModelo(){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectWAdmin()->prepare('SELECT * from Empresas as e
					inner join RCempresasusuario as eu on eu.CEMPRESA = e.CIDEMPRESA
					where CSTATUS = 1 and CUSUARIO = '.$_SESSION['id_usuario']);
		
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}
	
}