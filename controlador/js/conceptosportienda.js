function NuevoConceptoPorTienda() {
 $("#frm_newconceptperstore").submit(function(event) {
    event.preventDefault();
    var CODIGO = $('#codigo').val();
    var NOMBRE = $('#nombre_usuario').val();

    // Realizar una solicitud AJAX para ejecutar un evento
    $.ajax({
      url: 'controlador/ConceptosPorTiendaControlador.php',
      method: 'POST',
      data: {
        CODIGO: CODIGO,
        NOMBRE: NOMBRE,
        accionajax: 'NuevoConceptoPorTienda' // Enviar los datos de la fila como parte de la solicitud
    },
    success: function(response) {
        if (!response.includes('error')) {
            $('#AddNewConcept').modal('hide');
            Swal.fire({
              position: 'top-end',
              icon: 'success',
              title: 'Registro Agregado',
              showConfirmButton: false,
              timer: 2000,
              toast: true,
              background: true
          });
            $.ajax({
                url: 'controlador/ConceptosPorTiendaControlador.php',
                method: 'POST',
                data: {accionajax: "AgregarTblConceptosPorTienda"},
                success: function(response) {
                    if ($.fn.DataTable.isDataTable('#tbl_conceptsperstore')) {
                        $('#tbl_conceptsperstore').DataTable().destroy();
                    }
                    $('#tbody_conceptsperstore').html(response);
                    $('#tbl_conceptsperstore').DataTable({
                        language: {
                            url: 'vista/vendor/datatables/es-MX.json'
                        }
                    });
                    $('#codigo').val('');
                    $('#nombre_usuario').val('');
                }
            });
            
        }else{
            Swal.fire({
              position: 'top-end',
              icon: 'error',
              title: 'No puede haber registros Duplicados',
              showConfirmButton: false,
              timer: 2000,
              toast: true,
              background: true
          });
        }
    }
});
});
}

function BtnEliminarConceptoPorTienda() {
    $(document).on("click", ".deleteconceptperstore", function() {
        var row = $(this).closest("tr");
        var codigo = row.find("td:eq(1)").text();
        var nombre = row.find("td:eq(2)").text();
        Swal.fire({
          title: 'Eliminacion',
          text: "Seguro que quieres eliminar el concepto '"+nombre+"'",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Seguro!'
      }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
              url: 'controlador/ConceptosPorTiendaControlador.php',
              method: 'POST',
              data: {
                CODIGO: codigo, 
                NOMBRE: nombre,
                accionajax: 'EliminarConceptoPorTienda' 
            },success: function(response) {
                if(!response.includes('error')){
                    $.ajax({
                        url: 'controlador/ConceptosPorTiendaControlador.php',
                        method: 'POST',
                        data: {accionajax: "AgregarTblConceptosPorTienda"},
                        success: function(response) {
                            if ($.fn.DataTable.isDataTable('#tbl_conceptsperstore')) {
                                $('#tbl_conceptsperstore').DataTable().destroy();
                            }
                            $('#tbody_conceptsperstore').html(response);
                            $('#tbl_conceptsperstore').DataTable({
                                language: {
                                    url: 'vista/vendor/datatables/es-MX.json'
                                }
                            });
                        }
                    });
                    Swal.fire(
                      'ELIMINADO!',
                      "El Concepto '"+nombre+"' fue eliminado",
                      'success'
                      )
                }else{
                    Swal.fire(
                      'Algo fallo!',
                      "Intenta Desactivar Todos los conceptos",
                      'error'
                      )
                }
            }
        }); 
        } 
    })
  });
}


function BtnMostrarChxConceptosPorTienda() {
  $(document).on("click", ".showconceptsperStore", function() {
    var row = $(this).closest("tr");
    var codigo = row.find("td:eq(1)").text();
    var nombre = row.find("td:eq(2)").text();
    $('#conceptperstore_ID').html(codigo);
    $('#conceptperstore_NAME').html(nombre);

    $.ajax({
        url: 'controlador/ConceptosPorTiendaControlador.php',
        method: 'POST',
        data: {
        CODIGO: codigo,
        NOMBRE: nombre,
        accionajax: 'MostrarChxConceptosPorTienda'
    },
    success: function(response) {
        // Obtener el array de la respuesta AJAX
        var array = JSON.parse(response);

        // Iterar sobre los elementos de la tabla y seleccionar/deseleccionar los checkboxes según corresponda
        $('#tbody_tblconcepts tr').each(function() {
          var checkbox = $(this).find('input[type="checkbox"]');
          var name = checkbox.attr('name');

          // Verificar si el name existe en el array
          var exists = array.some(function(item) {
            return item.CODIGO === name;
        });

          // Seleccionar/deseleccionar el checkbox según corresponda
          checkbox.prop('checked', exists);
      });
    },
    error: function(error) {
        console.log(error);
    }
    });

    });
}

function BtnGuardarCambiosConceptosPorTienda() {
  $(document).on("click", ".btnsavechangesconceptsperstore", function() {
    var codigo = $('#conceptperstore_ID').text();
    var nombre = $('#conceptperstore_NAME').text();
    var checkboxes = $("#tbody_tblconcepts input[type='checkbox']:checked");
    var checkboxValues = [];
        //aqui se sacan los conceptos seleccionados con anterioridad
    checkboxes.each(function() {
        var checkboxValue = $(this).attr("name");
        checkboxValues.push(checkboxValue);
    });
    $.ajax({
      url: 'controlador/ConceptosPorTiendaControlador.php',
      method: 'POST',
      data: {
        CODIGO: codigo,
        NOMBRE: nombre,
        checkboxValues: JSON.stringify(checkboxValues),
        accionajax: 'GuardarCambiosConceptosPorTienda'
      },
      success: function(response) {
        if (!response.includes('error')) {
            Swal.fire({
              position: 'top-end',
              icon: 'success',
              title: 'Registro Actualizado',
              showConfirmButton: false,
              timer: 1000,
              toast: true,
              background: true
          });
        }else{
            Swal.fire({
              position: 'top-end',
              icon: 'error',
              title: 'Algo fallo',
              showConfirmButton: false,
              timer: 1000,
              toast: true,
              background: true
          });
        }
      }
    });

  });
}