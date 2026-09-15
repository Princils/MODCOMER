<?php 

class UsuariosYPermisosModelo extends Conexion{

	static public function AgregarTblUsuariosYPermisosModelo(){

				//AGREGAMOS LA CONSULTA QUE BUSCA LOS USUARIOS JUNTO CON SUS NIVELES Y TIPOS
		$query = Conexion::ConnectRepositorio()->prepare("SELECT * from RcUsuariosTablero  order by CTIPOUSUARIO asc");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarCbxCompanyUsuariosYPermisosModelo(){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectWAdmin()->prepare('SELECT * from Empresas where CIDEMPRESA >1
					');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarCbxReportesUsuariosYPermisosModelo(){
		//AGREGAMOS LA CONSULTA
		$query =  Conexion::ConnectRepositorio()->prepare("SELECT *
					FROM RcReportes
					ORDER BY
					  CASE CTIPOREPORTE
					    WHEN 'Ventas Con Utilidad' THEN 1
					    WHEN 'Ventas Sin Utilidad' THEN 2
					    WHEN 'Remisiones con Utilidad' THEN 3
					    WHEN 'Remisiones sin Utilidad' THEN 4
					    WHEN 'Inventario' THEN 5
					    WHEN 'Entregas' THEN 6
					    WHEN 'Compras' THEN 7
					    WHEN 'Cobranza' THEN 8
					    WHEN 'Configuración' THEN 9
					    ELSE 10 -- En caso de que haya otros tipos de reportes que no estén en la lista
					  END;
		");

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarUsuariosDelTableroRepositorioModelo(){
		//EXTRAE LOS DATOS DE TODOS LOS AGENTES EXISTENTES Y LOS AGREGA DENTRO DE UN ARRAY
		$query = Conexion::ConnectRepositorio()->prepare("
			SELECT CLAVE AS 'CODIGO',NOMBRE as'NOMBRE' FROM CAC10000 
			WHERE CIDAUTOINCSQL > 1
			ORDER BY CLAVE");
		if($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}
	

	static public function BuscarTablerosDelTableroNuevoModelo(){
		//EXTRAE LOS DATOS DE TODOS LOS AGENTES EXISTENTES Y LOS AGREGA DENTRO DE UN ARRAY
		$query = Conexion::ConnectRepositorio()->prepare("SELECT CIDCODIGOUSUARIOTABLERO as 'CODIGO', CNOMBREUSUARIOTABLERO as'NOMBRE' from RcUsuariosTablero 
			WHERE CIDUSUARIOTABLERO >2
			order by CNOMBREUSUARIOTABLERO ");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarNuevosUsuariosTableroModelo($data){
		$query =  Conexion::ConnectRepositorio()->prepare('
			INSERT INTO RcUsuariosTablero (CIDCODIGOUSUARIOTABLERO, CNOMBREUSUARIOTABLERO,CTIPOUSUARIO, CNIVEL1, CNIVEL2, CNIVEL3, CNIVEL4, CNIVEL5, CNIVEL6,CCONFIGURACION, CACTIVO,CNIVEL7, CNIVEL8)
			VALUES (:clave, :name, 3, 0, 0, 0, 0, 0, 0, 0 ,1 , 0, 0)');
		$query->bindParam(":clave" , $data['CODIGO'], PDO::PARAM_STR);
		$query->bindParam(":name" , $data['NOMBRE'], PDO::PARAM_STR);
		if ($query->execute()) {
				return 1;
		}else{
			return "erorr";
		}
	}

	static public function VerificarExistenciaDeUsuarioIgualModelo($data) {
	    // Prepare the SQL query with a placeholder for the parameter
	    $query = Conexion::ConnectRepositorio()->prepare("SELECT COUNT(*) AS 'CANTIDAD' FROM RcUsuariosTablero WHERE CIDCODIGOUSUARIOTABLERO = :cidCodigoUsuarioTablero");

	    // Bind the parameter to the prepared statement
	    $query->bindParam(':cidCodigoUsuarioTablero', $data['0'], PDO::PARAM_STR);

	    // Execute the query
	    if ($query->execute()) {
	        // Fetch the data as an associative array
	        $result = $query->fetch(PDO::FETCH_ASSOC);

	        // Return the count value
	        return $result['CANTIDAD'];
	    } else {
	        // Return "error" in case of an error
	        return "error";
	    }
	}


	static public function ActualizarUnicoUsuarioTableroModelo($data) {
		try {
			$CODIGO = $data['0'] != 'null' ? ", CIDCODIGOUSUARIOTABLERO = '".$data['0']."'" : "";
			$USUARIO = $data['1'] != 'null' ? ", CNOMBREUSUARIOTABLERO = '".$data['1']."'" : "";
			$CONTRASENA = $data['2'] != 'null' ? ", CCONTRASENA = '".$data['2']."'" : "";
			$ddbsarray = $data['3'] != 'null' ? $data['3']: array();
			$lvlarray = $data['4'] != 'null' ? $data['4']: array();


			//*************************************************
			//ACTUALIZAR REPORTES, CONTRASEÑA  CODIGO
			//*************************************************
			$query = "
			    UPDATE RcUsuariosTablero
			    SET 
			        CACTIVO = '1'
			        ".$CODIGO." 
			        ".$USUARIO." 
			        ".$CONTRASENA." 
			    WHERE CIDCODIGOUSUARIOTABLERO = '".$data['0']."';
			";

		    $stmt =  Conexion::ConnectRepositorio()->prepare($query);

		    if (!$stmt->execute()) {
		        return false;
		    }


			//*************************************************
			// OBTENER EL ID DEL USUARIO A ACTUALIZAR
			//*************************************************

			$query2 = "
			    SELECT CIDUSUARIOTABLERO
			    FROM [RcUsuariosTablero]  
			    WHERE CIDCODIGOUSUARIOTABLERO = '".$data['0']."';
			";  
			$stmt2 = Conexion::ConnectRepositorio()->prepare($query2);  // Cambiamos $query por $query2

			if ($stmt2->execute()) {  // Usamos el array con el valor en el execute
			    $dataid = $stmt2->fetch(PDO::FETCH_ASSOC);
			    $id = $dataid['CIDUSUARIOTABLERO'];
			} else {
			    return false;
			}

			//*************************************************
		    //AGREGAR TODOS LOS REPORTES PARA EL USUARIO
			//*************************************************

		    $query3 = "
		    	INSERT INTO RcReportesUsuarios(CIDUSUARIOTABLERO, CIDREPORTE, CSTATUS)
				SELECT '$id' , CIDREPORTE  , '0'
				from RcReportes where CIDREPORTE not in ((select CIDREPORTE from RcReportesUsuarios where  CIDUSUARIOTABLERO =  '$id'))";

		    $stmt3 =  Conexion::ConnectRepositorio()->prepare($query3);
		    if (!$stmt3->execute()) {
		        return false;
		    }

		    //*************************************************
		    //BUSCAR TODOS LOS REPORTES
			//*************************************************

		    $query4 = "
			    SELECT CIDREPORTE from RcReportes
			";  

			$stmt4 = Conexion::ConnectRepositorio()->prepare($query4);  // Cambiamos $query por $query4

			if ($stmt4->execute()) {  // Usamos el array con el valor en el execute
			    $data4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
			} else {
			    return false;
			}


		    //*************************************************
		    //ACTUALIZAR LOS REPORTES DEL USUARIO A TRUE O FALSE
			//*************************************************
			foreach ($data4 as $row) {

				 $CIDREPORTE = $row['CIDREPORTE'];

			    // Verificamos si el valor de CIDREPORTE existe en el array
			    $existsInArray = in_array($CIDREPORTE, $lvlarray);
			    $status = ($existsInArray ? '1' : '0');

				$query5 = "
				    UPDATE RcReportesUsuarios
				    SET 
				        CSTATUS = '$status'
				    WHERE CIDUSUARIOTABLERO = '$id' AND CIDREPORTE = '$CIDREPORTE';
				";


			    $stmt5 =  Conexion::ConnectRepositorio()->prepare($query5);

			    if (!$stmt5->execute()) {
			        return false;
			    }
			}


			//*************************************************
		    //AGREGAR TODAS LAS EMPRESAS PARA EL USUARIO
			//*************************************************

		    $query3 = "
		    	INSERT INTO RCempresasusuario (CEMPRESA, CUSUARIO)
				SELECT CIDEMPRESA,  '$id'
				from Empresas where CIDEMPRESA > 1 
				and CIDEMPRESA not in ((select CEMPRESA from RCempresasusuario where CUSUARIO =  '$id'))";

		    $stmt3 =  Conexion::ConnectWAdmin()->prepare($query3);
		    if (!$stmt3->execute()) {
		        return false;
		    }

		    //*************************************************
		    //BUSCAR TODAS LAS EMPRESAS
			//*************************************************

		    $query4 = "
			    SELECT CIDEMPRESA from Empresas  where CIDEMPRESA > 1
			";  

			$stmt4 = Conexion::ConnectWAdmin()->prepare($query4);  // Cambiamos $query por $query4

			if ($stmt4->execute()) {  // Usamos el array con el valor en el execute
			    $data4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
			} else {
			    return false;
			}

		    //*************************************************
		    //ACTUALIZAR LAS EMPRESAS DEL USUARIO A TRUE O FALSE
			//*************************************************
			foreach ($data4 as $row) {
				 $cidEmpresa = $row['CIDEMPRESA'];
			    // Verificamos si el valor de CIDEMPRESA existe en el array
			    $existsInArray = in_array($cidEmpresa, $ddbsarray);
			    $status = ($existsInArray ? '1' : '0');
				$query5 = "
				    UPDATE RCempresasusuario
				    SET 
				        CSTATUS = '$status'
				    WHERE CUSUARIO = '$id' AND CEMPRESA = '$cidEmpresa';
				";
			    $stmt5 =  Conexion::ConnectWAdmin()->prepare($query5);
			    if (!$stmt5->execute()) {
			        return false;
			    }

			}
			return true;
		} catch (Exception $e) {
			return false;
		}
	}


	static public function BuscarLvlsReportesUsuarioModelo($code){
		try {
			$query2 = "
				SELECT CIDUSUARIOTABLERO
				FROM [RcUsuariosTablero]  
				WHERE CIDCODIGOUSUARIOTABLERO = '$code';
			";  
			
			//SACA EL ID SEGUN EL CODIGO QUE SE AGREGE
			//***************************************************
			$stmt2 = Conexion::ConnectRepositorio()->prepare($query2);  // Cambiamos $query por $query2

			if ($stmt2->execute()) {  // Usamos el array con el valor en el execute
				$dataid = $stmt2->fetch(PDO::FETCH_ASSOC);
				$id = $dataid['CIDUSUARIOTABLERO'];
			} else {
				return false;
			}

			//***************************************************
			//EXTRAE LOS REPORTES ACTIVOS DEL USUARIO 
			//***************************************************

			// AGREGAMOS LA CONSULTA QUE BUSCA LOS USUARIOS JUNTO CON SUS NIVELES Y TIPOS
			$query = Conexion::ConnectRepositorio()->prepare("
				SELECT  CIDREPORTE, CIDUSUARIOTABLERO from RcReportesUsuarios
				where  CIDUSUARIOTABLERO =  :code and CSTATUS = 1
			");

			$query->bindParam(':code', $id, PDO::PARAM_STR);
			$query->execute();

			// Obtener los niveles asignados en un arreglo asociativo
			$data = $query->fetchAll(PDO::FETCH_ASSOC);

			// Devolver los datos en formato JSON
			return $data;

		} catch (PDOException $e) {
			return "error: " . $e->getMessage();
		}
	}

	static public function BuscarLvlsCompanysUsuarioModelo($code){
	 	try {
			$query2 = "
				SELECT CIDUSUARIOTABLERO
				FROM [RcUsuariosTablero]  
				WHERE CIDCODIGOUSUARIOTABLERO = '$code';
			";  
				//***************************************************
			//SACA EL ID SEGUN EL CODIGO QUE SE AGREGE
			//***************************************************
			$stmt2 = Conexion::ConnectRepositorio()->prepare($query2);  // Cambiamos $query por $query2

			if ($stmt2->execute()) {  // Usamos el array con el valor en el execute
			$dataid = $stmt2->fetch(PDO::FETCH_ASSOC);
				$id = $dataid['CIDUSUARIOTABLERO'];
			} else {
				return false;
			}

			//***************************************************
			//EXTRAE LAS EMPRESAS ACTIVAS DEL USUARIO 
			//***************************************************

			// AGREGAMOS LA CONSULTA QUE BUSCA LOS USUARIOS JUNTO CON SUS NIVELES Y TIPOS
			$query = Conexion::ConnectWAdmin()->prepare("
			    SELECT  CEMPRESA, CUSUARIO from Empresas as e
				inner join RCempresasusuario as eu on eu.CEMPRESA = e.CIDEMPRESA
				where  CUSUARIO =  :code and CSTATUS = 1
			");

			$query->bindParam(':code', $id, PDO::PARAM_STR);
			$query->execute();

			// Obtener los niveles asignados en un arreglo asociativo
			$data = $query->fetchAll(PDO::FETCH_ASSOC);

			// Devolver los datos en formato JSON
			return $data;

	    } catch (PDOException $e) {
			return "error: " . $e->getMessage();
		}
	}

}