<?php 
unset($_SESSION['server']);

function ObtenerValorData_($contenido, $clave) {
    $lineas = explode(PHP_EOL, $contenido);
    foreach ($lineas as $linea) {
        if (strpos($linea, $clave) !== false) {
            return trim(str_replace($clave, '', $linea));
        }
    }
    return '';
}

//****************************************
//DATA PREDEFINIDA PARA EL FUNCIONAMIENTO
//****************************************
define('URL', 'http://localhost/SRComercial/');
define('hora', date("H:i:s"));
define('fecha', date("Y-m-d"));
define('dbnamerepositorio', 'RepositorioAdminPAQ');
define('dbnamereWAdmin', 'CompacWAdmin');
define('username', '');
define('password', '');


// Leer el contenido del archivo datos_conexion.txt
$archivo = __DIR__ . '/data_conection.txt';

// Comprobar si el archivo existe
if (!file_exists($archivo)) {
    // Si no existe, crearlo vacío
    file_put_contents($archivo, "");
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Extraer los valores del archivo
$server = ObtenerValorData_($contenido, 'Servidor:');
$database = ObtenerValorData_($contenido, 'Base de Datos:');
$user = ObtenerValorData_($contenido, 'Usuario:');
$password = ObtenerValorData_($contenido, 'Contraseña:');

//****************************************
//DATA PARA EL SERVIDOR DINAMICO
//****************************************
define('txtserver', $server);
define('txtdatabase', (isset($_SESSION['database']) ? $_SESSION['database'] : $database));
define('txtuser', $user);
define('txtpassword', $password);


$_SESSION['server'] = txtserver;       
$_SESSION['database'] = txtdatabase; 
$_SESSION['userbd'] = txtuser;       
$_SESSION['password'] = txtpassword;








?>