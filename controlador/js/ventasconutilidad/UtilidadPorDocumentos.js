//funcion que saca los data-? que se tengan en el tr de su respetivo documento 
function EjecutarSubConsultaUtilidadPorDocumentos() {
    $(document).on('click', '.btnreportutilitydocumentonly', function() {
         //codigo que ejecuta el loader
        var screen = $('#loading-screen');
        screen.fadeIn();
        //saca los datos de los data y los imprime en el head del modal
        var serieValue = $(this).data('serie');
        var folioValue = $(this).data('folio');
        $('#dataValues').text("Serie: " + serieValue + ", Folio: " + folioValue);
        screen.fadeOut();


        //ejecuta la funcion ajax para llamar cada uno de los registros
        $.ajax({
            url: "controlador/ventasconutilidad/UtilidadPorDocumentosControlador.php",
            method: "POST",
            data: {folio: folioValue, serie: serieValue, accionajax: "InsertarTblSubConsultaUtilidadPorDocumentos"},
            success: function(response) {
                console.log(response)
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
                        title: 'Utilidad por Documento, Serie: '+serieValue+', Folio: '+folioValue,
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

                $('#tbody_tblsecundaria tr').each(function() {
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
                var numeroFilas = $('#tbody_tblsecundaria tr').length;
                sumatotal_margen=((sumatotal_utilidad*100)/sumatotal_ventas);
            // Mostrar el resultado en la etiqueta de totales
                $('#unit_total_filas').text("Filas: " + numeroFilas);
                $('#unit_total_ventas').text("Neto:" + currencyFormatter(sumatotal_ventas));
                $('#unit_total_descuento').text("Descuento:" + currencyFormatter(sumatotal_descuento));
                $('#unit_total_utilidad').text("Utilidad:" + currencyFormatter(sumatotal_utilidad));
                $('#unit_total_costo').text("Costo:" + currencyFormatter(sumatotal_costo));
                $('#unit_total_margen').text("Margen: " + sumatotal_margen.toFixed(2) + "%");
                screen.fadeOut();
            },
            error: function(xhr, status, error) {
            // Manejar el error de la solicitud AJAX
                console.error(error);
            }
        });
});
}


function EjecutarConsultaUtilidadPorDocumentos() {
    $(".ExecuteQueryUtilityForDocuments").click(function() {
       $.ajax({
        url: 'controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: {accionajax: "BuscarFiltroAutocompletadoClienteInput"},
        dataType: 'json',
        success: function(response) {
            var datos = response.map(function(item) {
                return { id: item.CIDCLIENTEPROVEEDOR, label: item.CRAZONSOCIAL };
            });
            var inputValue = $("#inp_filter_client").val();
            var clientValue = "0";

            for (var i = 0; i < datos.length; i++) {
                if (datos[i].label === inputValue) {
                    clientValue = datos[i].id;
                    break;
                }
            }
                $("#client").val(clientValue); // Establecer el valor del input 'client' utilizando jQuery
                var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
                var checkboxValues = [];
                var formData = $("#frm_utilityfordocuments").serializeArray(); // Obtener los datos del formulario en formato de matriz de objetos


            //codigo que ejecuta el loader
                var screen = $('#loading-screen');
                screen.fadeIn();
            //aqui se sacan los conceptos seleccionados con anterioridad
                checkboxes.each(function() {
                  var checkboxValue = $(this).attr("name");
                  checkboxValues.push(checkboxValue);
              });

            // Agregar la variable checkboxValues a los datos del formulario
                formData.push({ name: "checkboxValues", value: JSON.stringify(checkboxValues) });
                formData.push({ name: "accionajax", value: "InsertarTblUtilidadPorDocumentos" });

            // Realizar la solicitud AJAX enviando los valores de los checkbox
                $.ajax({
                  url: "controlador/ventasconutilidad/UtilidadPorDocumentosControlador.php",
                  method: "POST",
                  data: formData,
                  success: function(response) {
                    console.log(response);
                    // Manejar la respuesta de la solicitud AJAX
                            //SE AGREGAN LOS DATOS OBTENIDOS A LA TABLA
                    if ($.fn.DataTable.isDataTable('#tbl_reporteprincipal')) {
                        $('#tbl_reporteprincipal').DataTable().destroy();
                    }
                    $('#tbody_tblreporteprincipal').html(response);
                    var startDate = $("input[name='startdate']").val();
                    var endDate =$("input[name='endate']").val();
                    $('#tbl_reporteprincipal').DataTable({
                        language: {
                            url: 'vista/vendor/datatables/es-MX.json'
                        },
                        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>Brtp',
                        pageLength: 100,
                        lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                        buttons: [
                            {
                                extend: 'excel',
                                title: 'Utilidad Por Documento Del ' + startDate + ' Hasta ' + endDate,
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    $('row:first c', sheet).attr('s', '2');
                                }
                            },
                            {
                                text: 'PDF',
                                action: function() {
                                    // Llamar a tu función para generar el PDF
                                    GeneratePdf('controlador/ventasconutilidad/UtilidadPorDocumentosControlador.php','Utilidad Por Documento Del ' + startDate + ' Hasta ' + endDate,'GenerarPdfUtilidadPorDocumentos',formData);
                                }
                            }
                        ],
                        pageLength: 100,
                        lengthMenu: [[100, 500, 1000, 2000], [100, 500, 1000, 2000]],
                        columnDefs: [
                            {
                                type: 'num',
                                targets: 0 // Indica la primera columna (0-based index)
                            }
                        ]
                    });

                    // Realizar la suma de los valores de las columnas
                    var sumatotal_ventas = 0;
                    var sumatotal_descuento = 0;
                    var sumatotal_utilidad = 0;
                    var sumatotal_costo = 0;
                    var sumatotal_margen = 0;

                    $('#tbody_tblreporteprincipal tr').each(function() {
                        var total_ventas = parseFloat($(this).find('td:nth-child(9)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_ventas)) {
                            sumatotal_ventas += total_ventas;
                        }
                        var total_descuento = parseFloat($(this).find('td:nth-child(10)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_descuento)) {
                            sumatotal_descuento += total_descuento;
                        }
                        var total_utilidad = parseFloat($(this).find('td:nth-child(12)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_utilidad)) {
                            sumatotal_utilidad += total_utilidad;
                        }
                        var total_costo = parseFloat($(this).find('td:nth-child(11)').text().replace(/[$,]/g, ''));
                        if (!isNaN(total_costo)) {
                            sumatotal_costo += total_costo;
                        }
                    });
                    var numeroFilas = $('#tbody_tblreporteprincipal tr').length;
                    //formula que usamos para el margen
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
                    console.error(error);
                    screen.fadeOut();

                }
            });
}
});
});
}