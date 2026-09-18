<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function reply(array $data, int $status=200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
$folder = (getenv('ProgramData') ?: 'C:/ProgramData').'/MODCOMERCIAL/ConnectorDemo';
try {
    if (!is_file($folder.'/config.json')) reply(['error'=>'La demostración todavía no está vinculada.'],503);
    $config = json_decode(file_get_contents($folder.'/config.json'),true,512,JSON_THROW_ON_ERROR);
    $action = $_GET['action'] ?? '';
    $backend = in_array($action,['claim','complete','package'],true);
    if ($backend) {
        $headers = function_exists('getallheaders') ? array_change_key_case(getallheaders(),CASE_LOWER) : [];
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? $headers['authorization'] ?? '';
        if (!hash_equals('Bearer '.$config['token'],$authorization)) reply(['error'=>'No autorizado.'],401);
    } else {
        session_start();
        if (empty($_SESSION['codigo_usuario']) || ($_SESSION['database'] ?? '') !== $config['database']) reply(['error'=>'Inicia sesión y selecciona la empresa vinculada.'],403);
        require_once __DIR__.'/../configuracion/configuracion.php';
        require_once __DIR__.'/../configuracion/conexion.php';
        require_once __DIR__.'/../modelo/ModeloPrincipal.php';
        $id = ModeloPrincipal::BusacrReporteModelo('Utilidad por Documentos', 'Ventas Con Utilidad');
        if (!$id || empty($_SESSION['lvl'.$id])) reply(['error'=>'No tienes acceso al reporte.'],403);
        if ($action==='enqueue' && (empty($_SESSION['connector_csrf']) || !hash_equals($_SESSION['connector_csrf'],$_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) reply(['error'=>'Sesión inválida.'],403);
        $owner=(string)$_SESSION['codigo_usuario'];
        session_write_close();
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0)>25000000) reply(['error'=>'Solicitud demasiado grande.'],413);
    if ($action!=='status' && $_SERVER['REQUEST_METHOD']!=='POST') reply(['error'=>'Usa POST.'],405);
    $body=json_decode(file_get_contents('php://input') ?: '{}',true,512,JSON_THROW_ON_ERROR);
    if ($action==='package') {
        if (($body['report']??'')!=='utilidad_documentos') reply(['error'=>'Reporte no habilitado.'],400);
        reply(json_decode(file_get_contents(__DIR__.'/report.signed.json'),true,512,JSON_THROW_ON_ERROR));
    }
    $handle=fopen($folder.'/queue.json','c+');
    if (!$handle || !flock($handle,LOCK_EX)) throw new RuntimeException('No se pudo bloquear la cola.');
    $text=stream_get_contents($handle);
    $state=$text ? json_decode($text,true,512,JSON_THROW_ON_ERROR) : ['lastContact'=>null,'jobs'=>[]];
    $result=[];
    if ($action==='enqueue') {
        $start=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($body['inicio']??''));
        $end=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($body['fin']??''));
        if (!$start || !$end || $start->format('Y-m-d')!==$body['inicio'] || $end->format('Y-m-d')!==$body['fin'] || $end<$start || $start->diff($end)->days>366) {
            flock($handle,LOCK_UN); fclose($handle); reply(['error'=>'Usa fechas válidas, con un rango máximo de 366 días.'],400);
        }
        $id=bin2hex(random_bytes(12));
        $state['jobs'][$id]=['id'=>$id,'company'=>$config['company'],'report'=>'utilidad_documentos','inicio'=>$body['inicio'],'fin'=>$body['fin'],'status'=>'pending','owner'=>$owner,'created'=>time(),'lease'=>bin2hex(random_bytes(24))];
        $result=['id'=>$id,'status'=>'pending'];
    } elseif ($action==='claim') {
        if (($body['company']??'')!==$config['company']) { flock($handle,LOCK_UN); fclose($handle); reply(['error'=>'Empresa inválida.'],403); }
        $state['lastContact']=time(); $result=['job'=>null];
        foreach($state['jobs'] as &$job) {
            if ($job['status']==='pending' || ($job['status']==='running' && ($job['leaseUntil']??0)<time())) {
                $job['status']='running'; $job['leaseUntil']=time()+180;
                $result=['job'=>array_intersect_key($job,array_flip(['id','report','company','lease','inicio','fin']))]; break;
            }
        } unset($job);
    } elseif ($action==='complete') {
        $id=$body['id']??''; $job=$state['jobs'][$id]??null;
        if (!$job || ($body['company']??'')!==$config['company'] || !hash_equals($job['lease'],$body['lease']??'')) {
            flock($handle,LOCK_UN); fclose($handle); reply(['error'=>'Trabajo inválido.'],403);
        }
        if (!in_array($body['status']??'',['completed','failed'],true)) {
            flock($handle,LOCK_UN); fclose($handle); reply(['error'=>'Estado inválido.'],400);
        }
        // Entrega idempotente: el mismo resultado puede reenviarse tras una interrupción.
        if (!in_array($job['status'],['completed','failed'],true)) {
            $state['jobs'][$id]['status']=$body['status'];
            $state['jobs'][$id]['rows']=$body['rows']??[];
            $state['jobs'][$id]['error']=$body['error']??null;
            $state['jobs'][$id]['finished']=time();
        }
        $result=['ok'=>true];
    } elseif ($action==='status') {
        $id=$_GET['id']??''; $job=$state['jobs'][$id]??null;
        if ($job && $job['owner']!==$owner) $job=null;
        if ($job) unset($job['lease'],$job['owner']);
        $result=['online'=>$state['lastContact'] && time()-$state['lastContact']<25,'lastContact'=>$state['lastContact'],'job'=>$job];
    } else {
        flock($handle,LOCK_UN); fclose($handle); reply(['error'=>'Acción inválida.'],400);
    }
    foreach($state['jobs'] as $id=>$job) if (($job['finished']??time())<time()-86400) unset($state['jobs'][$id]);
    rewind($handle); ftruncate($handle,0); fwrite($handle,json_encode($state,JSON_INVALID_UTF8_SUBSTITUTE)); fflush($handle); flock($handle,LOCK_UN); fclose($handle);
    reply($result);
} catch(Throwable $e) {
    error_log('MODCOMERCIAL demo connector: '.$e->getMessage());
    reply(['error'=>'No se pudo procesar la solicitud del conector.'],500);
}
