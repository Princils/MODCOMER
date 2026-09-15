

function EjecutarConsultaUtilidadPorAgente() {
    $.ajax({
        url: 'Controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: {accionajax: "BuscarFiltroAutocompletadoAgenteInput"},
        dataType: 'json',
        success: function(response1) {
            $(document).on('click', '.BtnCalcularReportePrincipal', function() {
                //codigo que ejecuta el loader
                var screen = $('#loading-screen');
                screen.fadeIn();

                var agentmin = VerificarExistenciaAgentes(response1,"inp_filter_startagent","startagent","min");
                var agentmax = VerificarExistenciaAgentes(response1,"inp_filter_endagent","endagent","max");

                // Obtener los datos del formulario en formato de matriz de objetos
                var formData = $("#frm_principal").serializeArray();


                //LEEMOS LOS SELECTS MULTIPLES Y LO METEMOS AL ARRAY
                //**************************ARRAYS DE PRODUCTOS***********************************************************
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
                //**************************FIN ARRAYS DE PRODUCTOS***********************************************************
                
                //**************************ARRAYS DE AGENTE***********************************************************
                var cbxAgent1 = [];
                $("#cbxAgent1 option:selected").each(function() {
                    cbxAgent1.push($(this).val());
                });
                formData.push({ name: "cbxAgent1", value: JSON.stringify(cbxAgent1) });

                var agentClasification2 = [];
                $("#agentClasification2 option:selected").each(function() {
                    agentClasification2.push($(this).val());
                });
                formData.push({ name: "agentClasification2", value: JSON.stringify(agentClasification2) });

                var agentClasification3 = [];
                $("#agentClasification3 option:selected").each(function() {
                    agentClasification3.push($(this).val());
                });
                formData.push({ name: "agentClasification3", value: JSON.stringify(agentClasification3) });

                var agentClasification4 = [];
                $("#agentClasification4 option:selected").each(function() {
                    agentClasification4.push($(this).val());
                });
                formData.push({ name: "agentClasification4", value: JSON.stringify(agentClasification4) });

                var agentClasification5 = [];
                $("#agentClasification5 option:selected").each(function() {
                    agentClasification5.push($(this).val());
                });
                formData.push({ name: "agentClasification5", value: JSON.stringify(agentClasification5) });

                var agentClasification6 = [];
                $("#agentClasification6 option:selected").each(function() {
                    agentClasification6.push($(this).val());
                });
                formData.push({ name: "agentClasification6", value: JSON.stringify(agentClasification6) });
                //**************************FIN ARRAYS DE AGENTE***********************************************************
                //**************************ARRAYS DE CLIENTE***********************************************************
                var cbx_clas1client = [];
                $("#cbx_clas1client option:selected").each(function() {
                    cbx_clas1client.push($(this).val());
                });
                formData.push({ name: "cbx_clas1client", value: JSON.stringify(cbx_clas1client) });

                var cbx_typeclient = [];
                $("#cbx_typeclient option:selected").each(function() {
                    cbx_typeclient.push($(this).val());
                });
                formData.push({ name: "cbx_typeclient", value: JSON.stringify(cbx_typeclient) });

                var cbx_agent = [];
                $("#cbx_agent option:selected").each(function() {
                    cbx_agent.push($(this).val());
                });
                formData.push({ name: "cbx_agent", value: JSON.stringify(cbx_agent) });

                var cbx_zone = [];
                $("#cbx_zone option:selected").each(function() {
                    cbx_zone.push($(this).val());
                });
                formData.push({ name: "cbx_zone", value: JSON.stringify(cbx_zone) });

                var cbx_clas5client = [];
                $("#cbx_clas5client option:selected").each(function() {
                    cbx_clas5client.push($(this).val());
                });
                formData.push({ name: "cbx_clas5client", value: JSON.stringify(cbx_clas5client) });

                var cbx_clas6client = [];
                $("#cbx_clas6client option:selected").each(function() {
                    cbx_clas6client.push($(this).val());
                });
                formData.push({ name: "cbx_clas6client", value: JSON.stringify(cbx_clas6client) });

                //**************************FIN ARRAYS DE CLIENTE***********************************************************

                var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
                var checkboxValues = [];

                //aqui se sacan los conceptos seleccionados con anterioridad
                checkboxes.each(function() {
                    var checkboxValue = $(this).attr("name");
                    checkboxValues.push(checkboxValue);
                });


                // Agregar la variable checkboxValues a los datos del formulario
                formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });
                formData.push({ name: "accionajax", value: "InsertarTblUtilidadPorAgentes" });


            /********************PARA AGENTE MINIMO*************************************/
            if (agentmin == 0) {
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "startagentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = "0"; // Cambiar el valor del campo a "0"
                }
            }else{
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "startagentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = agentmin;
                }
            }

            /********************PARA FIN DE AGENTE MINIMO*************************************/
            /********************PARA AGENTE MAXIMO*************************************/
            if (agentmax == 0) {
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "endagentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = "0"; // Cambiar el valor del campo a "0"
                }
            }else{
                var fieldIndex = formData.findIndex(function(item) {
                    return item.name === "endagentval";
                });

                if (fieldIndex !== -1) {
                    formData[fieldIndex].value = agentmax;
                }
            }
            /********************PARA FIN DE AGENTE MAXIMO*************************************/

            console.log(formData);

            $.ajax({
                url: "controlador/ventasconutilidad/UtilidadPorAgenteControlador.php",
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
                            title: 'Utilidad por Agentes del '+$('#startdate').val()+' hasta: '+$('#endate').val(),
                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row:first c', sheet).attr('s', '2');
                            }
                        },
                        {
                            text: 'PDF',
                            action: function() {
                                // Llamar a tu función para generar el PDF
                                GeneratePdf('controlador/ventasconutilidad/UtilidadPorAgenteControlador.php','Utilidad Por Agentes Del '+$('#startdate').val()+' hasta: '+$('#endate').val(),'GenerarPdfUtilidadPorAgentes',formData);

                            }
                        }]
                    });
                    // Realizar la suma de los valores de las scolumnass
                    var sumatotal_descuento = 0;
                    var sumatotal_utilidad = 0;
                    var sumatotal_costo = 0;
                    var sumatotal_margen = 0;
                    var sumatotal_ventas = 0;

                    $('#tbody_tblreporteprincipal tr').each(function() {
                        var total_ventas = parseFloat($(this).find('td:nth-child(4)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_ventas)) {
                            sumatotal_ventas += total_ventas;
                        }
                        var total_descuento = parseFloat($(this).find('td:nth-child(5)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_descuento)) {
                            sumatotal_descuento += total_descuento;
                        }
                        var total_utilidad = parseFloat($(this).find('td:nth-child(7)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_utilidad)) {
                            sumatotal_utilidad += total_utilidad;
                        }
                        var total_costo = parseFloat($(this).find('td:nth-child(6)').text().replace(/[$,]/g, ''));
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
}


function AsignarDataUtilidadPorAgente() {
    $('#tbody_tblreporteprincipal').on('click', 'tr', function() {
      // Obtén el valor del atributo data-codigo del botón 1
        const codigo = $(this).data('codigo');
        const id = $(this).data('id');
        $('#CodigoCliente').html(codigo);
        $('#idcliente').html(id);
    });
}

function EjecutarConsultaUtilidadPorAgenteSoloProductos() {
    $('#EjecutarConsultaUtilidadPorAgenteSoloProductos').on('click', function() {
        Codigo= $("#CodigoCliente").text();
         //codigo que ejecuta el loader        
        var screen = $('#loading-screen');
        screen.fadeIn();
        //saca los datos de los data y los imprime en el head del modal
        $('#dataValues').text("Codigo de Cliente: " + Codigo);

        var formData = $("#frm_principal").serializeArray();
        
                //LEEMOS LOS SELECTS MULTIPLES Y LO METEMOS AL ARRAY
                //**************************ARRAYS DE PRODUCTOS***********************************************************
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
                //**************************FIN ARRAYS DE PRODUCTOS***********************************************************

                //**************************ARRAYS DE AGENTE***********************************************************
        var cbxAgent1 = [];
        $("#cbxAgent1 option:selected").each(function() {
            cbxAgent1.push($(this).val());
        });
        formData.push({ name: "cbxAgent1", value: JSON.stringify(cbxAgent1) });

        var agentClasification2 = [];
        $("#agentClasification2 option:selected").each(function() {
            agentClasification2.push($(this).val());
        });
        formData.push({ name: "agentClasification2", value: JSON.stringify(agentClasification2) });

        var agentClasification3 = [];
        $("#agentClasification3 option:selected").each(function() {
            agentClasification3.push($(this).val());
        });
        formData.push({ name: "agentClasification3", value: JSON.stringify(agentClasification3) });

        var agentClasification4 = [];
        $("#agentClasification4 option:selected").each(function() {
            agentClasification4.push($(this).val());
        });
        formData.push({ name: "agentClasification4", value: JSON.stringify(agentClasification4) });

        var agentClasification5 = [];
        $("#agentClasification5 option:selected").each(function() {
            agentClasification5.push($(this).val());
        });
        formData.push({ name: "agentClasification5", value: JSON.stringify(agentClasification5) });

        var agentClasification6 = [];
        $("#agentClasification6 option:selected").each(function() {
            agentClasification6.push($(this).val());
        });
        formData.push({ name: "agentClasification6", value: JSON.stringify(agentClasification6) });
                //**************************FIN ARRAYS DE AGENTE***********************************************************
                //**************************ARRAYS DE CLIENTE***********************************************************
        var cbx_clas1client = [];
        $("#cbx_clas1client option:selected").each(function() {
            cbx_clas1client.push($(this).val());
        });
        formData.push({ name: "cbx_clas1client", value: JSON.stringify(cbx_clas1client) });

        var cbx_typeclient = [];
        $("#cbx_typeclient option:selected").each(function() {
            cbx_typeclient.push($(this).val());
        });
        formData.push({ name: "cbx_typeclient", value: JSON.stringify(cbx_typeclient) });

        var cbx_agent = [];
        $("#cbx_agent option:selected").each(function() {
            cbx_agent.push($(this).val());
        });
        formData.push({ name: "cbx_agent", value: JSON.stringify(cbx_agent) });

        var cbx_zone = [];
        $("#cbx_zone option:selected").each(function() {
            cbx_zone.push($(this).val());
        });
        formData.push({ name: "cbx_zone", value: JSON.stringify(cbx_zone) });

        var cbx_clas5client = [];
        $("#cbx_clas5client option:selected").each(function() {
            cbx_clas5client.push($(this).val());
        });
        formData.push({ name: "cbx_clas5client", value: JSON.stringify(cbx_clas5client) });

        var cbx_clas6client = [];
        $("#cbx_clas6client option:selected").each(function() {
            cbx_clas6client.push($(this).val());
        });
        formData.push({ name: "cbx_clas6client", value: JSON.stringify(cbx_clas6client) });

                //**************************FIN ARRAYS DE CLIENTE***********************************************************

        var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
        var checkboxValues = [];

        //aqui se sacan los conceptos seleccionados con anterioridad
        checkboxes.each(function() {
            var checkboxValue = $(this).attr("name");
            checkboxValues.push(checkboxValue);
        });

        // Agregar la variable checkboxValues a los datos del formulario
        formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });

        formData.push({ name: "Codigo", value: Codigo });
        formData.push({ name: "accionajax", value: "InsertarTblUtilidadPorAgenteSoloProductos" });

        //ejecuta la funcion ajax para llamar cada uno de los registros
        $.ajax({
            url: "controlador/ventasconutilidad/UtilidadPorAgenteControlador.php",
            method: "POST",
            data: formData,
            success: function(response) {

                if ($.fn.DataTable.isDataTable('#tbl_SoloProductos')) {
                    $('#tbl_SoloProductos').DataTable().destroy();
                }

                // Manejar la respuesta de la solicitud AJAX
                //SE AGREGAN LOS DATOS OBTENIDOS A LA TABLA
                $('#tbody_tblSoloProductos').html(response);

                $('#tbl_SoloProductos').DataTable({
                    language: {
                        url: 'vista/vendor/datatables/es-MX.json'
                    },
                    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>Brtp',
                    pageLength : 100,
                    lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                    buttons: [{
                        extend: 'excel',
                        title: 'Reporte de Agentes Con Utilidad por Productos --- Codigo Agente: '+ Codigo ,
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row:first c', sheet).attr('s', '2');
                        }
                    }]
                });
                
                    var total_costo = 0;// Realizar la suma de los valores de las scolumnass
                    var sumatotal_ventas = 0;
                    var sumatotal_descuento = 0;
                    var sumatotal_utilidad = 0;
                    var sumatotal_costo = 0;
                    var sumatotal_margen = 0;

                    $('#tbody_tblSoloProductos tr').each(function() {
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
                    var numeroFilas = $('#tbody_tblSoloProductos tr').length;
                    sumatotal_margen=((sumatotal_utilidad*100)/sumatotal_ventas);
                    // Mostrar el resultado en la etiqueta de totales
                    $('#unitpro_total_filas').text("Filas: " + numeroFilas);
                    $('#unitpro_total_ventas').text("Neto:" + currencyFormatter(sumatotal_ventas));
                    $('#unitpro_total_descuento').text("Descuento:" + currencyFormatter(sumatotal_descuento));
                    $('#unitpro_total_utilidad').text("Utilidad: "+currencyFormatter((sumatotal_ventas)-(sumatotal_costo)));
                    $('#unitpro_total_costo').text("Importe Costo:" + currencyFormatter(sumatotal_costo));
                    $('#unitpro_total_margen').text("Margen: " + sumatotal_margen.toFixed(2) + "%");
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

function EjecutarConsultaUtilidadPorAgenteSoloDocumentos() {
    $('#EjecutarConsultaUtilidadPorAgenteSoloDocumentos').on('click', function() {
        Codigo= $("#CodigoCliente").text();
         //codigo que ejecuta el loader        
        var screen = $('#loading-screen');
        screen.fadeIn();
        //saca los datos de los data y los imprime en el head del modal
        $('#dataValues').text("Codigo de Agente: " + Codigo);

        var formData = $("#frm_principal").serializeArray();
        
                //LEEMOS LOS SELECTS MULTIPLES Y LO METEMOS AL ARRAY
                //**************************ARRAYS DE PRODUCTOS***********************************************************
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
                //**************************FIN ARRAYS DE PRODUCTOS***********************************************************

                //**************************ARRAYS DE AGENTE***********************************************************
        var cbxAgent1 = [];
        $("#cbxAgent1 option:selected").each(function() {
            cbxAgent1.push($(this).val());
        });
        formData.push({ name: "cbxAgent1", value: JSON.stringify(cbxAgent1) });

        var agentClasification2 = [];
        $("#agentClasification2 option:selected").each(function() {
            agentClasification2.push($(this).val());
        });
        formData.push({ name: "agentClasification2", value: JSON.stringify(agentClasification2) });

        var agentClasification3 = [];
        $("#agentClasification3 option:selected").each(function() {
            agentClasification3.push($(this).val());
        });
        formData.push({ name: "agentClasification3", value: JSON.stringify(agentClasification3) });

        var agentClasification4 = [];
        $("#agentClasification4 option:selected").each(function() {
            agentClasification4.push($(this).val());
        });
        formData.push({ name: "agentClasification4", value: JSON.stringify(agentClasification4) });

        var agentClasification5 = [];
        $("#agentClasification5 option:selected").each(function() {
            agentClasification5.push($(this).val());
        });
        formData.push({ name: "agentClasification5", value: JSON.stringify(agentClasification5) });

        var agentClasification6 = [];
        $("#agentClasification6 option:selected").each(function() {
            agentClasification6.push($(this).val());
        });
        formData.push({ name: "agentClasification6", value: JSON.stringify(agentClasification6) });
                //**************************FIN ARRAYS DE AGENTE***********************************************************
                //**************************ARRAYS DE CLIENTE***********************************************************
        var cbx_clas1client = [];
        $("#cbx_clas1client option:selected").each(function() {
            cbx_clas1client.push($(this).val());
        });
        formData.push({ name: "cbx_clas1client", value: JSON.stringify(cbx_clas1client) });

        var cbx_typeclient = [];
        $("#cbx_typeclient option:selected").each(function() {
            cbx_typeclient.push($(this).val());
        });
        formData.push({ name: "cbx_typeclient", value: JSON.stringify(cbx_typeclient) });

        var cbx_agent = [];
        $("#cbx_agent option:selected").each(function() {
            cbx_agent.push($(this).val());
        });
        formData.push({ name: "cbx_agent", value: JSON.stringify(cbx_agent) });

        var cbx_zone = [];
        $("#cbx_zone option:selected").each(function() {
            cbx_zone.push($(this).val());
        });
        formData.push({ name: "cbx_zone", value: JSON.stringify(cbx_zone) });

        var cbx_clas5client = [];
        $("#cbx_clas5client option:selected").each(function() {
            cbx_clas5client.push($(this).val());
        });
        formData.push({ name: "cbx_clas5client", value: JSON.stringify(cbx_clas5client) });

        var cbx_clas6client = [];
        $("#cbx_clas6client option:selected").each(function() {
            cbx_clas6client.push($(this).val());
        });
        formData.push({ name: "cbx_clas6client", value: JSON.stringify(cbx_clas6client) });

                //**************************FIN ARRAYS DE CLIENTE***********************************************************

        var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
        var checkboxValues = [];

        //aqui se sacan los conceptos seleccionados con anterioridad
        checkboxes.each(function() {
            var checkboxValue = $(this).attr("name");
            checkboxValues.push(checkboxValue);
        });

        // Agregar la variable checkboxValues a los datos del formulario
        formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });

        formData.push({ name: "Codigo", value: Codigo });
        formData.push({ name: "accionajax", value: "InsertarTblUtilidadPorAgentesSoloDocumentos" });
        
        //ejecuta la funcion ajax para llamar cada uno de los registros
        $.ajax({
            url: "controlador/ventasconutilidad/UtilidadPorAgenteControlador.php",
            method: "POST",
            data: formData,
            success: function(response) {

                if ($.fn.DataTable.isDataTable('#tbl_SoloDocumentos')) {
                    $('#tbl_SoloDocumentos').DataTable().destroy();
                }

                // Manejar la respuesta de la solicitud AJAX
                //SE AGREGAN LOS DATOS OBTENIDOS A LA TABLA
                $('#tbody_tblSoloDocumentos').html(response);

                $('#tbl_SoloDocumentos').DataTable({
                    language: {
                        url: 'vista/vendor/datatables/es-MX.json'
                    },
                    dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>Brtp',
                    pageLength : 100,
                    lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                    buttons: [{
                        extend: 'excel',
                        title: 'Reporte de Agentes Con Utilidad por Documentos --- Codigo Agente: '+ Codigo ,
                        customize: function(xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            $('row:first c', sheet).attr('s', '2');
                        }
                    }]
                });
                
                    var total_costo = 0;// Realizar la suma de los valores de las scolumnass
                    var sumatotal_ventas = 0;
                    var sumatotal_descuento = 0;
                    var sumatotal_utilidad = 0;
                    var sumatotal_costo = 0;
                    var sumatotal_margen = 0;

                    $('#tbody_tblSoloDocumentos tr').each(function() {
                        var total_ventas = parseFloat($(this).find('td:nth-child(6)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_ventas)) {
                            sumatotal_ventas += total_ventas;
                        }
                        var total_descuento = parseFloat($(this).find('td:nth-child(7)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_descuento)) {
                            sumatotal_descuento += total_descuento;
                        }
                        var total_utilidad = parseFloat($(this).find('td:nth-child(9)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_utilidad)) {
                            sumatotal_utilidad += total_utilidad;
                        }
                        var total_costo = parseFloat($(this).find('td:nth-child(8)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_costo)) {
                            sumatotal_costo += total_costo;
                        }
                    });
                    var numeroFilas = $('#tbody_tblSoloDocumentos tr').length;
                    sumatotal_margen=((sumatotal_utilidad*100)/sumatotal_ventas);
                    // Mostrar el resultado en la etiqueta de totales
                    $('#unitdoc_total_filas').text("Filas: " + numeroFilas);
                    $('#unitdoc_total_ventas').text("Neto:" + currencyFormatter(sumatotal_ventas));
                    $('#unitdoc_total_descuento').text("Descuento:" + currencyFormatter(sumatotal_descuento));
                    $('#unitdoc_total_utilidad').text("Utilidad:" + currencyFormatter(sumatotal_utilidad));
                    $('#unitdoc_total_costo').text("Importe Costo:" + currencyFormatter(sumatotal_costo));
                    $('#unitdoc_total_margen').text("Margen: " + sumatotal_margen.toFixed(2) + "%");
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
