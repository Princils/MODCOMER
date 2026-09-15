    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/UsuariosYPermisosControlador.php";
    include_once "modelo/UsuariosYPermisosModelo.php";
    $UsuariosYPermisosControlador = new UsuariosYPermisosControlador();
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();

    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Usuarios y Permisos","Configuración");
    if ($data) :
        ?>

        <title>SRComrcial - Usuarios y Pemisos</title>


        <!-- Page Wrapper -->
        <div id="wrapper">

         <?php 
         (isset($_SESSION['id_usuario'])? include_once "vista/menus/navbar_lateral.php" : '');
         ?>


         <!-- Content Wrapper -->
         <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php 
                (isset($_SESSION['id_usuario'])? include_once "vista/menus/navbar_top.php" : '');
                ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Usuarios y Permisos</h1>
                    <p class="mb-4">Modifica las <b>contraseñas, reportes y las bases de datos</b> a las que tendrán accesos los diferentes usuarios, <b>SI</b> aún no tiene usuarios, puede darle clic al botón de <b>(+)</b> para agregar a todos los usuarios que tienen registrados en el sistema <b>CONTPAQi</b></p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="container d-flex justify-content-end my-2">
                                <button 
                                class=" btn btn-success  mx-1" 
                                id="AddNewUsers" data-toggle="modal" 
                                data-target="#AddNewConcept"  
                                title="Agregar Usuarios Al Sistema" 
                                >
                                <i class="fas fa-fw fa-plus"></i>
                              </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tbl_userspermissions" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="">Nivel</th>
                                            <th class="w-25">Codigo</th>
                                            <th class="w-50">Usuario</th>
                                            <th>Ajustes</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_userspermissions">
                                        <?php
                                        $UsuariosYPermisosControlador->AgregarTblUsuariosYPermisosControlador();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->


        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>



<div class="modal fade" id="mdl_updateuser"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" >
    <div class="modal-content" >
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="TipoConcepto">Usuario</h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="container">
          <form id="frm_userspermissions" autocomplete="off" method="POST" action="#">
            <div class="row">
              <div class="col-12">
                <div class="form-outline mb-4 col">
                  <label class="form-label" for="codigo">Codigo</label>
                  <input type="text" id="codigo" class="form-control" required readonly/>
                </div>
              </div>
              <div class="col-12">
                <div class="form-outline mb-4 col">
                  <label class="form-label" for="nombre_usuario">Nombre de Usuario</label>
                  <input type="text" id="nombre_usuario" class="form-control" required readonly/>
                </div>
              </div>
              <div class="col-12">
                <div class="form-outline mb-4 col">
                  <label class="form-label" for="contrasena">Contraseña</label>
                  <input type="password" id="contrasena" value="" class="form-control"/>
                </div>
              </div>
              <div class="col-12">
                <div class="form-outline mb-4 col" style="display: none;">
                  <label class="form-label" for="nivel">Nivel</label>
                  <input type="number" min="1" max="3" id="nivel" />
                </div>
              </div>
              <div class="col-12 text-center my-2">

                <select id="Empresas" class="form-control w-100" multiple>
                    <optgroup label="Empresas">
                        <?php 
                        $UsuariosYPermisosControlador->AgregarCbxCompanyUsuariosYPermisosControlador();
                         ?>
                    </optgroup>
                </select>


              </div>

              <div class="col-12 text-center">
                
                <select id="LvlReports" class="form-control w-100" multiple>
                  <?php 
                    $UsuariosYPermisosControlador->AgregarCbxReportesUsuariosYPermisosControlador();
                  ?>
                    
                </select>


              </div>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary fw-bold">Guardar</button>
      </div>
          </form>   
    </div>
  </div>
</div>






<!--************************************************-->
<!--             SCRIPT DE LA PAGINA                -->
<!--************************************************-->

<script src="controlador/js/usuariosypermisos.js"></script>

<script type="text/javascript">
  
    $(document).ready(function() {
        $('#LvlReports').multiselect({
            includeFilterClearBtn: false,
            maxHeight: 500,
            includeSelectAllOption: true,
            buttonText: function(options, select) {
                return 'Reportes';
            },
            buttonTitle: function(options, select) {
                var labels = [];
                options.each(function () {
                    labels.push($(this).text());
                });
                return labels.join(' - ');
            },
            buttonContainer: '<div class="w-100 " />', // Agrega la clase CSS personalizada al contenedor del botón
        });
        $('#Empresas').multiselect({
            includeFilterClearBtn: false,
            maxHeight: 500,
            includeSelectAllOption: true,
            buttonText: function(options, select) {
                return 'Empresas';
            },
            buttonTitle: function(options, select) {
                var labels = [];
                options.each(function () {
                    labels.push($(this).text());
                });
                return labels.join(' - ');
            },
            buttonContainer: '<div class="w-100 " />', // Agrega la clase CSS personalizada al contenedor del botón
        });
        AgregarNuevosUsuariosTablero();
        ActualizarUnicoUsuarioTablero();
        MostrarDatosUsuarioTablero();
        $('#tbl_userspermissions').DataTable({
            language: {
                url: 'vista/vendor/datatables/es-MX.json'
            }
        });
    });
</script>



<?php else: ?>
  <script type="text/javascript">
    Swal.fire({
      title: '¡¡NO PUEDES ESTAR AQUÍ!!',
      text: 'No tienes acceso a esta página',
      icon: 'error',
      showConfirmButton: true,
      confirmButtonText: 'Continuar',
      allowOutsideClick: false
  }).then((result) => {
      if (result.isConfirmed) {      
        window.location.href = "index.php?view=login";   
    }
});
</script>
<?php endif ?>