<?php
function page_start(string $title): void { ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> · MODCOMERCIAL Beta</title><link rel="stylesheet" href="assets/style.css"></head><body><main>
<header><a class="brand" href="index.php"><img src="assets/coproi.jpeg" alt="Soluciones COPROI"><span>MODCOMERCIAL <small>BETA</small></span></a><span class="tag">Prueba de conexión remota</span></header>
<?php }
function page_end(): void { echo '<footer>MODCOMERCIAL Beta · La base de datos permanece en el servidor de tu empresa.</footer></main></body></html>'; }
