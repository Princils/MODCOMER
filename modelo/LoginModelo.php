<?php 

class LoginModelo extends Conexion{

	//FUNCION QUE VERIFICA UNICACMENTE EL USUARIO O LA CLAVE Y RETORNA SU ID PARA UNA FUTURA VERIFICACION
	static public function VerificarUsuarioModelo($datacontroller){
			//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare("SELECT CIDUSUARIOTABLERO AS 'IDUSUARIO' FROM RcUsuariosTablero WHERE CNOMBREUSUARIOTABLERO = :user");
		$query->bindParam(":user" , $datacontroller['0'], PDO::PARAM_STR);

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			if (isset($data["IDUSUARIO"])) {
				return $data["IDUSUARIO"];
			}else{
				return 0;
			}
		}else{
			return "erorr";
		}
	}

	static public function VerificarPasswordModelo($datacontroller){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare('SELECT COUNT(*)as CANTIDAD FROM RcUsuariosTablero WHERE CIDUSUARIOTABLERO = :id AND CCONTRASENA = :password');
		$query->bindParam(":id" , $datacontroller['2'], PDO::PARAM_STR);
		$query->bindParam(":password" , $datacontroller['1'], PDO::PARAM_STR);

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return $data['CANTIDAD'];
		}else{
			return "erorr";
		}
	}

	static public function CredencialesDeUsuarioModelo($datacontroller){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare('SELECT * FROM RcUsuariosTablero WHERE  CIDUSUARIOTABLERO = :id');
		$query->bindParam(":id" , $datacontroller['2'], PDO::PARAM_STR);

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function ConultarReportesModelo(){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare('SELECT CIDREPORTE FROM RcReportes WHERE CSTATUS = 1');
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function ConsultarReportesDeUsuarioModelo($datacontroller){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare('SELECT CIDREPORTE FROM RcReportesUsuarios WHERE CIDUSUARIOTABLERO = :id and CSTATUS = 1');
		$query->bindParam(":id" , $datacontroller['2'], PDO::PARAM_STR);

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}


	
}