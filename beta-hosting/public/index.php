<?php
declare(strict_types=1);
require __DIR__.'/load.php';
security();session_begin();
$state=store(fn(&$s)=>['users'=>$s['users'],'companies'=>$s['companies']]);
if(!$state['users']){header('Location: setup.php');exit;}
$error='';
$action=$_POST['action']??'';
if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf();
        if($action==='login'){
            $answer=store(function(&$s){
                $key=hash('sha256','login:'.($_SERVER['REMOTE_ADDR']??''));
                $a=$s['attempts'][$key]??['count'=>0,'until'=>0];
                if($a['until']>time() && $a['count']>=5)return ['error'=>'Demasiados intentos. Espera 15 minutos.'];
                $id=strtolower(trim((string)($_POST['username']??'')));$u=$s['users'][$id]??null;
                $valid=password_verify((string)($_POST['password']??''),$u['password']??'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
                if(!$u || !$valid){
                    $s['attempts'][$key]=['count'=>$a['until']>time()?$a['count']+1:1,'until'=>time()+900];
                    return ['error'=>'Usuario o contraseña incorrectos.'];
                }
                unset($s['attempts'][$key]);return ['id'=>$id];
            });
            if(isset($answer['error']))throw new DomainException($answer['error']);
            session_regenerate_id(true);$_SESSION['user']=$answer['id'];unset($_SESSION['company']);
            header('Location: index.php?view=companies');exit;
        }
        $u=user($state);
        if($action==='logout'){$_SESSION=[];session_destroy();header('Location: index.php');exit;}
        if($action==='select'){
            $c=company($state,$u,(string)($_POST['company']??''));$_SESSION['company']=$c['id'];
            header('Location: index.php?view=report');exit;
        }
        if($action==='add_company'){
            if($u['role']!=='admin')throw new DomainException('No autorizado.');
            $pair=store(function(&$s){return add_company($s,(string)($_POST['name']??''));});
            $_SESSION['pairing']=$pair;header('Location: index.php?view=companies');exit;
        }
        if($action==='rotate'){
            if($u['role']!=='admin')throw new DomainException('No autorizado.');
            $id=(string)($_POST['company']??'');company($state,$u,$id);
            $token=bin2hex(random_bytes(32));
            store(function(&$s)use($id,$token){$s['companies'][$id]['tokenHash']=hash('sha256',$token);$s['companies'][$id]['lastContact']=null;});
            $_SESSION['pairing']=['id'=>$id,'name'=>$state['companies'][$id]['name'],'token'=>$token];
            header('Location: index.php?view=companies');exit;
        }
    }catch(DomainException $e){$error=$e->getMessage();}
}
$logged=!empty($_SESSION['user']) && isset($state['users'][$_SESSION['user']]);
$view=$logged?($_GET['view']??'companies'):'login';
if($view==='report' && empty($_SESSION['company']))$view='companies';
page_start($view==='login'?'Iniciar sesión':($view==='report'?'Utilidad por Documentos':'Seleccionar empresa'));
if($error):?><p class="error" role="alert"><?=h($error)?></p><?php endif;
if(!$logged):?>
<section class="narrow card"><span class="eyebrow">ACCESO A LA BETA</span><h1>Bienvenido a MODCOMERCIAL</h1><p>Consulta los reportes de tu empresa desde aquí.</p><form method="post">
<input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="action" value="login">
<label>Usuario<input name="username" autocomplete="username" required maxlength="40"></label>
<label>Contraseña<input name="password" type="password" autocomplete="current-password" required maxlength="128"></label><button>Iniciar sesión</button></form></section>
<?php else: $u=user($state);?>
<nav><a href="index.php?view=companies">Empresas</a><span><?=h($u['id'])?></span><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="action" value="logout"><button class="secondary">Cerrar sesión</button></form></nav>
<?php if($view==='companies'):?>
<h1>Selecciona tu empresa</h1><p>Cada empresa se vincula con su propio conector local.</p>
<?php $pair=$_SESSION['pairing']??null;unset($_SESSION['pairing']);if($pair):?>
<section class="card pairing"><h2>Vincular <?=h($pair['name'])?></h2><p>En la ventana del conector, abre <strong>Configurar</strong> y captura estos datos. El token se muestra una sola vez.</p>
<label>Dirección de la plataforma<input id="endpoint" readonly></label><label>Empresa<input readonly value="<?=h($pair['id'])?>"></label><label>Token de vinculación<input readonly value="<?=h($pair['token'])?>" autocomplete="off"></label>
<p>La instancia, la base y sus credenciales se configuran únicamente en el conector, en la PC del cliente.</p></section>
<?php endif;?>
<div class="companies"><?php foreach($state['companies'] as $id=>$c):if($u['role']!=='admin' && !in_array($id,$u['companies'],true))continue;?>
<section class="card"><span class="tag"><?= $c['lastContact'] && time()-$c['lastContact']<30?'● Conector en línea':'○ Sin comunicación reciente' ?></span><h2><?=h($c['name'])?></h2>
<form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="action" value="select"><input type="hidden" name="company" value="<?=h($id)?>"><button>Entrar a la empresa</button></form>
<?php if($u['role']==='admin'):?><details><summary>Vincular o cambiar conector</summary><p>Generar otro token invalida el anterior. Tendrás que actualizarlo en el conector.</p><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="action" value="rotate"><input type="hidden" name="company" value="<?=h($id)?>"><button class="secondary">Generar nuevo token</button></form></details><?php endif;?></section>
<?php endforeach;?></div>
<?php if($u['role']==='admin'):?><details class="card"><summary>Agregar empresa</summary><form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="action" value="add_company"><label>Nombre<input name="name" required maxlength="120"></label><button>Crear empresa</button></form></details><?php endif;?>
<?php else: $c=company($state,$u,$_SESSION['company']);?>
<section id="report" data-csrf="<?=h($_SESSION['csrf'])?>" data-company="<?=h($c['id'])?>">
<div class="report-title"><div><span class="eyebrow"><?=h($c['name'])?></span><h1>Utilidad por Documentos</h1></div><span id="badge" class="tag">Comprobando conector…</span></div>
<p>Consulta ventas, costo y utilidad. La información se obtiene desde la base local de esta empresa.</p>
<div class="card"><form id="request" class="filters"><label>Fecha inicial<input id="inicio" type="date" value="<?=date('Y-m-01')?>" required></label><label>Fecha final<input id="fin" type="date" value="<?=date('Y-m-d')?>" required></label><button id="calculate">Calcular reporte</button></form><p id="message" role="status"></p><small>Beta: todos los clientes y conceptos activos del reporte. Máximo 366 días y 20,000 filas.</small></div>
<div class="card"><div class="report-title"><h2 id="totals">Resultados</h2><button id="export" class="secondary" disabled>Descargar CSV</button></div><div class="table-wrap"><table id="results"></table></div></div></section>
<?php endif;?><script src="assets/app.js" defer></script>
<?php endif;page_end();?>
