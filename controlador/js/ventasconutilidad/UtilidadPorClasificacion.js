function InicializarUtilidadPorClasificacion() {
    'use strict';
    var filtrosCalculados = null;
    var ocupado = false;
    var url = 'controlador/ventasconutilidad/UtilidadPorClasificacionControlador.php';
    var escape = $.fn.dataTable.render.text();
    var formato = new Intl.NumberFormat('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function FormatearNumero(value, type) {
        return type === 'display' ? formato.format(Number(value)) : Number(value);
    }

    function FormatearTexto(value, type) {
        return type === 'display' ? escape.display(value || '') : (value || '');
    }

    function MostrarAviso(message) {
        Swal.fire('Utilidad por clasificación', message, 'warning');
    }

    function CargarAutocompletado(selector, accion, codigo, nombre) {
        var input = $(selector);
        input.on('input', function () {
            input.removeData('codigo').removeData('seleccion');
        });
        $.ajax({
            url: 'controlador/ConceptosReutilizablesControlador.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accionajax: accion
            }
        }).done(function (rows) {
            input.autocomplete({
                minLength: 0,
                source: function (request, response) {
                    var term = request.term.toLocaleLowerCase();
                    response(rows.filter(function (row) {
                        return (row[codigo] + ' ' + row[nombre]).toLocaleLowerCase().indexOf(term) !== -1;
                    }).slice(0, 20).map(function (row) {
                        return {
                            label: row[codigo] + ': ' + row[nombre],
                            value: row[nombre],
                            codigo: row[codigo]
                        };
                    }));
                },
                select: function (event, ui) {
                    input.val(ui.item.value).data('codigo', ui.item.codigo).data('seleccion', ui.item.value);
                    return false;
                }
            }).on('focus', function () {
                input.autocomplete('search', input.val());
            });
        }).fail(function () {
            MostrarAviso('No se pudo cargar el catálogo de agentes o clientes. Recarga la página.');
        });
    }
    CargarAutocompletado('#inp_filter_agent', 'BuscarFiltroAutocompletadoAgenteInput', 'CCODIGOAGENTE', 'CNOMBREAGENTE');
    CargarAutocompletado('#inp_filter_client', 'BuscarFiltroAutocompletadoClienteInput', 'CCODIGOCLIENTE', 'CRAZONSOCIAL');
    $('#modo').on('change', function () {
        $('#filtro_agente').toggle(this.value !== '2');
        $('#filtro_cliente').toggle(this.value !== '1');
    });
    $('#frm_principal').on('submit', function (event) {
        event.preventDefault();
    });

    function ObtenerFiltrosFormulario() {
        var result = {
        };
        $('#frm_principal').serializeArray().forEach(function (field) {
            result[field.name] = field.value;
        });
        if (!result.startdate || !result.endate || result.startdate > result.endate) {
            throw new Error('Selecciona un rango de fechas válido.');
        }
        ['agente','cliente'].forEach(function (key) {
            var input = $(key === 'agente' ? '#inp_filter_agent' : '#inp_filter_client');
            var activo = key === 'agente' ? result.modo !== '2' : result.modo !== '1';
            result[key] = '';
            if (activo && input.val().trim()) {
                if (input.data('seleccion') !== input.val()) {
                    throw new Error('Selecciona el '+key+' de las sugerencias.');
                }
                result[key] = input.data('codigo');
            }
        });
        var conceptos = $('#tbody_tblconcepts input:checkbox:checked').map(function () {
            return this.name;
        }).get();
        if (!conceptos.length) {
            throw new Error('Selecciona al menos un concepto.');
        }
        result.checkboxValues = JSON.stringify(conceptos);
        $('#ModalClasificacionesClients select, #ModalClasificacionesAgent select').each(function () {
            result[this.id] = JSON.stringify($(this).val() || []);
        });
        result.servicios = $('#servicios').prop('checked') ? '1' : '0';
        result.ceros = $('#ceros').prop('checked') ? '1' : '0';
        return result;
    }

    function ActualizarTotales(rows, prefix) {
        var total = {
            Ventas: 0,
            Descuento: 0,
            Costo: 0,
            Utilidad: 0
        };
        rows.forEach(function (row) {
            Object.keys(total).forEach(function (key) {
                total[key] += Number(row[key]);
            });
        });
        $('#' + prefix + 'filas').text('Filas: ' + rows.length);
        ['ventas','descuento','costo','utilidad'].forEach(function (key) {
            var title = key.charAt(0).toUpperCase() + key.slice(1);
            $('#' + prefix + key).text(title + ': $' + formato.format(total[title]));
        });
        $('#' + prefix + 'margen').text('Margen: ' + formato.format(total.Ventas === 0 ? 0 : total.Utilidad / total.Ventas * 100) + '%');
    }

    function MostrarTabla(selector, rows, columns, titulo, filtrosPdf) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }
        $(selector + ' tbody').empty();
        var botones = [{
            extend:'excelHtml5',
            title: titulo
        }
        ];
        botones.push({
            text: 'PDF',
            action: function () {
                if (ocupado) {
                    return;
                }
                ocupado = true;
                $('#loading-screen').show();
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: filtrosPdf,
                    xhrFields:{
                        responseType:'blob'
                    }
                })
                .done(function (blob) {
                    var link = document.createElement('a');
                    var objectUrl = URL.createObjectURL(blob);
                    link.href = objectUrl;
                    link.download = 'UtilidadPorClasificacion.pdf';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(function () {
                        URL.revokeObjectURL(objectUrl);
                    }, 1000);
                }).fail(function () {
                    MostrarAviso('No se pudo generar el PDF. Revisa la conexión e intenta nuevamente.');
                })
                .always(function () {
                    ocupado = false;
                    $('#loading-screen').hide();
                });
            }
        });
        return $(selector).DataTable({
            data: rows,
            columns: columns,
            ordering:false,
            language:{
                url: 'vista/vendor/datatables/es-MX.json'
            },
            pageLength:100,
            lengthMenu:[100,500,1000,2000],
            dom:'lBfrtip',
            buttons: botones,
            createdRow:function (tr, row) {
                if (row.Subtotal) {
                    $(tr).addClass('font-weight-bold table-info');
                }
                else {
                    $(tr).css('cursor', 'pointer');
                }
            }
        });
    }

    function ColumnaNumerica(key) {
        return {
            data: key,
            render: FormatearNumero,
            className: 'text-right'
        };
    }

    function ConsultarReporte(data, done) {
        if (ocupado) {
            return;
        }
        ocupado = true;
        $('#loading-screen').show();
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: data
        }).done(done).fail(function (xhr) {
            MostrarAviso(xhr.responseJSON && xhr.responseJSON.error || 'No se pudo cargar el reporte. Intenta nuevamente.');
        }).always(function () {
            ocupado = false;
            $('#loading-screen').hide();
        });
    }
    $('.BtnCalcularReportePrincipal').on('click', function () {
        var data;
        try {
            data = ObtenerFiltrosFormulario();
        }
        catch (e) {
            MostrarAviso(e.message);
            return;
        }
        ConsultarReporte($.extend({
        }, data, {
            accionajax: 'Consultar'
        }), function (response) {
            filtrosCalculados = $.extend({
            }, data);
            var rows = [],
            grupo = null,
            suma = 0,
            contador = 0;
            function subtotal() {
                if (grupo !== null) {
                    rows.push({
                        Numero: '',
                        Clasificacion:'Total del grupo',
                        Agente: '',
                        Cliente: '',
                        Ventas: '',
                        Descuento: '',
                        Costo: '',
                        Utilidad: '',
                        Margen: '',
                        TotalVentas: suma,
                        Subtotal: true
                    });
                }
            }
            response.filas.forEach(function (row) {
                var key = JSON.stringify([row.IdAgente || 0, row.IdCliente || 0]);
                if (grupo !== key) {
                    subtotal();
                    grupo = key;
                    suma = 0;
                }
                suma += Number(row.Ventas);
                rows.push($.extend({
                    Agente: '',
                    Cliente: ''
                }, row, {
                    Numero: ++contador,
                    TotalVentas: ''
                }));
            });
            subtotal();
            function importe(key) {
                return {
                    data: key,
                    className: 'text-right',
                    render: function (value, type) {
                        return value === '' ? '' : FormatearNumero(value, type);
                    }
                };
            }
            MostrarTabla('#tbl_reporteprincipal', rows, [{
                data: 'Numero'
            }, {
                data: 'Clasificacion',
                render: FormatearTexto
            },
            {
                data: 'Agente',
                render: FormatearTexto
            }, {
                data: 'Cliente',
                render: FormatearTexto
            }, importe('Ventas'), importe('Descuento'),
            importe('Costo'), importe('Utilidad'), importe('Margen'), importe('TotalVentas')],
            'Utilidad por clasificación del '+data.startdate+' al '+data.endate,
            $.extend({
            }, data, {
                accionajax: 'Pdf'
            }));
            ActualizarTotales(response.filas, 'total_');
            $('#totals').show();
        });
    });
    $('#tbody_tblreporteprincipal').on('click', 'tr', function () {
        if (!filtrosCalculados || ocupado) {
            return;
        }
        var row = $('#tbl_reporteprincipal').DataTable().row(this).data();
        if (!row || row.Subtotal) {
            return;
        }
        var data = $.extend({
        }, filtrosCalculados, {
            accionajax: 'Productos',
            valor: row.Valor,
            idagente: row.IdAgente || 0,
            idcliente: row.IdCliente || 0
        });
        ConsultarReporte(data, function (response) {
            var title = [row.Clasificacion, row.Agente, row.Cliente].filter(Boolean).join(' / ');
            $('#dataValues').text(title);
            var rows = response.filas.map(function (item, index) {
                return $.extend({
                    Numero: index+1
                }, item);
            });
            MostrarTabla('#tbl_SoloProductos', rows, [{
                data: 'Numero'
            }, {
                data: 'Codigo',
                render: FormatearTexto
            }, {
                data: 'Producto',
                render: FormatearTexto
            },
            ColumnaNumerica('Unidades'), ColumnaNumerica('Ventas'), ColumnaNumerica('Descuento'), ColumnaNumerica('Costo'), ColumnaNumerica('Utilidad'), ColumnaNumerica('Margen')],
            'Productos - '+title+' del '+data.startdate+' al '+data.endate,
            $.extend({
            }, data, {
                accionajax: 'PdfProductos'
            }));
            ActualizarTotales(rows, 'unitpro_total_');
            $('#Mdl_UtilidadPorClasificacionProductos').modal('show');
        });
    });
}
