'use strict';
const endpoint = document.getElementById('endpoint');
if (endpoint) endpoint.value = new URL('api.php', window.location.href).href;
const report = document.getElementById('report');
if (report) {
    let job = '', busy = false, polling = false, rows = [];
    const message = document.getElementById('message');
    const badge = document.getElementById('badge');
    const button = document.getElementById('calculate');
    const exportButton = document.getElementById('export');
    const money = new Intl.NumberFormat('es-MX', {style:'currency',currency:'MXN'});
    async function call(action, body = {}) {
        const response = await fetch('api.php?action=' + action, {
            method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-Token':report.dataset.csrf},
            body: JSON.stringify(Object.assign({company: report.dataset.company},body))
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'No se pudo completar la solicitud.');
        return result;
    }
    function render(data) {
        rows = data.rows;
        const table = document.getElementById('results');
        table.replaceChildren();
        const names = rows.length ? Object.keys(rows[0]) : [];
        const header = table.createTHead().insertRow();
        names.forEach(name => {const cell=document.createElement('th');cell.textContent=name;header.append(cell);});
        const body = table.createTBody();
        rows.forEach(row => {const tr=body.insertRow();names.forEach(name=>{tr.insertCell().textContent=row[name]??'';});});
        const t = data.totals;
        document.getElementById('totals').textContent = t.filas+' filas · Neto '+money.format(t.neto)+' · Costo '+money.format(t.costo)+' · Utilidad '+money.format(t.utilidad)+' · Margen '+t.margen.toFixed(2)+'%';
        exportButton.disabled = rows.length === 0;
    }
    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const data = await call('status', {id:busy ? job : ''});
            badge.textContent = data.online ? '● Conector en línea' : '○ Conector sin comunicación';
            if (busy && !data.job) {
                busy=false;button.disabled=false;
                message.textContent='La solicitud venció. Vuelve a calcular el reporte.';
            }
            if (busy && data.job) {
                const s=data.job.status;
                message.textContent=s==='pending'?'Solicitud pendiente. Esperando al conector…':s==='running'?'Consultando la base local…':s==='completed'?'Reporte recibido correctamente.':data.job.error;
                if(s==='completed')render(data.job);
                if(s==='completed'||s==='failed'){busy=false;button.disabled=false;}
            }
            if(busy && !data.online)message.textContent='Solicitud guardada. Esperando que el conector vuelva a estar disponible.';
        } catch (e) {message.textContent=e.message;} finally {polling=false;}
    }
    document.getElementById('request').addEventListener('submit',async e=>{
        e.preventDefault();button.disabled=true;exportButton.disabled=true;
        try {
            const data=await call('enqueue',{inicio:document.getElementById('inicio').value,fin:document.getElementById('fin').value});
            job=data.id;busy=true;message.textContent='Solicitud enviada al conector.';await poll();
        } catch(e) {message.textContent=e.message;button.disabled=false;}
    });
    exportButton.addEventListener('click',()=>{
        if(!rows.length)return;
        const names=Object.keys(rows[0]);
        const escape=value=>{
            let text=String(value??'');
            if(/^[=+@\-\t\r]/.test(text))text="'"+text;
            return '"'+text.replace(/"/g,'""')+'"';
        };
        const csv=[names,...rows.map(row=>names.map(name=>row[name]))].map(row=>row.map(escape).join(',')).join('\r\n');
        const url=URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}));
        const link=document.createElement('a');link.href=url;link.download='utilidad-documentos.csv';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
    poll();setInterval(poll,3000);
}
