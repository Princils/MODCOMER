# MODCOMERCIAL Beta para hosting

Incluye acceso independiente, selector de empresas y Utilidad por Documentos con CSV. Las consultas las ejecuta el conector contra SQL Server local; el hosting recibe los resultados por HTTPS. No abre puertos de SQL Server hacia Internet.

## Requisitos

PHP 8.2 o superior, sesiones y JSON habilitados, sistema de archivos local con flock y escritura en private/data, certificado HTTPS. No necesita MySQL ni controlador SQL Server en el hosting. Es una beta de prueba con almacenamiento en archivos y un administrador, no un servicio multiusuario de producción.

## Subir al hosting

1. Descomprime MODCOMERCIAL-Beta-Hosting.zip en tu computadora.
2. Opción recomendada: crea un subdominio y establece su raíz pública en la carpeta public del paquete. Deja private fuera de esa raíz.
3. Si usas public_html: copia el contenido de public a public_html/modcomercial-beta y coloca private fuera de public_html, por ejemplo /home/usuario/modcomercial-private. En public/load.php cambia la expresión asignada a $privateDirectory por la ruta absoluta de esa carpeta (entre comillas). También puedes configurar la variable de entorno MODCOM_BETA_PRIVATE.
4. Dale al proceso PHP permiso de escritura sobre private/data. No uses permisos 777. Conserva config.local.php y los archivos de datos fuera de la zona pública.
5. Abre https://TU-DOMINIO/modcomercial-beta/setup.php (o /setup.php en el subdominio). La clave de instalación está en private/config.local.php, campo setup_key. Cada ZIP genera su propia clave.
6. Crea tu usuario, contraseña de mínimo 12 caracteres y primera empresa. Las cuentas son propias de esta beta; no son las cuentas de la aplicación local.
7. Guarda Dirección de la plataforma, Empresa y Token que aparecen al terminar. El token se muestra una sola vez. Puedes regenerarlo desde la tarjeta de empresa, lo que invalida el anterior.

El ZIP exige HTTPS. Si un proxy termina TLS, configura únicamente las IP reales de ese proxy en trusted_proxies; no aceptes cualquier IP. Si Apache usa PHP-FPM y elimina Authorization, habilita el reenvío del encabezado Authorization en el hosting.

## Vincular el conector

En el servidor Windows que tiene acceso a la base, instala MODCOMERCIAL-Conector-Setup.exe y abre MODCOMERCIAL Conector > Configurar como administrador. Captura endpoint HTTPS, identificador de empresa y token generados por esta beta, junto con instancia SQL, base y credenciales locales. El hosting nunca necesita la contraseña SQL.

La versión instalada permite una empresa/base por servicio. Para esta prueba elige una empresa; para probar otras cambia la configuración, o usa otro servidor con su conector. No instales dos copias esperando servicios independientes en la misma PC. Al apuntar el servicio actual al hosting dejará de atender conector-demo; la aplicación PHP local normal sigue funcionando. Conserva la configuración previa si deseas volver a la demo.

Mantén el archivo report-public.pem del instalador: corresponde al reporte firmado incluido. No regeneres la clave de firma para este paquete. El conector obtiene las consultas firmadas automáticamente, no ejecuta SQL arbitrario enviado desde el navegador.

Selecciona la empresa en el hosting, indica fechas y pulsa Calcular reporte. El conector consulta cada 5 segundos y entrega el resultado. No necesitas mantener abierto el monitor ni el navegador del servidor; sí debe estar encendido el servidor que ejecuta el servicio, conectado a Internet y con SQL disponible. El estado En línea comprueba comunicación, no garantiza que las credenciales SQL sean correctas.

## Alcance de esta prueba

- Reporte de todos los clientes y conceptos activos incluidos en el paquete SQL; sin filtros avanzados ni pesos.
- Máximo 366 días, 20,000 filas y 3 solicitudes pendientes por empresa. Resultados conservados hasta 24 horas; la limpieza ocurre al atender otra solicitud.
- Los resultados sí viajan al hosting y se almacenan temporalmente. La base completa permanece local.
- No incluye envíos programados de correo, recuperación de contraseña ni administración de múltiples usuarios.
- El ZIP está limpio: no incluye usuarios, resultados ni credenciales SQL de la prueba local.
- Aún debes verificar la conexión real después de subirlo a tu hosting. La prueba local no demuestra disponibilidad ni configuración de ese proveedor.

## Desarrollo y verificación

Rama beta/conector-hosting. La aplicación principal queda fuera de esta carpeta.

Ejecuta php tests/domain.php para probar aislamiento, fechas, permisos, entregas idempotentes y límites. tests/http.ps1 prueba la instalación y acceso desde Apache local; requiere config.local.php local con allow_local_http=true y almacenamiento nuevo. Crea solo datos de prueba; no lo ejecutes contra una instalación existente.

Para crear un ZIP nuevo ejecuta powershell -File beta-hosting/package.ps1 desde la raíz del repositorio. Guarda cada ZIP con cuidado: contiene su clave inicial de instalación. Para actualizar una instalación ya configurada no reemplaces config.local.php ni private/data.
