<?php

class Licencia {
    private static $secret_key = "SRComercial_MasterKey_Secret2026!#Key";

    // Contraseña directa
    private static $password_master = "AdminLicencia2026!";

    // Genera la firma criptográfica HMAC para la fecha
    public static function GenerarSignature($fecha) {
        return hash_hmac('sha256', $fecha, self::$secret_key);
    }

    // Lee el archivo configuracion/licencia.key y verifica la firma y fecha
    public static function ValidarEstadoLicencia() {
        $archivo = __DIR__ . '/licencia.key';

        if (!file_exists($archivo)) {
            return [
                'valido' => false, 
                'mensaje' => 'El archivo de licencia no existe o fue removido.',
                'fecha' => 'N/A'
            ];
        }

        $contenido = file_get_contents($archivo);
        $lineas = explode(PHP_EOL, $contenido);

        $fechaGuardada = '';
        $tokenGuardado = '';

        foreach ($lineas as $linea) {
            if (strpos($linea, 'Fecha:') !== false) {
                $fechaGuardada = trim(str_replace('Fecha:', '', $linea));
            }
            if (strpos($linea, 'Token:') !== false) {
                $tokenGuardado = trim(str_replace('Token:', '', $linea));
            }
        }

        // 1. Validar si la firma coincide (Detectar edición manual)
        $tokenCalculado = self::GenerarSignature($fechaGuardada);
        if (!hash_equals($tokenCalculado, $tokenGuardado)) {
            return [
                'valido' => false, 
                'mensaje' => 'ATENCIÓN: La licencia del sistema ha sido alterada ilegítimamente.',
                'fecha' => $fechaGuardada
            ];
        }

        // 2. Validar fecha de vencimiento
        $fechaHoy = date('Y-m-d');
        if ($fechaHoy > $fechaGuardada) {
            return [
                'valido' => false, 
                'mensaje' => 'ATENCIÓN: La licencia del sistema ha vencido.',
                'fecha' => $fechaGuardada
            ];
        }

        return [
            'valido' => true, 
            'mensaje' => 'Licencia activa.',
            'fecha' => $fechaGuardada
        ];
    }

    // Procesa la actualización de la licencia
    public static function ActualizarLicencia($nuevaFecha, $passIngresada) {
        // Validación directa con trim para descartar espacios
        if (trim($passIngresada) !== self::$password_master) {
            return [
                'status' => false, 
                'mensaje' => 'La contraseña administrativa es incorrecta.'
            ];
        }

        if (empty($nuevaFecha)) {
            return [
                'status' => false, 
                'mensaje' => 'Debe ingresar una fecha de vencimiento válida.'
            ];
        }

        $nuevoToken = self::GenerarSignature($nuevaFecha);
        $contenido = "Fecha:" . $nuevaFecha . PHP_EOL . "Token:" . $nuevoToken;

        $archivo = __DIR__ . '/licencia.key';
        if (file_put_contents($archivo, $contenido) !== false) {
            return [
                'status' => true, 
                'mensaje' => 'Licencia renovada exitosamente.'
            ];
        } else {
            return [
                'status' => false, 
                'mensaje' => 'Error al escribir en el archivo de licencia.'
            ];
        }
    }
}
?>