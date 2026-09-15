<?php

//***************************
//		INICIO DE CLASE
//***************************
class ConceptosPorTiendaModelo extends Conexion{


	static public function AgregarTblConceptosPorTiendaModelo(){

		$query = Conexion::ConnecBdDinamico()->prepare("
			SELECT CT.CCODIGOCONCEPTOPORTIENDA AS 'CODIGO', CT.CNOMBRECONCEPTOPORTIENDA AS 'NOMBRE', CT.CSTATUS AS 'ESTATUS' 
			from RcConceptosPorTienda as CT
			where CT.CSTATUS = 1
			order by CNOMBRECONCEPTOPORTIENDA asc");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}


	static public function VerificarConceptosPorTiendaNoRepetidoModelo($data){
		//AGREGAMOS LA CONSULTA
		
		$query = Conexion::ConnecBdDinamico()->prepare("SELECT COUNT(*) AS CANTIDAD FROM RcConceptosPorTienda
   		WHERE CCODIGOCONCEPTOPORTIENDA = '".$data['0']."' AND CNOMBRECONCEPTOPORTIENDA = '".$data['1']."'");

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return $data['CANTIDAD'];
		}else{
			return "erorr";
		}
	}

	static public function NuevoConceptoPorTiendaModelo($data) {

	    $query = "

		    INSERT INTO RcConceptosPorTienda (CCODIGOCONCEPTOPORTIENDA, CNOMBRECONCEPTOPORTIENDA, CSTATUS)
		    VALUES ('".$data['0']."', '".$data['1']."', 1);
	    ";

	    $stmt = Conexion::ConnecBdDinamico()->prepare($query);

	    if ($stmt->execute()) {
	        return '1';
	    } else {
	        return '0';
	    }
	}

	static public function EliminarConceptoPorTiendaModelo($data) {
	    $query = "
	    	DELETE FROM RcConceptosPorTienda
			WHERE CCODIGOCONCEPTOPORTIENDA = '".$data['0']."' 
			AND CNOMBRECONCEPTOPORTIENDA = '".$data['1']."';
	    ";
	    $stmt =  Conexion::ConnecBdDinamico()->prepare($query);
	    if ($stmt->execute()) {
	        return '1';
	    } else {
	        return 'error';
	    }
	}

	//BUSCAMOS LOS CONCEPTOS QUE TENGA LA CLASE DEL CONCEPTO
	static public function MostrarChxConceptosPorTiendaModelo($data){
		$query = Conexion::ConnecBdDinamico()->prepare("
			SELECT C.CIDCONCEPTODOCUMENTO AS CODIGO 
			from RcClasificacionConceptosPorTienda as CCT
			INNER JOIN admConceptos AS C ON CCT.CIDCONCEPTODOCUMENTO = C.CIDCONCEPTODOCUMENTO
			INNER JOIN RcConceptosPorTienda AS CT ON CCT.CIDCONCEPTOPORTIENDA = CT.CIDCONCEPTOPORTIENDA
			WHERE CT.CCODIGOCONCEPTOPORTIENDA = '".$data['0']."' AND CT.CNOMBRECONCEPTOPORTIENDA= '".$data['1']."'
			");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarIdConceptosPorTiendaModelo($data) {
	    // Prepare the SQL query with placeholders for the parameters
	    $query = Conexion::ConnecBdDinamico()->prepare("
	        SELECT CIDCONCEPTOPORTIENDA 
	        FROM RcConceptosPorTienda 
	        WHERE CCODIGOCONCEPTOPORTIENDA = :codigoConcepto
	        AND CNOMBRECONCEPTOPORTIENDA = :nombreConcepto
	    ");

	    // Bind the parameters to the prepared statement
	    $query->bindParam(':codigoConcepto', $data['0'], PDO::PARAM_STR);
	    $query->bindParam(':nombreConcepto', $data['1'], PDO::PARAM_STR);

	    // Execute the query
	    if ($query->execute()) {
	        // Fetch the data as an associative array
	        $result = $query->fetch(PDO::FETCH_ASSOC);

	        // Check if the record exists and return the CIDCONCEPTOPORTIENDA value
	        if ($result) {
	            return $result['CIDCONCEPTOPORTIENDA'];
	        } else {
	            // Return "error" if no record is found
	            return "error";
	        }
	    } else {
	        // Return "error" in case of an error
	        return "error";
	    }
	}

	static public function EliminarCambiosConceptosPorTiendaModelo($data){
		$query = "
	    	DELETE FROM RcClasificacionConceptosPorTienda
			WHERE CIDCONCEPTOPORTIENDA = '".$data."';
	    ";

	    $stmt =  Conexion::ConnecBdDinamico()->prepare($query);

	    if ($stmt->execute()) {
	        return '1';
	    } else {
	        return 'error';
	    }
	}

	static public function GuardarCambiosConceptosportienda($data){
		$query = "
	    	INSERT INTO RcClasificacionConceptosPorTienda(CIDCONCEPTODOCUMENTO,CIDCONCEPTOPORTIENDA)
			values ('".$data['1']."','".$data['0']."')
	    ";

	    $stmt =  Conexion::ConnecBdDinamico()->prepare($query);

	    if ($stmt->execute()) {
	        return 'ok';
	    } else {
	        return 'error';
	    }

	}



//***************************
//		  FIN DE CLASE
//***************************
}





