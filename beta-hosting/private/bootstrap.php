<?php
declare(strict_types=1);
ini_set('display_errors','0');
function config(): array {
    static $config;
    if ($config===null) {
        $path=__DIR__.'/config.local.php';
        if (!is_file($path)) throw new RuntimeException('Falta private/config.local.php. Revisa INSTALACION.md.');
        $config=require $path;
    }
    return $config;
}
function is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') return true;
    return in_array($_SERVER['REMOTE_ADDR']??'',config()['trusted_proxies']??[],true)
        && ($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';
}
function security(): void {
    if (PHP_SAPI==='cli') return;
    $local=(config()['allow_local_http']??false) && in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true);
    if (!is_https() && !$local) { http_response_code(400); exit('Esta beta requiere HTTPS. Configura el certificado de tu hosting.'); }
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    if (is_https()) header('Strict-Transport-Security: max-age=31536000');
}
function session_begin(): void {
    if (session_status()===PHP_SESSION_ACTIVE) return;
    session_name('MODCOMBETA');
    session_set_cookie_params(['httponly'=>true,'secure'=>is_https(),'samesite'=>'Strict','path'=>rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/')),'/').'/']);
    ini_set('session.use_strict_mode','1');
    session_start();
    if (isset($_SESSION['seen']) && time()-$_SESSION['seen']>1800) $_SESSION=[];
    $_SESSION['seen']=time();
    $_SESSION['csrf']=$_SESSION['csrf']??bin2hex(random_bytes(32));
}
function h($value): string { return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function csrf(): void {
    $token=$_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['csrf']??'';
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'],$token)) throw new DomainException('La sesión venció. Recarga la página.');
}
function store(callable $operation) {
    $directory=config()['storage'];
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) throw new RuntimeException('No se pudo crear el almacenamiento privado.');
    $lock=fopen($directory.'/state.lock','c');
    if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('No se pudo bloquear el almacenamiento.');
    try {
        $path=$directory.'/state.json';
        $state=is_file($path)?json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR):['users'=>[],'companies'=>[],'jobs'=>[],'attempts'=>[]];
        $before=$state;
        $cutoff=time()-max(1,(int)(config()['retention_hours']??24))*3600;
        foreach($state['jobs'] as $id=>$job) if($job['created']<$cutoff) unset($state['jobs'][$id]);
        foreach($state['attempts'] as $id=>$attempt) if($attempt['until']<time()) unset($state['attempts'][$id]);
        $result=$operation($state);
        if ($state!==$before) {
            $temp=$directory.'/state.'.bin2hex(random_bytes(8)).'.tmp';
            $json=json_encode($state,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
            if (file_put_contents($temp,$json)!==strlen($json)) throw new RuntimeException('No se pudo guardar el estado.');
            if (!rename($temp,$path)) { @unlink($temp); throw new RuntimeException('No se pudo confirmar el estado.'); }
        }
        return $result;
    } finally { flock($lock,LOCK_UN); fclose($lock); }
}
function user(array $state): array {
    $id=$_SESSION['user']??'';
    if (empty($state['users'][$id])) throw new DomainException('Inicia sesión.');
    return $state['users'][$id]+['id'=>$id];
}
function company(array $state,array $u,string $id): array {
    if (empty($state['companies'][$id]) || ($u['role']!=='admin' && !in_array($id,$u['companies'],true))) throw new DomainException('Empresa no autorizada.');
    return $state['companies'][$id]+['id'=>$id];
}
function add_company(array &$state,string $name): array {
    $name=trim($name);
    if (strlen($name)<2 || strlen($name)>120) throw new DomainException('Escribe un nombre de empresa de 2 a 120 caracteres.');
    if (count($state['companies'])>=20) throw new DomainException('La beta admite hasta 20 empresas.');
    $id='empresa_'.bin2hex(random_bytes(6));
    $token=bin2hex(random_bytes(32));
    $state['companies'][$id]=['name'=>$name,'tokenHash'=>hash('sha256',$token),'lastContact'=>null];
    return ['id'=>$id,'name'=>$name,'token'=>$token];
}
function dates(array $body): array {
    $a=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($body['inicio']??''));
    $b=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($body['fin']??''));
    if (!$a || !$b || $a->format('Y-m-d')!==$body['inicio'] || $b->format('Y-m-d')!==$body['fin'] || $b<$a || $a->diff($b)->days>366)
        throw new DomainException('Selecciona fechas válidas; máximo 366 días.');
    return [$body['inicio'],$body['fin']];
}
function totals(array $rows): array {
    $t=['filas'=>count($rows),'neto'=>0.0,'costo'=>0.0,'descuento'=>0.0];
    foreach($rows as $row) {
        $t['neto']+=(float)($row['Importe Ventas']??0);
        $t['costo']+=(float)($row['Importe de Costo']??0);
        $t['descuento']+=(float)($row['Descuento']??0);
    }
    $t['utilidad']=$t['neto']-$t['costo']; $t['margen']=$t['neto']==0?0:$t['utilidad']*100/$t['neto']; return $t;
}
function endpoint_action(array &$s,string $action,array $body,?string $connectorCompany,?array $u): array {
    if ($connectorCompany!==null) {
        if ($action==='package') {
            if (($body['report']??'')!=='utilidad_documentos') throw new DomainException('Reporte no habilitado.');
            return json_decode(file_get_contents(__DIR__.'/report.signed.json'),true,512,JSON_THROW_ON_ERROR);
        }
        if (($body['company']??'')!==$connectorCompany) throw new DomainException('Empresa no autorizada.');
        if ($action==='claim') {
            $s['companies'][$connectorCompany]['lastContact']=time();
            foreach($s['jobs'] as &$job) {
                if ($job['company']===$connectorCompany && ($job['status']==='pending' || ($job['status']==='running' && $job['leaseUntil']<time()))) {
                    $job['status']='running'; $job['leaseUntil']=time()+180;
                    return ['job'=>array_intersect_key($job,array_flip(['id','company','report','inicio','fin','lease']))];
                }
            } unset($job);
            return ['job'=>null];
        }
        if ($action==='complete') {
            $id=(string)($body['id']??'');$job=$s['jobs'][$id]??null;
            if (!$job || $job['company']!==$connectorCompany || !hash_equals($job['lease'],(string)($body['lease']??''))) throw new DomainException('Trabajo no autorizado.');
            if (!in_array($body['status']??'', ['completed','failed'],true)) throw new DomainException('Estado inválido.');
            if (!in_array($job['status'],['completed','failed'],true)) {
                $rows=$body['rows']??[];
                if (!is_array($rows) || count($rows)>20000) throw new DomainException('Resultado demasiado grande.');
                foreach($rows as $row) {
                    if(!is_array($row)) throw new DomainException('Resultado inválido.');
                    foreach(['Importe Ventas','Importe de Costo','Descuento'] as $key)
                        if (!array_key_exists($key,$row) || !is_numeric($row[$key]) || !is_finite((float)$row[$key])) throw new DomainException('Importes inválidos.');
                }
                $s['jobs'][$id]['status']=$body['status'];$s['jobs'][$id]['rows']=$rows;
                $s['jobs'][$id]['error']=$body['status']==='failed'?substr((string)($body['error']??'Error del conector.'),0,400):null;
                $s['jobs'][$id]['finished']=time();
            }
            return ['ok'=>true];
        }
        throw new DomainException('Acción no autorizada.');
    }
    if (!$u) throw new DomainException('Inicia sesión.');
    $id=(string)($body['company']??$_SESSION['company']??'');
    $c=company($s,$u,$id);
    if ($action==='enqueue') {
        [$inicio,$fin]=dates($body);
        $active=array_filter($s['jobs'],fn($j)=>$j['company']===$id && in_array($j['status'],['pending','running'],true));
        if(count($active)>=3) throw new DomainException('Hay reportes pendientes para esta empresa. Espera a que terminen.');
        $jobId=bin2hex(random_bytes(16));
        $s['jobs'][$jobId]=['id'=>$jobId,'owner'=>$u['id'],'company'=>$id,'report'=>'utilidad_documentos','inicio'=>$inicio,'fin'=>$fin,'status'=>'pending','created'=>time(),'lease'=>bin2hex(random_bytes(24)),'leaseUntil'=>0];
        return ['id'=>$jobId];
    }
    if ($action==='status') {
        $job=$s['jobs'][(string)($body['id']??'')]??null;
        if($job && ($job['company']!==$id || $job['owner']!==$u['id'])) throw new DomainException('Reporte no autorizado.');
        if($job) { unset($job['lease'],$job['owner']);if(isset($job['rows']))$job['totals']=totals($job['rows']); }
        return ['online'=>$c['lastContact']!==null && time()-$c['lastContact']<30,'lastContact'=>$c['lastContact'],'job'=>$job];
    }
    throw new DomainException('Acción inválida.');
}
