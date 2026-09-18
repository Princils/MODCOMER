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
function BuscarFiltroAutocompletadoClienteInput(selector, texto) {
    ConfigurarCatalogoReporte(selector, texto, 'BuscarFiltroAutocompletadoClienteInput', 'CCODIGOCLIENTE', 'CRAZONSOCIAL', false);
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
function BuscarFiltroAutocompletadoProductoInput(selector, texto) {
    ConfigurarCatalogoReporte(selector, texto, 'BuscarFiltroAutocompletadoProductoInput', 'CCODIGOPRODUCTO', 'CNOMBREPRODUCTO', true);
}


function BuscarFiltroAutocompletadoAgenteInput(selector, texto) {
    ConfigurarCatalogoReporte(selector, texto, 'BuscarFiltroAutocompletadoAgenteInput', 'CCODIGOAGENTE', 'CNOMBREAGENTE', false);
}


// Compartir el catálogo entre los campos inicial/final de la misma página.
var catalogosReporte = {};
function ConfigurarCatalogoReporte(selector, texto, accion, campoCodigo, campoNombre, usarCodigo) {
    if (!catalogosReporte[accion]) {
        catalogosReporte[accion] = $.ajax({
            url: 'controlador/ConceptosReutilizablesControlador.php',
            method: 'POST',
            dataType: 'json',
            data: { accionajax: accion }
        }).fail(function () {
            delete catalogosReporte[accion];
            console.error('No se pudo cargar el catálogo del reporte.');
        });
    }
    catalogosReporte[accion].done(function (filas) {
        $(selector).each(function () {
            var input = $(this);
            input.autocomplete({
                minLength: 0,
                delay: 100,
                source: function (request, response) {
                    var termino = request.term.toLocaleLowerCase();
                    response(filas.filter(function (fila) {
                        return (String(fila[campoCodigo] || '') + ' ' + String(fila[campoNombre] || ''))
                            .toLocaleLowerCase().indexOf(termino) !== -1;
                    }).slice(0, 20).map(function (fila) {
                        return {
                            label: fila[campoCodigo] + ': ' + fila[campoNombre],
                            value: usarCodigo ? fila[campoCodigo] : fila[campoNombre],
                            descripcion: usarCodigo ? fila[campoNombre] : fila[campoCodigo]
                        };
                    }));
                },
                focus: function () { return false; },
                select: function (event, ui) {
                    input.val(ui.item.value);
                    $(texto).text(ui.item.descripcion);
                    return false;
                }
            }).off('.catalogoReporte').on('focus.catalogoReporte click.catalogoReporte', function () {
                input.autocomplete('search', input.val());
            });
            // Si el usuario hizo clic mientras se cargaban los datos, abrir al terminar.
            if (input.is(':focus')) input.autocomplete('search', input.val());
        });
    });
}
