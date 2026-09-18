$(function () {
    'use strict';

    var url = 'controlador/compras/MargenesProteccionControlador.php';
    var ocupado = false;
    var filtrosCalculados = null;
    var vistaPrevia = null;
    var textoSeguro = $.fn.dataTable.render.text();
    var formato = new Intl.NumberFormat('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function MostrarError(mensaje) {
        Swal.fire('Márgenes de protección', mensaje, 'error');
    }

    function HabilitarBotones() {
        var sinFecha = !$('#endate').val();
        $('#calcular_margenes, #preparar_ceros').prop('disabled', ocupado || sinFecha);
        $('#preparar_actualizacion').prop('disabled', ocupado || sinFecha);
        $('#aplicar_margenes').prop('disabled', ocupado || !vistaPrevia);
        $('#ModalCambiosMargenes [data-dismiss="modal"]').prop('disabled', ocupado);
    }

    function Solicitar(data, terminado) {
        if (ocupado) {
            return;
        }
        ocupado = true;
        HabilitarBotones();
        $('#loading-screen').show();
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: data
        }).done(function (respuesta) {
            terminado(respuesta);
        }).fail(function (xhr) {
            MostrarError(xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error : 'No se pudo completar la solicitud.');
        }).always(function () {
            ocupado = false;
            $('#loading-screen').hide();
            HabilitarBotones();
        });
    }

    function ObtenerFiltros() {
        var data = {};
        $('#frm_margenes').serializeArray().forEach(function (campo) {
            data[campo.name] = campo.value;
        });
        if (!data.endate) {
            throw new Error('Selecciona la fecha de corte.');
        }
        [
            ['#producto_inicial', 'startproductval'],
            ['#producto_final', 'endproductval']
        ].forEach(function (campo) {
            var input = $(campo[0]);
            data[campo[1]] = '';
            if (input.val().trim()) {
                if (input.data('seleccion') !== input.val()) {
                    throw new Error('Selecciona el producto de las sugerencias.');
                }
                data[campo[1]] = String(input.data('codigo'));
            }
        });
        $('#ModalClasificaciones select').each(function () {
            data[this.id] = JSON.stringify($(this).val() || []);
        });
        return data;
    }

    function ColumnaNumero(campo) {
        return {
            data: campo,
            className: 'text-right',
            render: function (valor, tipo) {
                return tipo === 'display' ? formato.format(valor) : valor;
            }
        };
    }

    function ColumnaTexto(campo) {
        return {
            data: campo,
            render: textoSeguro
        };
    }

    function DescargarPdf(data) {
        if (ocupado) {
            return;
        }
        ocupado = true;
        HabilitarBotones();
        $('#loading-screen').show();
        $.ajax({
            url: url,
            method: 'POST',
            data: $.extend({}, data, {accionajax: 'PdfMargenes'}),
            xhrFields: {responseType: 'blob'}
        }).done(function (archivo) {
            var enlace = document.createElement('a');
            var direccion = URL.createObjectURL(archivo);
            enlace.href = direccion;
            enlace.download = 'MargenesProteccion.pdf';
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
            setTimeout(function () {
                URL.revokeObjectURL(direccion);
            }, 1000);
        }).fail(function () {
            MostrarError('No se pudo generar el PDF.');
        }).always(function () {
            ocupado = false;
            $('#loading-screen').hide();
            HabilitarBotones();
        });
    }

    function MostrarReporte(filas, filtros) {
        if ($.fn.DataTable.isDataTable('#tbl_margenes')) {
            $('#tbl_margenes').DataTable().destroy();
        }
        $('#tbl_margenes tbody').empty();
        filas.forEach(function (fila, indice) {
            fila.Numero = indice + 1;
        });
        $('#tbl_margenes').DataTable({
            data: filas,
            columns: [
                {data: 'Numero'},
                ColumnaTexto('Codigo'),
                ColumnaTexto('Producto'),
                ColumnaTexto('Unidad'),
                ColumnaNumero('Precio'),
                ColumnaNumero('Costo'),
                ColumnaNumero('MargenActual'),
                ColumnaNumero('MargenRecomendado'),
                ColumnaTexto('Estatus'),
                ColumnaNumero('Precio1'),
                ColumnaNumero('Precio2'),
                ColumnaNumero('Precio3'),
                ColumnaNumero('Precio4'),
                ColumnaNumero('Precio5'),
                ColumnaNumero('Existencia')
            ],
            language: {url: 'vista/vendor/datatables/es-MX.json'},
            pageLength: 100,
            lengthMenu: [100, 500, 1000, 2000],
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Márgenes de protección - ' + filtros.endate + ' - Lista ' + filtros.cbx_listprice
                },
                {
                    text: 'PDF',
                    action: function () {
                        DescargarPdf(filtros);
                    }
                }
            ],
            createdRow: function (tr, fila) {
                var colores = {
                    'REVISIÓN': 'table-warning',
                    'REQUIERE BAJAR': 'table-primary',
                    'REQUIERE SUBIR': 'table-success'
                };
                $(tr).addClass(colores[fila.Estatus] || '');
            }
        });
        var revisiones = filas.filter(function (fila) {
            return fila.Estatus === 'REVISIÓN';
        }).length;
        $('#resumen_margenes').text('Productos: ' + filas.length + ' | Requieren revisión: ' + revisiones);
    }

    $('#endate').on('change', HabilitarBotones);
    $('#frm_margenes').on('submit', function (event) {
        event.preventDefault();
        var filtros;
        try {
            filtros = ObtenerFiltros();
        } catch (error) {
            MostrarError(error.message);
            return;
        }
        Solicitar($.extend({}, filtros, {accionajax: 'ConsultarMargenes'}), function (respuesta) {
            filtrosCalculados = $.extend({}, filtros);
            MostrarReporte(respuesta.filas, filtrosCalculados);
        });
    });

    function PrepararCambios(accion) {
        var data;
        try {
            data = ObtenerFiltros();
        } catch (error) {
            MostrarError(error.message);
            return;
        }
        data.accionajax = accion;
        Solicitar(data, function (respuesta) {
            vistaPrevia = respuesta.filas.length ? respuesta.vista : null;
            $('#alcance_cambios').text(accion === 'PrepararCeros'
                ? 'Se pondrá en cero el margen de los productos que cumplen los filtros actuales del formulario.'
                : 'Se actualizarán los productos que cumplen el rango, las clasificaciones y la selección de inactivos del formulario. El cálculo usa la lista 4 y el corte de hoy; suprimir ceros se aplica al precio de la lista 4.');
            $('#cantidad_cambios').text('Productos con cambios: ' + respuesta.filas.length);
            if ($.fn.DataTable.isDataTable('#tbl_cambios_margenes')) {
                $('#tbl_cambios_margenes').DataTable().destroy();
            }
            $('#tbl_cambios_margenes tbody').empty();
            $('#tbl_cambios_margenes').DataTable({
                data: respuesta.filas,
                columns: [
                    ColumnaTexto('Codigo'),
                    ColumnaTexto('Producto'),
                    ColumnaNumero('Precio'),
                    ColumnaNumero('Costo'),
                    ColumnaNumero('MargenActual'),
                    ColumnaNumero('MargenNuevo')
                ],
                language: {url: 'vista/vendor/datatables/es-MX.json'},
                pageLength: 100
            });
            $('#ModalCambiosMargenes').modal('show');
        });
    }

    $('#preparar_ceros').on('click', function () {
        PrepararCambios('PrepararCeros');
    });
    $('#preparar_actualizacion').on('click', function () {
        PrepararCambios('PrepararActualizacion');
    });
    $('#aplicar_margenes').on('click', function () {
        if (!vistaPrevia) {
            return;
        }
        Solicitar({
            accionajax: 'AplicarMargenes',
            vista: vistaPrevia,
            csrf: $('#frm_margenes input[name="csrf"]').val()
        }, function (respuesta) {
            vistaPrevia = null;
            $('#ModalCambiosMargenes').modal('hide');
            if ($.fn.DataTable.isDataTable('#tbl_margenes')) {
                $('#tbl_margenes').DataTable().clear().draw();
            }
            filtrosCalculados = null;
            $('#resumen_margenes').text('Los márgenes cambiaron. Pulsa Calcular para consultar los valores actualizados.');
            Swal.fire('Actualización completa', 'Productos actualizados: ' + respuesta.actualizados, 'success');
        });
    });

    $.ajax({
        url: 'controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        dataType: 'json',
        data: {accionajax: 'BuscarFiltroAutocompletadoProductoInput'}
    }).done(function (productos) {
        $('#producto_inicial, #producto_final').each(function () {
            var input = $(this);
            input.on('input', function () {
                input.removeData('codigo').removeData('seleccion');
            });
            input.autocomplete({
                minLength: 0,
                source: function (request, response) {
                    var busqueda = request.term.toLocaleLowerCase();
                    response(productos.filter(function (producto) {
                        return (producto.CCODIGOPRODUCTO + ' ' + producto.CNOMBREPRODUCTO)
                            .toLocaleLowerCase().indexOf(busqueda) !== -1;
                    }).slice(0, 20).map(function (producto) {
                        return {
                            label: producto.CCODIGOPRODUCTO + ': ' + producto.CNOMBREPRODUCTO,
                            value: producto.CNOMBREPRODUCTO,
                            codigo: producto.CCODIGOPRODUCTO
                        };
                    }));
                },
                select: function (event, ui) {
                    input.val(ui.item.value).data('codigo', ui.item.codigo).data('seleccion', ui.item.value);
                    return false;
                }
            }).on('focus click', function () {
                input.autocomplete('search', input.val());
            });
            if (input.is(':focus')) input.autocomplete('search', input.val());
        });
    }).fail(function () {
        MostrarError('No se pudo cargar el catálogo de productos. Recarga la página.');
    });
    HabilitarBotones();
});
