<?php
return [
    // El ZIP incluye una clave aleatoria aquí. Se usa UNA sola vez en setup.php.
    'setup_key' => 'REEMPLAZAR_POR_UNA_CLAVE_ALEATORIA_DE_64_CARACTERES',
    'storage' => __DIR__.'/data',
    // Mantener false en hosting. true solo se admite desde 127.0.0.1/::1.
    'allow_local_http' => false,
    'trusted_proxies' => [],
    'retention_hours' => 24,
];
