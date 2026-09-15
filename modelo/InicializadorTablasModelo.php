<?php

//***************************
//		INICIO DE CLASE
//***************************
class InicializadorTablasModelo extends Conexion{

	static public function VerificarTablasPrincipalesModelo($data){
		$query = "
			IF OBJECT_ID('RepositorioAdminPAQ.dbo.RcUsuariosTablero', 'U') IS NULL
			BEGIN
			    USE RepositorioAdminPAQ
			    CREATE TABLE RcUsuariosTablero 
			    (
			        CIDUSUARIOTABLERO INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
			        CIDCODIGOUSUARIOTABLERO varchar(50) NOT NULL, 
			        CNOMBREUSUARIOTABLERO varchar(200) NOT NULL, 
			        CCONTRASENA VARCHAR(200) DEFAULT CONVERT(VARCHAR(200), HASHBYTES('SHA1', '123456'), 2), 
			        CTIPOUSUARIO INT NOT NULL DEFAULT 3
			    );

			    INSERT INTO RcUsuariosTablero (
				  CIDCODIGOUSUARIOTABLERO, CNOMBREUSUARIOTABLERO, CTIPOUSUARIO
				)
				VALUES ( 'ADMIN',  'ADMINISTRADOR',  2 );

				 INSERT INTO RcUsuariosTablero (
				  CIDCODIGOUSUARIOTABLERO, CNOMBREUSUARIOTABLERO, CTIPOUSUARIO
				)
				VALUES ( 'SUPERV',  'SUPERVISOR',  1 );
			END

			IF OBJECT_ID('RepositorioAdminPAQ.dbo.RcReportes', 'U') IS NULL
			BEGIN
			    USE RepositorioAdminPAQ
			    CREATE TABLE RcReportes(
					CIDREPORTE INT IDENTITY(1,1) PRIMARY KEY,
					CNOMBRE VARCHAR(100) NOT NULL,
					CTIPOREPORTE VARCHAR(100) NOT NULL DEFAULT 'OTRO',
					CSTATUS  BIT NOT NULL DEFAULT 1
				)

				INSERT INTO RcReportes(CNOMBRE,CTIPOREPORTE)
				VALUES
					('Utilidad por Documentos','Ventas Con Utilidad'),
					('Utilidad por Productos','Ventas Con Utilidad'),
					('Utilidad por Agentes','Ventas Con Utilidad'),
					('Conexión a Base de Datos','Configuración'),
					('Usuarios y Permisos','Configuración'),
					('Conceptos por Tienda','Configuración')
			END

			INSERT INTO RcReportes (CNOMBRE, CTIPOREPORTE,CSTATUS)
				SELECT 'Utilidad por Documentos','Ventas Con Utilidad',1
			WHERE NOT EXISTS (
			    SELECT 1
			    FROM RcReportes
			    WHERE CNOMBRE = 'Utilidad por Documentos'
			    AND CTIPOREPORTE = 'Ventas Con Utilidad'
			)

			Delete from RcReportes where CNOMBRE ='Utilidad por Agente' and CTIPOREPORTE = 'Ventas Con Utilidad'

			INSERT INTO RcReportes (CNOMBRE, CTIPOREPORTE,CSTATUS)
				SELECT 'Utilidad por Productos','Ventas Con Utilidad',1
			WHERE NOT EXISTS (
			    SELECT 1
			    FROM RcReportes
			    WHERE CNOMBRE = 'Utilidad por Productos'
			    AND CTIPOREPORTE = 'Ventas Con Utilidad'
			)

			INSERT INTO RcReportes (CNOMBRE, CTIPOREPORTE,CSTATUS)
				SELECT 'Utilidad por Agentes','Ventas Con Utilidad',1
			WHERE NOT EXISTS (
			    SELECT 1
			    FROM RcReportes
			    WHERE CNOMBRE = 'Utilidad por Agentes'
			    AND CTIPOREPORTE = 'Ventas Con Utilidad'
			)

			INSERT INTO RcReportes (CNOMBRE, CTIPOREPORTE,CSTATUS)
				SELECT 'Utilidad por Clasificación','Ventas Con Utilidad',1
			WHERE NOT EXISTS (
			    SELECT 1
			    FROM RcReportes
			    WHERE CNOMBRE = 'Utilidad por Clasificación'
			    AND CTIPOREPORTE = 'Ventas Con Utilidad'
			)

			IF OBJECT_ID('RepositorioAdminPAQ.dbo.RcReportesUsuarios', 'U') IS NULL
			BEGIN
			    USE RepositorioAdminPAQ
			    CREATE TABLE RcReportesUsuarios(
					CIDREPORTESUSUARIOS INT IDENTITY(1,1) PRIMARY KEY,
					CIDUSUARIOTABLERO INT NOT NULL,
					CIDREPORTE INT NOT NULL,
					CSTATUS BIT NOT NULL DEFAULT 1
				);



			END

				-- Verificar y agregar registros individualmente
				IF NOT EXISTS (SELECT 1 FROM RcReportes WHERE CNOMBRE = 'Conexión a Base de Datos')
				BEGIN
				    INSERT INTO RcReportes(CNOMBRE, CTIPOREPORTE)
				    VALUES ('Conexión a Base de Datos', 'Configuración')
				END

				IF NOT EXISTS (SELECT 1 FROM RcReportes WHERE CNOMBRE = 'Usuarios y Permisos')
				BEGIN
				    INSERT INTO RcReportes(CNOMBRE, CTIPOREPORTE)
				    VALUES ('Usuarios y Permisos', 'Configuración')
				END

				IF NOT EXISTS (SELECT 1 FROM RcReportes WHERE CNOMBRE = 'Conceptos por Tienda')
				BEGIN
				    INSERT INTO RcReportes(CNOMBRE, CTIPOREPORTE)
				    VALUES ('Conceptos por Tienda', 'Configuración')
				END

				INSERT INTO RcReportesUsuarios(CIDUSUARIOTABLERO, CIDREPORTE, CSTATUS)
				SELECT (SELECT CIDUSUARIOTABLERO FROM RcUsuariosTablero WHERE CIDCODIGOUSUARIOTABLERO = 'SUPERV') , CIDREPORTE  , '1'
				from RcReportes where CIDREPORTE not in ((select CIDREPORTE from RcReportesUsuarios where  CIDUSUARIOTABLERO =  
				(SELECT CIDUSUARIOTABLERO FROM RcUsuariosTablero WHERE CIDCODIGOUSUARIOTABLERO = 'SUPERV')))

				INSERT INTO RcReportesUsuarios(CIDUSUARIOTABLERO, CIDREPORTE, CSTATUS)
				SELECT (SELECT CIDUSUARIOTABLERO FROM RcUsuariosTablero WHERE CIDCODIGOUSUARIOTABLERO = 'ADMIN') , CIDREPORTE  , '1'
				from RcReportes where CIDREPORTE not in ((select CIDREPORTE from RcReportesUsuarios where  CIDUSUARIOTABLERO =  
				(SELECT CIDUSUARIOTABLERO FROM RcUsuariosTablero WHERE CIDCODIGOUSUARIOTABLERO = 'ADMIN')))

							
				UPDATE RcReportesUsuarios
				SET CSTATUS = 1
				WHERE CIDUSUARIOTABLERO = (select CIDUSUARIOTABLERO from RcUsuariosTablero where CIDCODIGOUSUARIOTABLERO = 'SUPERV');

				UPDATE RcReportesUsuarios
				SET CSTATUS = 1
				WHERE CIDUSUARIOTABLERO = (select CIDUSUARIOTABLERO from RcUsuariosTablero where CIDCODIGOUSUARIOTABLERO = 'ADMIN');
							";

			

		$query3 = " 
			IF OBJECT_ID('CompacWAdmin.dbo.RCempresasusuario', 'U') IS NULL
			BEGIN
			    CREATE TABLE RCempresasusuario(
			        CIDEMPRESAUSUARIO int IDENTITY(1,1) PRIMARY KEY,
			        CEMPRESA INT NOT NULL,
			        CUSUARIO INT NOT NULL,
			        CSTATUS INT NOT NULL DEFAULT 1
			    )

				-- Suponiendo que Empresas tiene una columna llamada CIDEMPRESA
				INSERT INTO RCempresasusuario(CEMPRESA, CUSUARIO)
				SELECT CIDEMPRESA, 2
				FROM Empresas
				WHERE CIDEMPRESA > 1

				INSERT INTO RCempresasusuario(CEMPRESA, CUSUARIO)
				SELECT CIDEMPRESA, 1
				FROM Empresas
				WHERE CIDEMPRESA > 1
			END

			INSERT INTO RCempresasusuario (CEMPRESA, CUSUARIO, CSTATUS)
			SELECT e.CIDEMPRESA, u.CUSUARIO, 1 AS CSTATUS
			FROM (SELECT DISTINCT CUSUARIO FROM RCempresasusuario) AS u
			CROSS JOIN (SELECT CIDEMPRESA FROM Empresas WHERE CIDEMPRESA > 1) AS e
			WHERE NOT EXISTS (
			    SELECT 1
			    FROM RCempresasusuario r
			    WHERE r.CUSUARIO = u.CUSUARIO AND r.CEMPRESA = e.CIDEMPRESA
			);
		 ";

		$stmt = Conexion::ConnectRepositorio()->prepare($query);
		$stmt3 = Conexion::ConnectWAdmin()->prepare($query3);
				if ($stmt->execute()){
						if ($stmt3->execute()){
							return true;
						} else {
							return false;
						}
					
				} else {
					return false;
				}

	}

	static public function VerificarTablasBdDinamicaModelo($data){
	    $query1 = "
	        IF OBJECT_ID('".$data['1'].".dbo.RcConceptosPorTienda', 'U') IS NULL
	        BEGIN
	            USE ".$data['1']."
	            CREATE TABLE RcConceptosPorTienda 
	            (
	                CIDCONCEPTOPORTIENDA INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
	                CCODIGOCONCEPTOPORTIENDA varchar(50) NOT NULL, 
	                CNOMBRECONCEPTOPORTIENDA varchar(200) NOT NULL, 
	                CSTATUS bit not null DEFAULT 1
	            );
	        END
	    ";

	    $query2 = "
	        IF OBJECT_ID('".$data['1'].".dbo.RcClasificacionConceptosPorTienda', 'U') IS NULL
	        BEGIN
	            USE ".$data['1']."
	            CREATE TABLE RcClasificacionConceptosPorTienda 
	            (
	                CIDCLASIFICACIONCONCEPTOSPORTIENDA INT IDENTITY(1,1) PRIMARY KEY NOT NULL,
	                CIDCONCEPTODOCUMENTO INT NOT NULL,
	                FOREIGN KEY(CIDCONCEPTODOCUMENTO) REFERENCES admConceptos(CIDCONCEPTODOCUMENTO),
	                CIDCONCEPTOPORTIENDA INT NOT NULL,
	                FOREIGN KEY(CIDCONCEPTOPORTIENDA) REFERENCES RcConceptosPorTienda(CIDCONCEPTOPORTIENDA)
	            );   
	        END
	    ";

	    try {
	        $stmt1 = Conexion::ConnectarDinamico($data)->prepare($query1);
	        $stmt2 = Conexion::ConnectarDinamico($data)->prepare($query2);

	        $stmt1->execute();
	        $stmt2->execute();

	        return true;
	    } catch (Exception $e) {
	        // Manejar la excepción o registrar el error
	        return false;
	    }
	}

//***************************
//		  FIN DE CLASE
//***************************
}



