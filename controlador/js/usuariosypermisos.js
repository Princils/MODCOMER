function AgregarNuevosUsuariosTablero() {
    $('#AddNewUsers').on('click', function() {
        var screen = $('#loading-screen');
        screen.fadeIn();
        $.ajax({
            url: 'controlador/UsuariosYPermisosControlador.php',
            method: 'POST',
            data: {accionajax: "AgregarNuevosUsuariosTablero"},
            success: function(response) {
                if (response > 0) {
                    Swal.fire(
                        'Finalizado',
                        ''+response+' Usuarios nuevos fueron agregados con exito',
                        'success'
                        );
                }else{
                   Swal.fire(
                    'Finalizado',
                    'No se agrego ningun usuario nuevo',
                    'success'
                    );
               }
               $.ajax({
                url: 'controlador/UsuariosYPermisosControlador.php',
                method: 'POST',
                data: {accionajax: "AgregarTblUsuariosYPermisos"},
                success: function(response) {
                    if ($.fn.DataTable.isDataTable('#tbl_userspermissions')) {
                        $('#tbl_userspermissions').DataTable().destroy();
                    }
                    $('#tbody_userspermissions').html(response);
                    $('#tbl_userspermissions').DataTable({
                        language: {
                            url: 'vista/vendor/datatables/es-MX.json'
                        }
                    });
                    
                }
            }); 
           }
       });
        screen.fadeOut();   
    });
}


function ActualizarUnicoUsuarioTablero() {
    $("#frm_userspermissions").submit(function(event) {
        event.preventDefault();
        
        var LVLS = $('#LvlReports').val();
        var DDBS = $('#Empresas').val();

        // Verificar si hay valores seleccionados
        if (DDBS && DDBS.length > 0) {
          // Convertir el arreglo de valores a una cadena separada por comas (si es necesario)
          DDBS = DDBS.join(',');
      } else {
          // Si no hay valores seleccionados, asignar un valor por defecto o realizar alguna acción adecuada.
          DDBS = ''; // Por ejemplo, podrías asignar un mensaje para indicar que no se seleccionaron opciones.
      }

        // Verificar si hay valores seleccionados
      if (LVLS && LVLS.length > 0) {
          // Convertir el arreglo de valores a una cadena separada por comas (si es necesario)
          LVLS = LVLS.join(',');
      } else {
          // Si no hay valores seleccionados, asignar un valor por defecto o realizar alguna acción adecuada.
          LVLS = 'Sin selecciones'; // Por ejemplo, podrías asignar un mensaje para indicar que no se seleccionaron opciones.
      }

      console.log(LVLS);
      console.log(DDBS);

      var NIVEL = $('#nivel').val();
      var CODIGO = $('#codigo').val();
      var USUARIO = $('#nombre_usuario').val();
      var CONTRASENA = $('#contrasena').val();
      $('#contrasena').val('');

            // Realizar una solicitud AJAX para ejecutar un evento
      $.ajax({
          url: 'controlador/UsuariosYPermisosControlador.php',
          method: 'POST',
          data: {
            NIVEL: NIVEL,
            CODIGO: CODIGO,
            USUARIO:USUARIO,
            LVLS: LVLS,
            DDBS: DDBS,
                CONTRASENA: CONTRASENA, // No hay campo de contraseña en la tabla
                accionajax: 'ActualizarUnicoUsuarioTablero' // Enviar los datos de la fila como parte de la solicitud
            },
            success: function(response) {
                console.log(response)
                
                if (!response.includes('error')) {
                    Swal.fire({
                      position: 'top-end',
                      icon: 'success',
                      title: 'Registro Actualizado',
                      showConfirmButton: false,
                      timer: 2000,
                      toast: true,
                      background: true
                  });
                    $.ajax({
                        url: 'controlador/UsuariosYPermisosControlador.php',
                        method: 'POST',
                        data: {accionajax: "AgregarTblUsuariosYPermisos"},
                        success: function(response) {
                            if ($.fn.DataTable.isDataTable('#tbl_userspermissions')) {
                                $('#tbl_userspermissions').DataTable().destroy();
                            }
                            $('#tbody_userspermissions').html(response);
                            $('#tbl_userspermissions').DataTable({
                                language: {
                                    url: 'vista/vendor/datatables/es-MX.json'
                                }
                            });
                            
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
                $('#mdl_updateuser').modal('hide');
            }
        });
    });
}



function MostrarDatosUsuarioTablero() {
    $(document).on('click', '.showdatauserboard', function() {
        var row = $(this).closest("tr");
        var id = row.find("td:eq(1)").text();
        var codigo = row.find("td:eq(2)").text();
        var nombre = row.find("td:eq(3)").text();
        var nivel = row.find("td:eq(3)").text();

            // Establecer los valores en el formulario del modal
        $("#codigo").val(codigo);
        $("#nombre_usuario").val(nombre);
        $("#nivel").val(nivel);
        $("#contrasena").val('');

        $.ajax({
          url: 'controlador/UsuariosYPermisosControlador.php',
          method: 'POST',
          dataType: 'json',
          data: {
            ID: id,
            CODIGO: codigo,
            accionajax: 'MostrarDataSelectReportes' // Enviar los datos de la fila como parte de la solicitud
          },
          success: function(data) {
                // Deselecciona todas las opciones en el multiselect
                $('#LvlReports').multiselect('deselectAll', false);

                var cidreportevalue = [];
               // Iterar a través del array de objetos JSON y agregar los valores de CEMPRESA
                for (var i = 0; i < data.length; i++) {
                  cidreportevalue.push(data[i].CIDREPORTE);
                }

                console.log(cidreportevalue)

                // Selecciona las nuevas opciones en el multiselect
                $('#LvlReports').multiselect('select', cidreportevalue);


                // Actualiza el multiselect después de seleccionar opciones
                $('#LvlReports').multiselect('refresh');


            }
        });
        $.ajax({
          url: 'controlador/UsuariosYPermisosControlador.php',
          method: 'POST',
          dataType: 'json',
          data: {
            ID: id,
            CODIGO: codigo,
            accionajax: 'MostrarDataSelectCompanysBd' // Enviar los datos de la fila como parte de la solicitud
          },
            success: function(data) {
                console.log(data);
                $('#Empresas').multiselect('deselectAll', false);
            
                var cempresaValues = [];
               // Iterar a través del array de objetos JSON y agregar los valores de CEMPRESA
                for (var i = 0; i < data.length; i++) {
                  cempresaValues.push(data[i].CEMPRESA);
                }

                // Selecciona las opciones que coinciden con cempresaValue
                $('#Empresas').multiselect('select', cempresaValues);

                // Actualiza el multiselect después de seleccionar opciones
                $('#Empresas').multiselect('refresh');

            }
        });

    });
}