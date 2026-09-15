<?php

//***************************
//		INICIO DE CLASE
//***************************
class ConceptosReutilizablesModelo extends Conexion{

	static public function CreadorDeConcicionBd($data, $index, $dbField,$tb) {
	    if (empty($data[$index])) {
	        return "";
	    } else {
	        $values = "'" . implode("','", $data[$index]) . "'";
	        return "AND $tb.$dbField in($values)";
	    }
	}

    // Función para recortar el texto si es demasiado largo
    static public function CortarText($text, $maxWidth) {

        if (strlen($text) > $maxWidth ) {
            $text = substr($text, 0, $maxWidth ) . '...';
        }
        return $text;
    }


	static public function AgregarCbxSucursalModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS SUCURSALES Y REGRESAN EL ID + EL NOMBRE (USADO PARA EL CBX_SUCURSAL)
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT DISTINCT D.CSUCURSAL, C.CIDDIRSUCU
			FROM admConceptos as C
			INNER join admDomicilios as D on C.CIDDIRSUCU=D.CIDDIRECCION
			ORDER BY D.CSUCURSAL ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function ConsultarConceptosTodos($id){
			//AGREGAMOS LA CONSULTA QUE BUSCA LOS CONCEPTOS SEGUN EL DOCUMENTO ELEGIDO Y REGRESA LOS DATOS PARA AGREGARLOS EN LA TABLA
		$query = Conexion::ConnecBdDinamico()->prepare("SELECT C.CIDCONCEPTODOCUMENTO, C.CNOMBRECONCEPTO, C.CIDDOCUMENTODE, D.CSUCURSAL, C.CIDDIRSUCU
			FROM admConceptos as C
			left JOIN admDomicilios as D ON C.CIDDIRSUCU = D.CIDDIRECCION
			WHERE  C.CCARTAPOR='0' AND CESTATUS = 1 and not (CUSAEXISTENCIA = 1 and CIDDOCUMENTODE = 4)
			ORDER BY CNOMBRECONCEPTO ASC");	
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function ContultarConceptosPorTiendaModelo($id){
		$query = Conexion::ConnecBdDinamico()->prepare("
			SELECT * from RcClasificacionConceptosPorTienda 
			WHERE CIDCONCEPTODOCUMENTO = '".$id."'
			");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}


	static public function BuscarTiposDocumentosModelo($tipo){
		//AGREGAMOS LA CONSULTA QUE EXTRAE LOS TIPOS DE DOCUMENTOS DEL 3 AL 6 QUE SON LOS NESESARIOS PARA LOS DOCUMENTOS CON UTILIDADES
		$documentos = implode(",", $tipo);
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT dm.CIDDOCUMENTODE, dm.CDESCRIPCION FROM admDocumentosModelo as DM WHERE DM.CIDDOCUMENTODE in ($documentos) order by dm.CDESCRIPCION asc ");

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}


	static public function BuscarConceptosSegunIdDocuemntosModelo($id){
			if ($id == 0) {
				$id = "'3','4','5','6'";
			} else {
  				// Convertir el valor $id en un array
				$idArray = explode(',', $id);
  				// Escapar cada elemento del array y rodearlo con comillas simples
				$idArray = array_map(function($value) {
					return "'" . addslashes($value) . "'";
				}, $idArray);
  				// Unir los elementos del array en una cadena separada por comas
				$id = implode(',', $idArray);
			}

				//AGREGAMOS LA CONSULTA QUE BUSCA LOS CONCEPTOS SEGUN EL DOCUMENTO ELEGIDO Y REGRESA LOS DATOS PARA AGREGARLOS EN LA TABLA
			$query = Conexion::ConnecBdDinamico()->prepare("SELECT C.CIDCONCEPTODOCUMENTO, C.CNOMBRECONCEPTO, C.CIDDOCUMENTODE, D.CSUCURSAL, C.CIDDIRSUCU
				FROM admConceptos as C
				LEFT JOIN admDomicilios as D ON C.CIDDIRSUCU = D.CIDDIRECCION
				WHERE CIDDOCUMENTODE IN ($id) AND C.CCARTAPOR='0' AND CESTATUS = 1 and not (CUSAEXISTENCIA = 1 and CIDDOCUMENTODE = 4)
				ORDER BY CNOMBRECONCEPTO ASC");	
			if ($query->execute()) {
				$data = $query->fetchAll(PDO::FETCH_ASSOC);
				return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarFiltroConceptosPorTiendaSegunIdConceptoModelo($id){
		$query = Conexion::ConnecBdDinamico()->prepare("
			SELECT * from RcClasificacionConceptosPorTienda 
			WHERE CIDCONCEPTODOCUMENTO = '".$id."'
			");
		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgrgearCbxConceptosPorTiendaModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS SUCURSALES Y REGRESAN EL ID + EL NOMBRE (USADO PARA EL CBX_SUCURSAL)
		$query =  Conexion::ConnecBdDinamico()->prepare('
			SELECT DISTINCT CIDCONCEPTOPORTIENDA,CNOMBRECONCEPTOPORTIENDA 
			FROM RcConceptosPorTienda 
			WHERE CSTATUS = 1
			order by CNOMBRECONCEPTOPORTIENDA asc;
			');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarFiltroAutocompletadoProductoInputModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LOS CLIENTES Y REGRESAN EL ID + EL NOMBRE (USADO PARA EL cbx_client)
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT CIDPRODUCTO, CCODIGOPRODUCTO,CNOMBREPRODUCTO 
			FROM admProductos 
			WHERE CIDPRODUCTO > 0 
			order BY CNOMBREPRODUCTO asc");

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return json_encode($data);
		}else{
			return "erorr";
		}
	}
	
	static public function BuscarFiltroAutocompletadoClienteInputModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LOS CLIENTES Y REGRESAN EL ID + EL NOMBRE (USADO PARA EL cbx_client)
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT CCODIGOCLIENTE, CIDCLIENTEPROVEEDOR, CRAZONSOCIAL 
			FROM admClientes 
			where CIDCLIENTEPROVEEDOR > 0 
			ORDER BY CRAZONSOCIAL ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return json_encode($data);
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxLineaGeneralModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 25 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxTypoClienteModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(8) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClienteModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(10) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxZonaModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(9) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxClasificacion5ClienteModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(11) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxClasificacion6ClienteModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(12) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClasificacion2Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 2 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClasificacion3Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 3 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClasificacion4Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 4 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClasificacion5Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 5 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxAgenteClasificacion6Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 6 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	

	static public function AgregarCbxAgente1Modelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 1 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}
	

	static public function AgregarCbxClasificacion1ClienteModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION in(7) order by CIDCLASIFICACION asc');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxLineaDetalladaModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 26 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	
	static public function AgregarCbxIndicadorComisionModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 28 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}


	static public function AgregarCbxTipoClasificacionModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 27 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxRotacionModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 29 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function AgregarCbxRevisionDiariaModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LAS CLASIFICACIONES Y REGRESAN EL ID + EL VALORCLASIFICACION
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT * from admClasificacionesValores where CIDCLASIFICACION = 30 order by CVALORCLASIFICACION ASC');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		}else{
			return "erorr";
		}
	}

	static public function BuscarFiltroAutocompletadoAgenteInputModelo(){
		//AGREGAMOS LA CONSULTA QUE BUSCA LOS CLIENTES Y REGRESAN EL ID + EL NOMBRE (USADO PARA EL cbx_client)
		$query =  Conexion::ConnecBdDinamico()->prepare('SELECT CIDAGENTE, CCODIGOAGENTE, CNOMBREAGENTE from admAgentes where CTIPOAGENTE = 2 order by CNOMBREAGENTE');

		if ($query->execute()) {
			$data = $query->fetchAll(PDO::FETCH_ASSOC);
			return json_encode($data);
		}else{
			return "erorr";
		}
	}



	static public function BuscarMinimoAgenteAlphanumericoModelo(){
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT MIN(c.CCODIGOAGENTE) AS 'minimo'  
			FROM admAgentes as c ");

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return ($data['minimo']);
		}else{
			return "erorr";
		}
	}

	static public function BuscarMaximoAgenteAlphanumericoModelo(){
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT MAX(c.CCODIGOAGENTE) AS 'maximo'
		FROM admAgentes as c ");

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return ($data['maximo']);
		}else{
			return "erorr";
		}
	}



	static public function BuscarMinimoProductoAlphanumericoModelo(){
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT min(CCODIGOPRODUCTO) as minimo
			from admProductos 
			where CIDPRODUCTO >0");

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return ($data['minimo']);
		}else{
			return "erorr";
		}
	}

	static public function BuscarMaximoProductoAlphanumericoModelo(){
		$query =  Conexion::ConnecBdDinamico()->prepare("SELECT Max(CCODIGOPRODUCTO) as maximo
			from admProductos 
			where CIDPRODUCTO >0");

		if ($query->execute()) {
			$data = $query->fetch(PDO::FETCH_ASSOC);
			return ($data['maximo']);
		}else{
			return "erorr";
		}
	}

	
	



//***************************
//		  FIN DE CLASE
//***************************
}

