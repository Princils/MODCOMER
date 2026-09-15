    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/ConceptosPorTiendaControlador.php";
    include_once "modelo/ConceptosPorTiendaModelo.php";
    $ConceptosPorTienda = new ConceptosPorTiendaControlador();
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();

    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Conceptos por Tienda","Configuración");
    if ($data) :
        ?>

        <title>SRComrcial - Conceptos por Tienda</title>


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
                    <h1 class="h3 mb-2 text-gray-800">Conceptos por tienda</h1>
                    <p class="mb-4">Cree una <b>nueva clase</b> de conceptos usando el boton <b>(+)</b> y elija los conceptos que tendrá dicha clase, siempre que seleccione alguna clase en un reporte, se <b>seleccionaran únicamente los conceptos disponibles</b> </p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="container d-flex justify-content-end my-2">
                                <button 
                                class=" btn btn-success  mx-1" 
                                id="btn_mdlnewconcept" data-toggle="modal" 
                                data-target="#AddNewConcept"  
                                title="Agregar Usuarios Al Sistema" 
                                >
                                <i class="fas fa-fw fa-plus"></i>
                              </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tbl_conceptsperstore" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="w-25">Codigo</th>
                                            <th class="w-50">Nombre Clase</th>
                                            <th>Conceptos</th>
                                            <th>Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_conceptsperstore">
                                        <?php
                                        $ConceptosPorTienda->AgregarTblConceptosPorTiendaControlador();
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



<div class="modal fade" id="ModalConceptos"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog" style="min-width: 45%;">
        <div class="modal-content" >
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="TipoConcepto">Conceptos del  ///  codigo:<label id="conceptperstore_ID"></label>  ///  Nombre:<label id="conceptperstore_NAME"></label></h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-check my-1">
                    <input class="mycheck2" type="checkbox" value="" id="chx_todos" />
                    <label class="form-check-label" for="chx_todos">TODO</label>
                </div>

                <select class="form-control" multiple id="cbx_sucursal" name="cbx_sucursal[]" >
                    <?php 
                    $ConceptosReutilizables->AgregarCbxSucursalControlador();
                    ?>
                </select>
                <div class="m-2">
                      <div style="height: 40vh; width: 100%; overflow: auto; table-layout: fixed;" >
                        <table class="table table-hover border mt-3 table-responsive"  >
                          <thead class="text-white text-center bg-primary"  >
                            <th style="width: 95%" class="px-1 mx-0">CONCEPTO</th>
                            <th style="width: 5%" class="px-1 mx-0">ELEGIR</th>
                        </thead>
                        <tbody id="tbody_tblconcepts">
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary fw-bold btnsavechangesconceptsperstore" data-dismiss="modal">Guardar</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="AddNewConcept"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content" >
            <div class="modal-header bg-primary" >
                <h5 class="modal-title text-white" id="TipoConcepto">Nuevo Concepto por Tienda</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frm_newconceptperstore" method="POST" >
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-outline mb-4 col">
                                  <label class="form-label" for="codigo">Codigo</label>
                                  <input type="text" id="codigo" class="form-control" required/>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-outline mb-4 col">
                                  <label class="form-label" for="nombre_usuario">Nombre</label>
                                  <input type="text" id="nombre_usuario" class="form-control" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark fw-bold bg-primary">Guardar</button>
                    </div>
                </form>   
            </div>
        </div>
    </div>
</div>






<!--************************************************-->
<!--             SCRIPT DE LA PAGINA                -->
<!--************************************************-->

<script src="controlador/js/conceptosportienda.js"></script>
<script type="text/javascript">

    $(document).ready(function() {


        // Call the dataTables jQuery plugin
        $('#tbl_conceptsperstore').DataTable({
            language: {
                url: 'vista/vendor/datatables/es-MX.json'
            }
        });
        AgregarCbxConceptos();
        SeleccionarTodoCheck("chx_todos","tbody_tblconcepts");
        CheckboxSucu();
        NuevoConceptoPorTienda();
        BtnEliminarConceptoPorTienda();
        BtnMostrarChxConceptosPorTienda();
        BtnGuardarCambiosConceptosPorTienda();
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