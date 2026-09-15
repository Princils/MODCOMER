# MODCOMERCIAL

Aplicación PHP para consultar reportes comerciales sobre SQL Server.

## Requisitos

- Apache y PHP (la instalación local utiliza XAMPP y PHP 8.2).
- Extensión PDO SQLSRV y controlador ODBC para SQL Server.
- Acceso a las bases comerciales y al repositorio de usuarios/reportes.

## Configuración de una nueva instalación

1. Copiar `configuracion/configuracion.example.php` como `configuracion/configuracion.php` y ajustar sus valores locales.
2. Copiar `configuracion/Licencia.example.php` como `configuracion/Licencia.php` y configurar las claves privadas de la instalación.
3. Configurar la conexión desde el sistema y disponer de una licencia válida para esa instalación.
4. Abrir `index.php` desde Apache e iniciar sesión para seleccionar la empresa.

Los archivos de conexión, credenciales, licencia y reportes generados se excluyen de Git. Conserva un respaldo privado de estos archivos: clonar el repositorio no recupera la configuración local ni las bases de datos.

## Estructura

- `controlador/`: controladores PHP y JavaScript.
- `modelo/`: consultas y acceso a datos.
- `vista/`: vistas, estilos y bibliotecas del navegador.
- `configuracion/`: rutas y configuración de la instalación.
