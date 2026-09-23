$(function () {
    'use strict';
    var url = 'controlador/inventario/InventarioControlador.php';
    var csrf = $('#frm_inventario [name=csrf]').val();
    var ocupado = false, catalogosListos = false;
    var filtrosCalculados = null;
    var formato = new Intl.NumberFormat('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 4});
    var texto = $.fn.dataTable.render.text();
    function Solicitar(accion, data) {
        return $.ajax({url: url, method: 'POST', dataType: 'json', data: $.extend({}, data, {accionajax: accion, csrf: csrf})});
    }
    function ErrorSolicitud(xhr) {
        Swal.fire('Inventario', xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo completar la consulta.', 'error');
    }
    function Boton() { $('#inventario_calcular').prop('disabled', ocupado || !catalogosListos || !$('#inventario_corte').val()); }
    function Numero(campo) {
        return {data: campo, className: 'text-right', render: function (valor, tipo) {
            return tipo === 'display' ? (valor === null ? '—' : formato.format(valor)) : valor;
        }};
    }
    var columnas = [{data: null, render: function (data, tipo, fila, meta) { return meta.row + 1; }}];
    ['Codigo', 'Producto', 'Unidad'].forEach(function (campo) { columnas.push({data: campo, defaultContent: '', render: texto}); });
    columnas.push(Numero('Impuesto'));
    ['Familia', 'Detallada'].forEach(function (campo) { columnas.push({data: campo, defaultContent: '', render: texto}); });
    ['Existencia', 'Costo', 'Minimo', 'Maximo', 'ReordenMin', 'ReordenMax'].forEach(function (campo) { columnas.push(Numero(campo)); });
    for (var i = 1; i <= 10; i++) { columnas.push(Numero('Precio' + i)); }
    var tabla = $('#tbl_inventario').DataTable({
        data: [], columns: columnas,
        pageLength: 100, order: [[1, 'asc']], dom: 'lBf<"inventario-scroll"t>rip',
        language: {url: 'vista/vendor/datatables/es-MX.json'},
        buttons: [{extend: 'excelHtml5', title: function () { return 'Inventario - ' + (tabla.fecha || ''); }, exportOptions: {orthogonal: 'export', escapeExcelFormula: true}},
            {text: 'PDF', action: function (event, dt, node) {
                if (!filtrosCalculados) { Swal.fire('Inventario', 'Calcula el reporte antes de exportarlo.', 'info'); return; }
                var boton = $(node).prop('disabled', true).text('Generando…');
                fetch(url, {method: 'POST', body: new URLSearchParams($.extend({}, filtrosCalculados, {accionajax: 'ExportarPdf', csrf: csrf}))})
                    .then(function (respuesta) {
                        if (!respuesta.ok) { return respuesta.json().then(function (data) { throw new Error(data.error || 'No se pudo generar el PDF.'); }); }
                        return respuesta.blob();
                    }).then(function (archivo) {
                        var enlace = document.createElement('a'), direccion = URL.createObjectURL(archivo);
                        enlace.href = direccion; enlace.download = 'Inventario-' + tabla.fecha + '.pdf';
                        document.body.appendChild(enlace); enlace.click(); enlace.remove();
                        setTimeout(function () { URL.revokeObjectURL(direccion); }, 1000);
                    }).catch(function (error) { Swal.fire('Inventario', error.message, 'error'); })
                    .finally(function () { boton.prop('disabled', false).text('PDF'); });
            }}]
    });
    Solicitar('Catalogos').done(function (data) {
        data.almacenes.forEach(function (a) { $('#inventario_almacenes').append(new Option(a.Codigo + ' - ' + a.Nombre, a.Id)); });
        data.clasificaciones.forEach(function (c) { $('#inventario_clasificacion' + c.Numero).append(new Option(c.Nombre, c.Id)); });
        catalogosListos = true; ResumenFiltros(); Boton();
    }).fail(function (xhr) { $('#inventario_filtros').text('No se pudieron cargar los filtros. Recarga la página.'); ErrorSolicitud(xhr); });
    function ResumenFiltros() {
        var almacenes = ($('#inventario_almacenes').val() || []).length;
        var clasificaciones = 0;
        for (var n = 1; n <= 6; n++) { clasificaciones += ($('#inventario_clasificacion' + n).val() || []).length; }
        $('#inventario_filtros').text('Almacenes: ' + (almacenes || 'todos') + ' · Clasificaciones seleccionadas: ' + clasificaciones);
    }
    $('[id^=inventario_clasificacion], #inventario_almacenes').on('change', ResumenFiltros);
    $('.limpiar-inventario').on('click', function () { $('#' + $(this).data('select')).val([]).trigger('change'); });
    $('#inventario_corte').on('change', Boton);
    $('#inventario_inicial, #inventario_final').each(function () {
        $(this).autocomplete({minLength: 0, delay: 200, source: function (request, response) {
            Solicitar('Productos', {term: request.term}).done(function (data) {
                response(data.map(function (p) { return {label: p.Codigo + ' - ' + p.Producto, value: p.Codigo}; }));
            }).fail(function (xhr) {response([]); ErrorSolicitud(xhr);});
        }}).on('focus', function () { $(this).autocomplete('search', this.value); });
    });
    $('#frm_inventario').on('submit', function (event) {
        event.preventDefault(); if (ocupado || !catalogosListos) { return; }
        var filtros = {};
        $(this).serializeArray().forEach(function (campo) { filtros[campo.name] = campo.value; });
        filtros.almacenes = JSON.stringify($('#inventario_almacenes').val() || []);
        for (var n = 1; n <= 6; n++) { filtros['clasificacion' + n] = JSON.stringify($('#inventario_clasificacion' + n).val() || []); }
        ocupado = true; Boton(); $('#inventario_calcular').text('Consultando…'); $('#loading-screen').show();
        Solicitar('ConsultarInventario', filtros).done(function (data) {
            tabla.clear().rows.add(data.filas).draw(); tabla.columns.adjust(); tabla.fecha = data.fecha;
            filtrosCalculados = $.extend({}, filtros);
            $('#inventario_resumen').text('Corte: ' + data.fecha + ' · Productos: ' + data.filas.length);
            EnfocarTablaReporte('#tbl_inventario');
        }).fail(ErrorSolicitud).always(function () { ocupado = false; Boton(); $('#inventario_calcular').text('Calcular'); $('#loading-screen').hide(); });
    });
});
