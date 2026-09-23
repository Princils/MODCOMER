function CreateVarScreen() {
    var screen = $('#loading-screen');
    configureLoadingScreenAjax(screen);
}


function configureLoadingScreenAjax(screen){
    $(document)
    .ajaxStart(function () {
        screen.fadeIn();
    })
    .ajaxStop(function () {
        screen.fadeOut();
    });
}


//FUNCION PARA CHEQUEAR TODOS LOS CHECKBOXES AL SELECCIONAR UNO QUE SEA PARA TODOS
function SeleccionarTodoCheck(chxall,tbl) {
    $("#"+chxall).change(function() {
        var isChecked = $(this).prop("checked");
        $("#"+tbl+" input[type='checkbox']").prop("checked", isChecked);
    });
}

function AgregarCbxConceptos() {
// Obtener una referencia al elemento select original
    const id = '0';
    $.ajax({
        type: 'POST',
        url: 'controlador/ConceptosReutilizablesControlador.php',
        data:{id: id, accionajax: 'AgregarTblConceptosTodos'},
        success: function(response) {
            $('#tbody_tblconcepts').html(response);
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error al obtener los datos: ' + textStatus + ', ' + errorThrown);
        }
    });

}

//FUNCION PARA CHEQUEAR LOS CHECKBOXES QUE TENGAN EN COMUN LA PERSONALIZADA
// Las tablas creadas con el modal oculto necesitan recalcular su ancho al abrirlo.
$(document).on('shown.bs.modal', '.modal', function () {
    $(this).find('table').each(function () {
        if ($.fn.dataTable && $.fn.dataTable.isDataTable(this)) {
            $(this).DataTable().columns.adjust();
        }
    });
});

function CheckboxSucu() {
    //VERIFICAMOS QUE EL SELECT CAMBIE
    $("#cbx_sucursal").change(function() {
        var selectedIds = [];
        //LEEMOS EL SELECT MULTIPLE Y LO METEMOS AL ARRAY
        $("#cbx_sucursal option:selected").each(function() {
            selectedIds.push($(this).val());
        });

        // Desactivar todos los checkboxes
        $(".mycheck2").prop("checked", false);

        // Activar solo los checkboxes que tengan algo en común con los IDs seleccionados
        for (var i = 0; i < selectedIds.length; i++) {
            $(".sucu_" + selectedIds[i]).prop("checked", true);
        }
    });

    //VERIFICAMOS QUE EL SELECT CAMBIE
    $("#cbx_ConceptsPerStore").change(function() {
        var selectsStore = $('#cbx_ConceptsPerStore').val();

        // Desactivar todos los checkboxes
        $(".mycheck2").prop("checked", false);

        // Activar solo los checkboxes que tengan algo en común con los IDs seleccionados
        $(".store_" + selectsStore).prop("checked", true);
    });
}

// Llevar al resultado solo al calcular, no al ordenar o cambiar de pagina.
function EnfocarTablaReporte(selector) {
    requestAnimationFrame(function () {
        var tabla = document.querySelector(selector);
        if (!tabla || !tabla.closest('.reporte-compacto')) { return; }
        var tarjeta = tabla.closest('.card') || tabla;
        tarjeta.querySelectorAll('.table-responsive, .inventario-scroll, .dataTables_scrollBody').forEach(function (contenedor) {
            contenedor.scrollTop = 0;
            contenedor.scrollLeft = 0;
        });
        tarjeta.scrollIntoView({block: 'start', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'});
    });
}
$(document).on('init.dt', function (evento, settings) {
    if (settings && settings.nTable && ['tbl_reporteprincipal', 'tbl_margenes'].indexOf(settings.nTable.id) !== -1) {
        EnfocarTablaReporte('#' + settings.nTable.id);
    }
});
