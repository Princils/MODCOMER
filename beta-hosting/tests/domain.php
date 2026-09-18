<?php
require __DIR__.'/../private/bootstrap.php';
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "OK $label\n";}
function denied(callable $f,$label){try{$f();}catch(DomainException $e){check(true,$label);return;}throw new RuntimeException($label);}
$s=['users'=>[],'companies'=>[],'jobs'=>[],'attempts'=>[]];
$a=add_company($s,'Empresa A');$b=add_company($s,'Empresa B');
$u=['id'=>'ana','role'=>'user','companies'=>[$a['id']]];
$call=function($action,$body=[],$connector=null,$actor=null)use(&$s,$u){return endpoint_action($s,$action,$body,$connector,$actor??$u);};
$body=['company'=>$a['id'],'inicio'=>'2026-01-01','fin'=>'2026-01-10'];
denied(fn()=> $call('enqueue',array_replace($body,['company'=>$b['id']])),'aislamiento de empresas');
denied(fn()=> $call('enqueue',array_replace($body,['inicio'=>'2026-02-30'])),'fechas invalidas');
$id=$call('enqueue',$body)['id'];
check($call('claim',['company'=>$b['id']],$b['id'])['job']===null,'conector B no recibe A');
$job=$call('claim',['company'=>$a['id']],$a['id'])['job'];
check($job['id']===$id,'conector recibe trabajo');
check(!isset($call('status',['company'=>$a['id'],'id'=>$id])['job']['lease']),'lease no expuesto al navegador');
denied(fn()=> $call('status',['company'=>$a['id'],'id'=>$id],null,['id'=>'otro','role'=>'admin']),'propietario del reporte');
$result=['company'=>$a['id'],'id'=>$id,'lease'=>$job['lease'],'status'=>'completed','rows'=>[['Importe Ventas'=>100,'Importe de Costo'=>60,'Descuento'=>0]]];
denied(fn()=> $call('complete',array_replace($result,['lease'=>'incorrecto']),$a['id']),'lease incorrecto');
denied(fn()=> $call('complete',array_replace($result,['company'=>$b['id']]),$b['id']),'entrega entre empresas rechazada');
$call('complete',$result,$a['id']);$call('complete',array_replace($result,['rows'=>[]]),$a['id']);
check($call('status',['company'=>$a['id'],'id'=>$id])['job']['totals']['utilidad']===40.0,'entrega idempotente y totales');
check(totals([])['margen']===0,'margen sin division por cero');
$id2=$call('enqueue',$body)['id'];$first=$call('claim',['company'=>$a['id']],$a['id'])['job'];
$s['jobs'][$id2]['leaseUntil']=time()-1;
check($call('claim',['company'=>$a['id']],$a['id'])['job']['lease']===$first['lease'],'recuperacion tras desconexion');
$call('enqueue',$body);$call('enqueue',$body);
denied(fn()=> $call('enqueue',$body),'limite de pendientes');
echo "Pruebas de dominio completas.\n";
