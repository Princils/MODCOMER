<?php 

class ModeloPrincipal extends Conexion{

	//FUNCION QUE VERIFICA UNICACMENTE EL USUARIO O LA CLAVE Y RETORNA SU ID PARA UNA FUTURA VERIFICACION
	static public function BusacrReporteModelo($reporte,$tipo){
			//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare("SELECT CIDREPORTE from RcReportes where CNOMBRE = :reporte and CTIPOREPORTE = :tipo");
		$query->bindParam(":reporte" , $reporte, PDO::PARAM_STR);
		$query->bindParam(":tipo" , $tipo, PDO::PARAM_STR);

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			if (isset($data["CIDREPORTE"])) {
				return $data["CIDREPORTE"];
			}else{
				return 0;
			}
		}else{
			return "erorr";
		}
	}

	
	static public function ValidarConexionTxtModelo(){
		if (txtserver != '') {
			$query =  Conexion::ConnectRepositorio();
		}else{
			$query =false;
		}

		return $query;
	}


	
}