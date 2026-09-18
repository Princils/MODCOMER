<?php
declare(strict_types=1);
// En hosting puedes cambiar únicamente esta ruta si private queda fuera de public_html.
$privateDirectory = getenv('MODCOM_BETA_PRIVATE') ?: __DIR__.'/../private';
require $privateDirectory.'/bootstrap.php';
require $privateDirectory.'/layout.php';
