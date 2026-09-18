# MODCOMERCIAL Conector — demostración 0.1.0

Servicio Windows en C#/.NET 10 LTS, autocontenido para Windows x64. No necesita PHP ni un SDK en el equipo del cliente. La demostración web sí requiere el MODCOMERCIAL PHP existente.

## Instalación

Ejecutar `dist/MODCOMERCIAL-Conector-Setup.exe` como administrador. Instala en `C:\Program Files\MODCOMERCIAL Connector`, crea el servicio `MODCOMERCIALConnector` con identidad LocalService y arranque automático retrasado, recuperación ante fallos y un acceso directo al monitor de estado.

En una máquina nueva, abrir **Estado del conector > Configurar**, completar plataforma, empresa, token, instancia SQL y base. Los datos no vienen incluidos en el instalador. La configuración está protegida con DPAPI de Windows y ACL en `C:\ProgramData\MODCOMERCIAL\Connector`. Autenticación Windows usa la identidad del servicio, no la del usuario del monitor.

El monitor consulta únicamente `127.0.0.1:17643/status`. No se abre un puerto a la red. Cerrar el monitor no detiene el servicio. No existe un endpoint HTTP para ejecutar SQL o editar credenciales.

## Demostración PHP

`php-demo/` contiene una copia de la página instalada en `MODCOMCER/conector-demo/`. Entrar a MODCOMERCIAL, seleccionar la empresa vinculada y abrir `/conector-demo/`. Requiere permiso de Utilidad por Documentos.

Flujo: PHP encola una solicitud → servicio recoge por conexión saliente → verifica firma del paquete → ejecuta consulta con parámetros → entrega JSON → PHP muestra tabla y totales. Consultas de 5 segundos; límite de 366 días y 20,000 filas. El ejemplo usa todos los clientes del catálogo de la base vinculada y conceptos activos; no migra aún las pantallas existentes al conector.

La cola PHP y su token están fuera del directorio público, en `C:\ProgramData\MODCOMERCIAL\ConnectorDemo`. Para alojar PHP en otro servidor hay que adaptar esa ruta de almacenamiento, configurar HTTPS, credenciales por cliente y permisos del usuario que ejecuta PHP. El ejemplo atiende una sola empresa vinculada y no es todavía una plataforma multiempresa de producción.

El resultado pendiente se guarda cifrado para reintentar la entrega tras cortes de red. La recepción es idempotente. Los trabajos reclamados pueden recuperarse después de 180 segundos. Resultados PHP se retienen hasta 24 horas y se limpian al procesar solicitudes.

## Reportes y actualizaciones

`report.json` es la definición legible; incluye SQL y versión. El motor 1 admite reportes de lectura con parámetros `@inicio` y `@fin`. El paquete se firma RSA-PSS/SHA-256 y PHP publica el sobre firmado. El conector verifica firma, identificador, versión de motor y evita volver a una versión anterior. Cada reporte debe habilitarse en `AllowedReports` de la configuración; nuevos tipos de parámetros/capacidades requieren ampliar el motor.

La clave privada NO se distribuye. En esta PC se conserva fuera del proyecto, en `%LOCALAPPDATA%\MODCOMERCIALBuild\report-private.pem`. Respaldarla de forma segura. El instalador incluye solamente la clave pública. Para publicar otra versión, aumentar `version`, firmar y copiar el archivo `.signed.json` al servidor PHP. Para nuevos reportes también hay que agregar su pantalla y registro en PHP.

Construir con SDK .NET 10 e Inno Setup 6: `powershell -File build.ps1 -PrivateKeyPath C:\ruta-segura\report-private.pem`. El directorio publish es autocontenido; el cliente no necesita instalar .NET. El instalador de ejemplo no tiene firma Authenticode; debe firmarse con un certificado del distribuidor antes de distribución comercial.

## Límites de este ejemplo

- Solo reporte Utilidad por Documentos; no envía correos ni actualiza márgenes.
- La base no se copia ni se modifica. Se usa la conexión existente durante la prueba local. Para distribución crear un usuario SQL con permisos mínimos de lectura: una firma no reemplaza los permisos del motor SQL.
- HTTP se acepta únicamente en loopback para esta prueba. Con plataforma remota exige HTTPS y no sigue redirecciones.
- La prueba local permite confiar en el certificado de la instancia SQL existente, conservando cifrado. En clientes, configurar un certificado válido y desactivar esa excepción.
- Cola de archivos para una demostración, sin cola distribuida ni persistencia transaccional de producción. No incluir secretos en archivos del hosting público.
- Si el servidor local se apaga, no se consultan datos nuevos. No existe aún envío programado de correos.
- Desinstalar desde Aplicaciones de Windows elimina servicio/programa; conserva configuración y resultados protegidos para no perderlos por accidente.
