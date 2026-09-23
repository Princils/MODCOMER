# Actualizacion 0.1.2 — compatibilidad de hosting

La API devolvia HTTP 409 con un documento exacto que solicita la cookie humans_21909=1. El transporte ahora reconoce exclusivamente esa respuesta por HTTPS, conserva la cookie en memoria para el mismo origen (esquema, dominio y puerto) y reintenta una vez. No ejecuta JavaScript, no sigue redirecciones y mantiene la autenticacion Bearer y la validacion TLS. Los conflictos 409 distintos siguen siendo errores.

Tambien conserva la correccion 0.1.1: JSON con Content-Length, necesario para evitar el rechazo 406 observado en ModSecurity. El instalador detiene el servicio antes de reemplazar archivos y conserva la configuracion protegida existente.

Pruebas automaticas: desafio seguido de exito, reutilizacion de cookie, aislamiento entre origenes, limite de reintentos, conflictos reales sin reintento, encabezado de autenticacion y Content-Length. Se mantienen las pruebas de firmas y HTTPS.

Si el proveedor cambia el desafio, el conector mostrara el rechazo; este ajuste solo cubre el formato verificado. No modifica el PHP ni la base de datos.

Monitor: Actualizar estado muestra una barra animada y texto durante la consulta, evita clics repetidos y muestra la hora al finalizar. El boton consulta el estado local; el servicio realiza los reintentos de conexion automaticamente.
