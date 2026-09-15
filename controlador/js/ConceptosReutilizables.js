function GeneratePdf(url,name,action,formData){
    var screen = $('#loading-screen');
    screen.fadeIn();

    // Buscar el índice del objeto con clave "accionajax" en formData
    var indexToRemove = formData.findIndex(function(item) {
        return item.name === "accionajax";
    });

    // Si se encuentra el objeto con clave "accionajax", eliminarlo
    if (indexToRemove !== -1) {
        formData.splice(indexToRemove, 1);
    }

    // Agregar el nuevo objeto con clave "action" y valor proporcionado
    formData.push({ name: "accionajax", value: action });
    // Eliminar la última parte de la URL y agregar "Reporte.pdf"
    var pdfUrl = url.replace(/\/[^/]+$/, '') + '/Reporte.pdf';
    
    // Realizar la solicitud AJAX enviando los valores de los checkbox
    $.ajax({
        url: url,
        method: "POST",
        data: formData,
        success: function(response) {
            // Generar un enlace para descargar el PDF
            var downloadLink = document.createElement("a");
            downloadLink.href = pdfUrl;
            downloadLink.target = "_blank"; // Para abrir el PDF en una nueva pestaña
            downloadLink.download = name+".pdf"; // Puedes personalizar el nombre del archivo aquí
            downloadLink.click(); // Simular un clic en el enlace para iniciar la descarga
            screen.fadeOut();
            
        }
    });
}

function currencyFormatter(value) {
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    minimumFractionDigits: 2,
    currency: 'USD'
}) 
  return formatter.format(value)
}

function currencyFormatWithoutSymbol(value) {
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'decimal',
    minimumFractionDigits: 2
});
  return formatter.format(value);
}



function CambiarCbxConceptos() {
// Obtener una referencia al elemento select original
    const cbx_document = $('#cbx_document');
    const id = cbx_document.val();
    $.ajax({
        type: 'POST',
        url: 'controlador/ConceptosReutilizablesControlador.php',
        data:{id: id, accionajax: 'CambiarCbxConceptos'},
        success: function(response) {
            $('#tbody_tblconcepts').html(response);
        },
        error: function(xhr, textStatus, errorThrown) {
        console.error('Error al obtener los datos: ' + textStatus + ', ' + errorThrown);
        }
    });

    // Agregar un controlador de eventos para el evento "change"
    $(cbx_document ).on( "change", function() {
        // Obtener el valor seleccionado en el select original
        $("#chx_todos").prop("checked", false);
        const id = cbx_document.val();
        // Enviar una solicitud AJAX al servidor para obtener los datos necesarios para el nuevo select
        $.ajax({
            type: 'POST',
            url: 'controlador/ConceptosReutilizablesControlador.php',
            data:{id: id, accionajax: 'CambiarCbxConceptos'},
            success: function(response) {
                $('#tbody_tblconcepts').html(response);
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error al obtener los datos: ' + textStatus + ', ' + errorThrown);
            }
        });
    });
}

function ValidarFechasInicioFinFrm() {
    $("input[name='startdate']").change(function() {
        var startDate = $(this).val();
        var endDateInput = $("input[name='endate']");

        // Desactivar la selección de endDate si startDate está vacío
        if (startDate === "") {
            endDateInput.prop("disabled", true);
        } else {
            endDateInput.prop("disabled", false);
        }
        // Restablecer el valor de endDate si es anterior a startDate
        if (endDateInput.val() !== "" && endDateInput.val() < startDate) {
            endDateInput.val("");
        }
        // Habilitar o deshabilitar el botón "Ver" según si se han seleccionado ambas fechas
        if (startDate !== "" && endDateInput.val() !== "") {
            $(".btn_validatedate").prop("disabled", false);
        } else {
            $(".btn_validatedate").prop("disabled", true);
        }
    });

    $("input[name='endate']").on('blur', function() {
        var startDate = $("input[name='startdate']").val();
        var endDate = $(this).val();

        // Habilitar o deshabilitar el botón "Ver" según si se han seleccionado ambas fechas
        if (startDate !== "" && endDate !== "") {
            if (startDate <= endDate) {
                $(".btn_validatedate").prop("disabled", false);
            } else {
                Swal.fire(
                    'Fecha Errónea',
                    'Seleccione una fecha mayor a la fecha inicial',
                    'question'
                    )
                $(".btn_validatedate").prop("disabled", true);

            }
        } else {
            $(".btn_validatedate").prop("disabled", true);
        }
    });
}


//FUNCION QUE CAMBIA EL CBX_CLIENTE SEGUN LO QUE SE HAYA ESCRITO EN EL INPUT Y SE AGREGA AL TXT DE IGUAL MANERA
function BuscarFiltroAutocompletadoClienteInput(autocomplete, Txt) {
    $.ajax({
        url: 'controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: { accionajax: "BuscarFiltroAutocompletadoClienteInput" },
        dataType: 'json',
        success: function(response) {
            var datos = response.map(function(item) {
                return { id: item.CCODIGOCLIENTE, label: item.CRAZONSOCIAL};
            });

            // Configura el autocompletado utilizando jQuery UI con los datos obtenidos
            $(autocomplete).on('focus', function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var filteredResults = datos.filter(function(item) {
                            return item.label.toLowerCase().indexOf(term) > -1 || item.id.toLowerCase().indexOf(term) > -1;
                        });

                        var formattedResults = filteredResults.map(function(item) {
                            return {
                                label: item.id + ": " + item.label,
                                value: item.label,
                                id: item.id
                            };
                        });

                        // Limitar el número de resultados mostrados
                        var maxResults = 10;
                        if (formattedResults.length > maxResults) {
                            formattedResults = formattedResults.slice(0, maxResults);
                            formattedResults.push({
                                label: "Existen mas resultados...",
                                value: ""
                            });
                        }

                        response(formattedResults);
                    },
                    select: function(event, ui) {
                        if (ui.item.label === "Existen mas resultados...") {
                            // Implementa la lógica para mostrar más resultados
                            return false;
                        } else {
                            $(autocomplete).val(ui.item.value);
                            $(Txt).html(ui.item.id);
                            $(this).blur();
                            $("#inp_filter_endproduct").focus();
                            return false; // Evitar que se inserte el valor seleccionado en el input
                        }
                    },
                    focus: function(event, ui) {
                        $(this).val(ui.item.value);
                        $(Txt).html(ui.item.id);
                        return false; // Evitar que se inserte el valor resaltado en el input
                    }
                });
            });
        },

        error: function() {
            console.log("Error al obtener los datos del servidor");
        }
    });
}

function VerificarExistenciaAgentes(json,inp_filter,startend,maxmin) {
    var datos1 = json.map(function(item) {
        return {id: item.CCODIGOAGENTE, label: item.CNOMBREAGENTE };
    });
    var inp_filter_tagent = $("#"+inp_filter);
    var agentlabel = $("#"+startend);
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
    if (maxmin != 'max') {
        if (agent == '0') {
            $(agentlabel).html("No existe el Agente, Se agregará el mínimo");
        } else {
            $(agentlabel).html(agent);
        } 
    }else{
        if (agent == '0') {
            $(agentlabel).html("No existe el Agente, Se agregará el maximo");
        } else {
            $(agentlabel).html(agent);
        } 
    }

    //SE ESTA VERIFICANDO QUE LOS CODIGOS AGREGADOS EXISTAN SINO  AGREGA MENSAJES PARA ESPECIFICAR QUE NO EXISTEN
    for (var i = 0; i < datos1.length; i++) {
        if (datos1[i].label === inp_filter_tagent.val()) {
            agent = datos1[i].id;
            nameagent = datos1[i].nombre;
            $(agentlabel).html(nameagent);
        }
    }

    return agent;

}

//FUNCION QUE CAMBIA EL CBX_CLIENTE SEGUN LO QUE SE HAYA ESCRITO EN EL INPUT
function BuscarFiltroAutocompletadoProductoInput(autocomplete, Txt) {
    $.ajax({
        url: 'controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: { accionajax: "BuscarFiltroAutocompletadoProductoInput" },
        dataType: 'json',
        success: function(response) {
            var datos = response.map(function(item) {
                return { id: item.CIDPRODUCTO, label: item.CCODIGOPRODUCTO, nombre: item.CNOMBREPRODUCTO };
            });

            // Configura el autocompletado utilizando jQuery UI con los datos obtenidos
            $(autocomplete).on('focus', function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var filteredResults = datos.filter(function(item) {
                            return item.label.toLowerCase().indexOf(term) > -1 || item.nombre.toLowerCase().indexOf(term) > -1;
                        });

                        var formattedResults = filteredResults.map(function(item) {
                            return {
                                label: item.label + ": " + item.nombre,
                                value: item.label,
                                nombre: item.nombre
                            };
                        });

                        // Limitar el número de resultados mostrados
                        var maxResults = 10;
                        if (formattedResults.length > maxResults) {
                            formattedResults = formattedResults.slice(0, maxResults);
                            formattedResults.push({
                                label: "Existen mas resultados...",
                                value: "",
                                nombre: ""
                            });
                        }

                        response(formattedResults);
                    },
                    select: function(event, ui) {
                        if (ui.item.label === "Existen mas resultados...") {
                            // Implementa la lógica para mostrar más resultados
                            return false;
                        } else {
                            $(autocomplete).val(ui.item.value);
                            $(Txt).html(ui.item.nombre);
                            $(this).blur();
                            $("#inp_filter_endproduct").focus();
                            return false; // Evitar que se inserte el valor seleccionado en el input
                        }
                    },
                    focus: function(event, ui) {
                        $(this).val(ui.item.value);
                        $(Txt).html(ui.item.nombre);
                        return false; // Evitar que se inserte el valor resaltado en el input
                    }
                });
            });
        },

        error: function() {
            console.log("Error al obtener los datos del servidor");
        }
    });
}




//FUNCION QUE CAMBIA EL CBX_CLIENTE SEGUN LO QUE SE HAYA ESCRITO EN EL INPUT Y SE AGREGA AL TXT DE IGUAL MANERA
function BuscarFiltroAutocompletadoAgenteInput(autocomplete, Txt) {
    $.ajax({
        url: 'controlador/ConceptosReutilizablesControlador.php',
        method: 'POST',
        data: { accionajax: "BuscarFiltroAutocompletadoAgenteInput"},
        dataType: 'json',
        success: function(response) {
            var datos = response.map(function(item) {
                return { id: item.CCODIGOAGENTE, label: item.CNOMBREAGENTE};
            });

            // Configura el autocompletado utilizando jQuery UI con los datos obtenidos
            $(autocomplete).on('focus', function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var filteredResults = datos.filter(function(item) {
                            return item.label.toLowerCase().indexOf(term) > -1 || item.id.toLowerCase().indexOf(term) > -1;
                        });

                        var formattedResults = filteredResults.map(function(item) {
                            return {
                                label: item.id + ": " + item.label,
                                value: item.label,
                                id: item.id
                            };
                        });

                        // Limitar el número de resultados mostrados
                        var maxResults = 10;
                        if (formattedResults.length > maxResults) {
                            formattedResults = formattedResults.slice(0, maxResults);
                            formattedResults.push({
                                label: "Existen mas resultados...",
                                value: ""
                            });
                        }

                        response(formattedResults);
                    },
                    select: function(event, ui) {
                        if (ui.item.label === "Existen mas resultados...") {
                            // Implementa la lógica para mostrar más resultados
                            return false;
                        } else {
                            $(autocomplete).val(ui.item.value);
                            $(Txt).html(ui.item.id);
                            $(this).blur();
                            $("#inp_filter_endproduct").focus();
                            return false; // Evitar que se inserte el valor seleccionado en el input
                        }
                    },
                    focus: function(event, ui) {
                        $(this).val(ui.item.value);
                        $(Txt).html(ui.item.id);
                        return false; // Evitar que se inserte el valor resaltado en el input
                    }
                });
            });
        },

        error: function() {
            console.log("Error al obtener los datos del servidor");
        }
    });
}

