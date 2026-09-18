<?php
session_start();
if (empty($_SESSION['codigo_usuario']) || empty($_SESSION['database'])) {
    header('Location: ../index.php?vista=login'); exit;
}
$_SESSION['connector_csrf']=$_SESSION['connector_csrf']??bin2hex(random_bytes(32));
?>
<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>MODCOMERCIAL · Prueba del conector</title>
<style>
body{margin:0;background:#f2f7f9;color:#13364b;font:16px system-ui}main{max-width:1200px;margin:40px auto;padding:0 24px}
header{display:flex;align-items:center;justify-content:space-between;gap:20px}h1{margin:0}p{line-height:1.6}
.card{background:white;border:1px solid #d5e5ea;border-radius:12px;padding:24px;margin:24px 0;box-shadow:0 5px 24px #13364b09}
form{display:flex;align-items:end;gap:24px;flex-wrap:wrap}label{display:grid;gap:8px}input,button{font:inherit;padding:10px 16px;border:1px solid #bfd2db;border-radius:6px}
button{background:#087f99;color:white;cursor:pointer}button:disabled{opacity:.5}#badge{background:#e2eaf0;padding:8px 16px;border-radius:24px}
#message{min-height:24px}#tableWrap{overflow:auto;max-height:500px}table{border-collapse:collapse;font-size:13px;width:100%}td,th{padding:4px 8px;white-space:nowrap;border:1px solid #d5e5ea}th{background:#087f99;color:white;position:sticky;top:0}
a{color:#087f99}small{color:#587383}
</style>
<main><header><div><small>SOLUCIONES COPROI · DEMOSTRACIÓN</small><h1>MODCOMERCIAL Conector</h1></div><span id="badge">Comprobando conexión…</span></header>
<p>Esta página solicita el reporte por PHP. El servicio de Windows consulta SQL Server y devuelve los resultados.</p>
<div class="card"><h2>Utilidad por Documentos</h2><form id="request"><label>Fecha inicial<input id="inicio" type="date" value="2026-01-01" required></label><label>Fecha final<input id="fin" type="date" value="2026-01-10" required></label><button id="calculate">Solicitar reporte al conector</button></form><p id="message" role="status"></p><small>Ejemplo de solo lectura · Todos los clientes y conceptos activos del reporte · Los resultados se conservan hasta 24 horas.</small></div>
<div class="card"><h2 id="totals">Resultados</h2><div id="tableWrap"><table id="results"></table></div></div>
<a href="../index.php?vista=Dashboard">Volver a MODCOMERCIAL</a>
<script>
const csrf=<?=json_encode($_SESSION['connector_csrf'],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
let job='',busy=false,polling=false;
const message=document.getElementById('message'),badge=document.getElementById('badge'),button=document.getElementById('calculate');
async function call(action,body){
 const response=await fetch('api.php?action='+action,{method:body?'POST':'GET',headers:body?{'Content-Type':'application/json','X-CSRF-Token':csrf}:{},body:body?JSON.stringify(body):undefined});
 const result=await response.json();if(!response.ok)throw new Error(result.error||'No se pudo completar la solicitud.');return result;
}
function render(rows){
 const table=document.getElementById('results');table.replaceChildren();
 const names=rows.length?Object.keys(rows[0]):[];const header=table.createTHead().insertRow();
 names.forEach(name=>{const th=document.createElement('th');th.textContent=name;header.append(th)});
 const body=table.createTBody();let neto=0,costo=0;
 rows.forEach(row=>{const tr=body.insertRow();names.forEach(name=>{tr.insertCell().textContent=row[name]??''});neto+=Number(row['Importe Ventas']||0);costo+=Number(row['Importe de Costo']||0)});
 const money=new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'});
 document.getElementById('totals').textContent=rows.length+' filas · Neto '+money.format(neto)+' · Utilidad '+money.format(neto-costo);
}
async function poll(){
 if(polling)return;polling=true;
 try{const data=await call('status'+(job?'&id='+encodeURIComponent(job):''));
 badge.textContent=data.online?'● Conector en línea':'● Conector sin comunicación';badge.style.background=data.online?'#d9f4eb':'#ffe9cd';
 if(busy&&data.job){
 const s=data.job.status;message.textContent=s==='pending'?'Solicitud pendiente. Esperando al conector…':s==='running'?'El conector está consultando SQL Server…':s==='completed'?'Reporte recibido correctamente.':data.job.error;
 if(s==='completed')render(data.job.rows);
 if(s==='completed'||s==='failed'){busy=false;button.disabled=false;}
 }
 }catch(e){message.textContent=e.message;}finally{polling=false;}
}
document.getElementById('request').addEventListener('submit',async e=>{
 e.preventDefault();button.disabled=true;
 try{const data=await call('enqueue',{inicio:document.getElementById('inicio').value,fin:document.getElementById('fin').value});job=data.id;busy=true;message.textContent='Solicitud enviada al conector.';await poll();}
 catch(e){message.textContent=e.message;button.disabled=false;}
});
poll();setInterval(poll,2500);
</script></main></html>

