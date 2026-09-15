function EjecutarConsultaUtilidadPorProductos() {
$.ajax({
        url: 'Controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: {accionajax: "BuscarFiltroAutocompletadoAgenteInput"},
        dataType: 'json',
        success: function(response1) {
 $.ajax({
    url: 'Controlador/ConceptosReutilizablesControlador.php',
    method: 'POST',
    data: {accionajax: "BuscarFiltroAutocompletadoProductoInput"},
    dataType: 'json',
    success: function(response) {
        $(document).on('click', '.BtnCalcularReportePrincipal', function() {
            //codigo que ejecuta el loader
            var screen = $('#loading-screen');
            screen.fadeIn();

            var datos = response.map(function(item) {
                return { id: item.CIDPRODUCTO, label: item.CCODIGOPRODUCTO, nombre: item.CNOMBREPRODUCTO };
            });
            var inp_filter_startproduct = $("#inp_filter_startproduct");
            var inp_filter_endproduct = $("#inp_filter_endproduct");
            var endproductlabel = $("#endproduct");
            var startproductlabel = $("#startproduct");
            var productstart;
            var productend;
            var nameproduct = '';
            productstart = "0";
            productend = "0";
            nameproduct = '';

            //SE ESTA VERIFICANDO QUE LOS CODIGOS AGREGADOS EXISTAN SINO  AGREGA MENSAJES PARA ESPECIFICAR QUE NO EXISTEN
            for (var i = 0; i < datos.length; i++) {
                if (datos[i].label === inp_filter_startproduct.val()) {
                    productstart = datos[i].id;
                    nameproduct = datos[i].nombre;
                }
                if (datos[i].label === inp_filter_endproduct.val()) {
                    productend = datos[i].id;
                    nameproduct = datos[i].nombre;
                }


            }

            var datos1 = response1.map(function(item) {
                return {id: item.CCODIGOAGENTE, label: item.CNOMBREAGENTE };
                });
                var inp_filter_tagent = $("#inp_filter_startagent");
                var agentlabel = $("#startagent");
                var agent;
                var nameagent = '';
                agent = '0';

                //SE ESTA VERIFICANDO QUE LOS CODIGOS AGREGADOS EXISTAN SINO  AGREGA MENSAJES PARA ESPECIFICAR QUE NO EXISTEN
            for (var i = 0; i < datos1.length; i++) {
                if (datos1[i].label === inp_filter_tagent.val()) {
                    agent = datos1[i].id;
                    nameagent = datos1[i].nombre;
                    $(agentlabel).html(nameagent);
                }
            }

            if (agent == '0') {
                $(agentlabel).html("No existe el Agente");
            } 

            if (productstart == '0') {
                $(startproductlabel).html("No existe el producto, Se agregará el mínimo");
            } else {
                $(startproductlabel).html(nameproduct);
            }



            if (productend == '0') {
                $(endproductlabel).html("No existe el producto, Se agregará el Máximo");
            } else {
                $(endproductlabel).html(nameproduct);
            }  

            // Obtener los datos del formulario en formato de matriz de objetos
            var formData = $("#frm_utilidadporproductos").serializeArray();


            //LEEMOS LOS SELECTS MULTIPLES Y LO METEMOS AL ARRAY
            var cbx_LineGeneral = [];
            $("#cbx_LineGeneral option:selected").each(function() {
                cbx_LineGeneral.push($(this).val());
            });
            formData.push({ name: "cbx_LineGeneral", value: JSON.stringify(cbx_LineGeneral) });

            var cbx_LineDetailed = [];
            $("#cbx_LineDetailed option:selected").each(function() {
                cbx_LineDetailed.push($(this).val());
            });
            formData.push({ name: "cbx_LineDetailed", value: JSON.stringify(cbx_LineDetailed) });

            var cbx_CommissionIndicator = [];
            $("#cbx_CommissionIndicator option:selected").each(function() {
                cbx_CommissionIndicator.push($(this).val());
            });
            formData.push({ name: "cbx_CommissionIndicator", value: JSON.stringify(cbx_CommissionIndicator) });

            var cbx_TypeClassification = [];
            $("#cbx_TypeClassification option:selected").each(function() {
                cbx_TypeClassification.push($(this).val());
            });
            formData.push({ name: "cbx_TypeClassification", value: JSON.stringify(cbx_TypeClassification) });

            var cbx_Rotation = [];
            $("#cbx_Rotation option:selected").each(function() {
                cbx_Rotation.push($(this).val());
            });
            formData.push({ name: "cbx_Rotation", value: JSON.stringify(cbx_Rotation) });

            var cbx_DailyReview = [];
            $("#cbx_DailyReview option:selected").each(function() {
                cbx_DailyReview.push($(this).val());
            });
            formData.push({ name: "cbx_DailyReview", value: JSON.stringify(cbx_DailyReview) });

            var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
            var checkboxValues = [];

            //aqui se sacan los conceptos seleccionados con anterioridad
            checkboxes.each(function() {
                var checkboxValue = $(this).attr("name");
                checkboxValues.push(checkboxValue);
            });

            
            // Agregar la variable checkboxValues a los datos del formulario
            formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });
            formData.push({ name: "accionajax", value: "InsertarTblUtilidadPorProductos" });

            if (productstart == 0) {
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "startproductval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = "0"; // Cambiar el valor del campo a "0"
                }
            }

            if (productend == 0) {
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "endproductval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = "0"; // Cambiar el valor del campo a "0"
                }
            }

            if (agent == 0) {
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "agentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = "0"; // Cambiar el valor del campo a "0"
                }
            }else{
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "agentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = agent;
                }
            }


            $.ajax({
                url: "controlador/ventasconutilidad/UtilidadPorProductosControlador.php",
                method: "POST",
                data: formData,
                success: function(response) {
                    console.log(response);
                    // Manejar la respuesta de la solicitud AJAX
                    // SE AGREGAN LOS DATOS OBTENIDOS A LA TABLA
                    if ($.fn.DataTable.isDataTable('#tbl_reporteprincipal')) {
                        $('#tbl_reporteprincipal').DataTable().destroy();
                    }
                    $('#tbody_tblreporteprincipal').html(response);
                    $('#tbl_reporteprincipal').DataTable({
                        language: {
                            url: 'vista/vendor/datatables/es-MX.json'
                        },
                        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>Brtp',
                        pageLength : 100,
                        lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                        buttons: [{
                            extend: 'excel',
                            title: 'Utilidad por Productos del '+$('#startdate').val()+' hasta: '+$('#endate').val(),
                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row:first c', sheet).attr('s', '2');
                            }
                        },
                        {
                            text: 'PDF',
                            action: function() {
                                // Llamar a tu función para generar el PDF
                                GeneratePdf('controlador/ventasconutilidad/UtilidadPorProductosControlador.php','Utilidad Por Productos Del '+$('#startdate').val()+' hasta: '+$('#endate').val(),'GenerarPdfUtilidadPorProductos',formData);
                            
                        }
                        }]
                    });
                    // Realizar la suma de los valores de las scolumnass
                    var sumatotal_pesotn = 0;
                    var sumatotal_descuento = 0;
                    var sumatotal_utilidad = 0;
                    var sumatotal_costo = 0;
                    var sumatotal_margen = 0;
                    var sumatotal_ventas = 0;

                    $('#tbody_tblreporteprincipal tr').each(function() {
                        var total_ventas = parseFloat($(this).find('td:nth-child(5)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_ventas)) {
                            sumatotal_ventas += total_ventas;
                        }
                        var total_descuento = parseFloat($(this).find('td:nth-child(6)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_descuento)) {
                            sumatotal_descuento += total_descuento;
                        }
                        var total_utilidad = parseFloat($(this).find('td:nth-child(8)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_utilidad)) {
                            sumatotal_utilidad += total_utilidad;
                        }
                        var total_costo = parseFloat($(this).find('td:nth-child(7)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_costo)) {
                            sumatotal_costo += total_costo;
                        }

                    });
                    var numeroFilas = $('#tbody_tblreporteprincipal tr').length;
                    sumatotal_margen=((sumatotal_utilidad*100)/sumatotal_ventas);
            // Mostrar el resultado en la etiqueta de totales
                    $('#total_filas').text("Filas: " + numeroFilas);
                    $('#total_ventas').text("Neto:" + currencyFormatter(sumatotal_ventas));
                    $('#total_descuento').text("Descuento:" + currencyFormatter(sumatotal_descuento));
                    $('#total_utilidad').text("Utilidad:" + currencyFormatter(sumatotal_utilidad));
                    $('#total_costo').text("Costo:" + currencyFormatter(sumatotal_costo));
                    $('#total_margen').text("Margen: " + sumatotal_margen.toFixed(2) + "%");
                    $("#totals").show();
                    screen.fadeOut();
                },
                error: function(xhr, status, error) {
        // Manejar el error de la solicitud AJAX
                    screen.fadeOut();
                    console.error(error);
                }
            });


});
}
});
}})
}




//funcion que saca los data-? que se tengan en el tr de su respetivo documento 
function EjecutarSubConsultaUtilidadPorProductos() {

    $(document).on('click', '.btnreportutilityproductonly', function() {
        var Codigo = $(this).find('td:nth-child(2)').text();
         //codigo que ejecuta el loader        
        var screen = $('#loading-screen');
        screen.fadeIn();
        //saca los datos de los data y los imprime en el head del modal
        $('#dataValues').text("Codigo: " + Codigo);

        var formData = $("#frm_utilidadporproductos").serializeArray();
        var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
        var checkboxValues = [];

        //aqui se sacan los conceptos seleccionados con anterioridad
        checkboxes.each(function() {
            var checkboxValue = $(this).attr("name");
            checkboxValues.push(checkboxValue);
        });
        formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });

        formData.push({ name: "Codigo", value: Codigo });
        formData.push({ name: "accionajax", value: "EjecutarSubConsultaUtilidadPorProductos" });
        

        //ejecuta la funcion ajax para llamar cada uno de los registros
        $.ajax({
            url: "controlador/ventasconutilidad/UtilidadPorProductosControlador.php",
            method: "POST",
            data: formData,
            success: function(response) {

                if ($.fn.DataTable.isDataTable('#tbl_secundaria')) {
                    $('#tbl_secundaria').DataTable().destroy();
                }
                // Manejar la respuesta de la solicitud AJAX
                //SE AGREGAN LOS DATOS OBTENIDOS A LA TABLA
                $('#tbody_tblsecundaria').html(response);

                $('#tbl_secundaria').DataTable({
                    language: {
                        url: 'vista/vendor/datatables/es-MX.json'
                    },
                    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>Brtp',
                        pageLength : 100,
                        lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                    buttons: [
                    {
                        extend: 'excel',
                        title: 'Documentos del producto '+Codigo,
                        customize: function(xlsx) {
                          var sheet = xlsx.xl.worksheets['sheet1.xml'];
                          $('row:first c', sheet).attr('s', '2');
                      }
                  }
                  ]
                });
                
            // Realizar la suma de los valores de las scolumnass
                var sumatotal_ventas = 0;
                var sumatotal_descuento = 0;
                var sumatotal_utilidad = 0;
                var sumatotal_costo = 0;
                var sumatotal_margen = 0;
                var sumatotal_peso = 0;

                $('#tbody_tblsecundaria tr').each(function() {
                    var total_ventas = parseFloat($(this).find('td:nth-child(11)').text().replace(/[$,]/g, ''));
                    if (!isNaN(total_ventas)) {
                        sumatotal_ventas += total_ventas;
                    }
                    var total_descuento = parseFloat($(this).find('td:nth-child(12)').text().replace(/[$,]/g, ''));
                    if (!isNaN(total_descuento)) {
                        sumatotal_descuento += total_descuento;
                    }
                    var total_utilidad = parseFloat($(this).find('td:nth-child(14)').text().replace(/[$,]/g, ''));
                    if (!isNaN(total_utilidad)) {
                        sumatotal_utilidad += total_utilidad;
                    }
                    var total_costo = parseFloat($(this).find('td:nth-child(13)').text().replace(/[$,]/g, ''));
                    if (!isNaN(total_costo)) {
                        sumatotal_costo += total_costo;
                    }
                    var total_peso = parseFloat($(this).find('td:nth-child(16)').text().replace(/[$,]/g, ''));
                    if (!isNaN(total_peso)) {
                        sumatotal_peso += total_peso;
                    }
                });
                var numeroFilas = $('#tbody_tblsecundaria tr').length;
                sumatotal_margen=((sumatotal_utilidad*100)/sumatotal_ventas);
            // Mostrar el resultado en la etiqueta de totales
                $('#unit_total_filas').text("Filas: " + numeroFilas);
                $('#unit_total_ventas').text("Neto:" + currencyFormatter(sumatotal_ventas));
                $('#unit_total_descuento').text("Descuento:" + currencyFormatter(sumatotal_descuento));
                $('#unit_total_utilidad').text("Utilidad:" + currencyFormatter(sumatotal_utilidad));
                $('#unit_total_costo').text("Costo:" + currencyFormatter(sumatotal_costo));
                $('#unit_total_peso').text("Peso(Tn):" + currencyFormatWithoutSymbol(sumatotal_peso.toFixed(3)));
                $('#unit_total_margen').text("Margen: " + sumatotal_margen.toFixed(2) + "%");
                screen.fadeOut();
            },
            error: function(xhr, status, error) {
            // Manejar el error de la solicitud AJAX
                console.error(error);
                screen.fadeOut();
            }
        });
    });
}