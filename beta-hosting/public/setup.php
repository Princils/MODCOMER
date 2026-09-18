<?php
declare(strict_types=1);
require __DIR__.'/load.php';
security();session_begin();
$installed=store(fn(&$s)=>count($s['users'])>0);
if($installed){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf();
        $answer=store(function(&$s){
            if($s['users'])return ['error'=>'La instalación ya fue completada.'];
            $key=hash('sha256','setup:'.($_SERVER['REMOTE_ADDR']??''));
            $attempt=$s['attempts'][$key]??['count'=>0,'until'=>0];
            if($attempt['until']>time() && $attempt['count']>=5)return ['error'=>'Demasiados intentos. Espera 15 minutos.'];
            if(strlen(config()['setup_key']??'')<32 || !hash_equals(config()['setup_key'],(string)($_POST['key']??''))){
                $s['attempts'][$key]=['count'=>$attempt['until']>time()?$attempt['count']+1:1,'until'=>time()+900];
                return ['error'=>'Clave de instalación incorrecta.'];
            }
            $username=strtolower(trim((string)($_POST['username']??'')));
            $password=(string)($_POST['password']??'');
            if(!preg_match('/^[a-z0-9_.-]{3,40}$/',$username))return ['error'=>'Usuario: 3 a 40 letras, números, punto o guion.'];
            if(strlen($password)<12 || strlen($password)>128)return ['error'=>'La contraseña debe tener entre 12 y 128 caracteres.'];
            $company=add_company($s,(string)($_POST['company']??''));
            $s['users'][$username]=['password'=>password_hash($password,PASSWORD_DEFAULT),'role'=>'admin','companies'=>[$company['id']]];
            return ['user'=>$username,'company'=>$company];
        });
        if(isset($answer['error']))throw new DomainException($answer['error']);
        session_regenerate_id(true);$_SESSION['user']=$answer['user'];$_SESSION['company']=$answer['company']['id'];$_SESSION['pairing']=$answer['company'];
        header('Location: index.php?view=companies');exit;
    }catch(DomainException $e){$error=$e->getMessage();}
}
page_start('Primera instalación');
?>
<section class="narrow card"><span class="eyebrow">PRIMER ACCESO</span><h1>Prepara tu beta</h1><p>Crea el administrador y registra la primera empresa. No necesitas datos ni contraseñas de SQL Server en el hosting.</p>
<?php if($error):?><p class="error" role="alert"><?=h($error)?></p><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>">
<label>Clave de instalación<input name="key" type="password" required autocomplete="off"><small>Se encuentra en private/config.local.php del ZIP.</small></label>
<label>Usuario administrador<input name="username" required autocomplete="username" minlength="3" maxlength="40"></label>
<label>Contraseña nueva<input name="password" type="password" required autocomplete="new-password" minlength="12" maxlength="128"></label>
<label>Nombre de la empresa<input name="company" required maxlength="120" placeholder="Ejemplo: Aceros del Nayar"></label>
<button>Crear administrador y empresa</button></form></section>
<?php page_end();?>
