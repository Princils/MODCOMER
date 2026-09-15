<?php

class Conexion{
	static public function ConnectRepositorio(){
		try {
			$dns = 'sqlsrv:Server='.txtserver.';Database='.dbnamerepositorio ;
			$conn = new PDO($dns, txtuser , txtpassword );
			return $conn;
		} catch (PDOException $e) {
			return false;
		}
		
	}

	static public function ConnectWAdmin(){
		try {
			$dns = 'sqlsrv:Server='.txtserver.';Database='.dbnamereWAdmin ;
			$conn = new PDO($dns, txtuser , txtpassword );
			return $conn;
		} catch (PDOException $e) {
			return false;
		}
	}

	static public function ConnectarDinamico($data){
		try {
			$dns = 'sqlsrv:Server='.$data['0'].';Database='.$data['1'] ;
			$conn = new PDO($dns, $data['2'] , $data['3'] );
			return $conn;
		} catch (PDOException $e) {
			return false;
		}
	}

	static public function ConnecBdDinamico(){
		try {
			$dns = 'sqlsrv:Server='.txtserver.';Database='.txtdatabase;
			$conn = new PDO($dns, txtuser , txtpassword );
			return $conn;
		} catch (PDOException $e) {
			return false;
		}
	}
	
}

?>