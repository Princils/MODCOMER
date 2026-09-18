<?php
declare(strict_types=1);
require __DIR__.'/load.php';
header('Content-Type: application/json; charset=utf-8');
try {
    security();
    if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); throw new DomainException('Usa POST.'); }
    if ((int)($_SERVER['CONTENT_LENGTH']??0)>25000000) { http_response_code(413); throw new DomainException('Solicitud demasiado grande.'); }
    $body=json_decode(file_get_contents('php://input')?:'{}',true,512,JSON_THROW_ON_ERROR);
    if(!is_array($body)) throw new DomainException('Solicitud inválida.');
    $action=$_GET['action']??'';
    $backend=in_array($action,['claim','complete','package'],true);
    if(!$backend){session_begin();csrf();}
    $result=store(function(&$s)use($body,$action,$backend){
        $connector=null;$u=null;
        if($backend){
            $headers=function_exists('getallheaders')?array_change_key_case(getallheaders(),CASE_LOWER):[];
            $auth=$_SERVER['HTTP_AUTHORIZATION']??$_SERVER['REDIRECT_HTTP_AUTHORIZATION']??$headers['authorization']??'';
            if(strncmp($auth,'Bearer ',7)===0){
                $hash=hash('sha256',substr($auth,7));
                foreach($s['companies'] as $id=>$c) if(hash_equals($c['tokenHash'],$hash)){$connector=$id;break;}
            }
            if($connector===null){http_response_code(401);throw new DomainException('Conector no autorizado.');}
        }else{$u=user($s);}
        return endpoint_action($s,$action,$body,$connector,$u);
    });
    echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR);
}catch(DomainException $e){
    if(http_response_code()<400)http_response_code(403);
    echo json_encode(['error'=>$e->getMessage()]);
}catch(JsonException $e){
    http_response_code(400);echo json_encode(['error'=>'JSON inválido.']);
}catch(Throwable $e){
    error_log('MODCOMERCIAL beta: '.$e->getMessage());http_response_code(500);
    echo json_encode(['error'=>'No se pudo procesar la solicitud. Revisa el registro privado del hosting.']);
}
