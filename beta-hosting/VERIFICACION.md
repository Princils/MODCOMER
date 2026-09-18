# Verificacion local — 18 de septiembre de 2026

- PHP 8.2.12: sintaxis de todos los archivos correcta; JavaScript validado con node --check.
- Pruebas de dominio: aislamiento de empresas y propietarios, fechas inválidas, token de trabajo, reintentos idempotentes, recuperación de solicitudes, cálculo sin división por cero y límite de pendientes.
- HTTP Apache: instalación inicial, rechazo de contraseña incorrecta, login válido, selección de empresa, solicitud de reporte y rechazo de CSRF inválido. Acceso HTTP al almacenamiento privado denegado (403).
- Recorrido real beta PHP → conector C# aislado → SQL Server → beta PHP: terminado, 444 filas del 1 al 10 de enero de 2026 en adACEROS_2025. Neto 3,064,315.99; costo 1,998,673.0596; utilidad 1,065,642.9304.
- El ejecutable de prueba reutilizó Worker.cs y Settings.cs con directorio temporal y entrada de configuración aislados. No cambió la configuración ni la instalación del servicio Windows existente; este sigue Running y conectado a conector-demo.
- Compilación C# correcta. La comprobación online de vulnerabilidades NuGet no estuvo disponible (NU1900); no se presenta como auditoría de dependencias.
- Login revisado visualmente en navegador con logo COPROI y colores de MODCOMERCIAL.
- ZIP revisado: solo archivos de aplicación, instrucciones y configuración inicial con clave aleatoria; sin usuarios, datos de prueba, tokens de empresa ni credenciales SQL.

Pendiente para la siguiente etapa: subir al hosting del usuario, configurar HTTPS y vincular el conector a su URL pública. Esa conectividad aún no fue comprobada.
