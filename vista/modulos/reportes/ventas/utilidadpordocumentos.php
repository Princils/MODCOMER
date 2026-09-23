    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/ventasconutilidad/UtilidadPorDocumentosControlador.php";
    include_once "modelo/UsuariosYPermisosModelo.php";
    $UtilidadPorDocumentosControlador = new UtilidadPorDocumentosControlador();
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();

    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Utilidad por Documentos","Ventas Con Utilidad");
    if ($data) :
        ?>

        <title>SRComrcial - Utilidad Por Documentos</title>


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
                <div class="container-fluid reporte-compacto">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Utilidad por Documentos</h1>
                    <p class="mb-4">Muestra todos los <b>documentos</b> que se elijan en el apartado de <b>conceptos</b>, dando una vista general de cada documento, al darle <b>clic</b> en alguno, muestra un <b>reporte más detallado</b> sobre el documento específico</b></p>

                     <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form class="filtros-compactos" id="frm_utilityfordocuments" action="POST" autocomplete="off">
                                <div class="row">
                                    <div class="col-12 col-md-4 my-2">
                                        <label class="form-label">Cliente</label>
                                        <input type="text" name="client" id="client" class="form-control form-control-sm mb-1 d-none" value="0">
                                        <input type="text" name="" id="inp_filter_client" class="form-control mb-1 form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-4  my-2">
                                        <label class="form-label">Fecha Inicial</label>
                                        <input type="date" class="form-control form-control-sm py-1" name="startdate">
                                        <label class="form-label">Fecha Final</label>
                                        <input type="date" disabled class="form-control form-control-sm py-1" name="endate">
                                    </div>
                                    <div class="col-12 col-md-4  my-2">
                                        <label class="form-label">Documento</label>
                                        <select class="form-control form-control-sm" id="cbx_document" name="cbx_document">
                                          <option value="0">(TODOS)</option>
                                          <?php 
                                          $ConceptosReutilizables->AgregarCbxDocumentosControlador();
                                          ?>
                                        </select>
                                        <label class="form-label d-block">Conceptos</label>
                                        <button type="button" class="btn w-100 btn-sm btn-primary  fw-bold btn_validatedate" id="mdl_concepts" data-toggle="modal" data-target="#ModalConceptos" disabled>Ver</button>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" id="btn_calculate" disabled name="btn_calculate" class="btn_validatedate btn btn-success w-100  fw-bold ExecuteQueryUtilityForDocuments" >Calcular</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                     <!-- End DataTales Example -->
                    </div>

                    <div id="totals" class="card shadow mb-4 " style="display: none;">
                        <div class="card-body">
                            <div class="container my-1 d-flex justify-content-center flex-wrap">
                                <label id="total_filas" class="text-dark fw-bold mx-4"></label>
                                <label id="total_ventas" class="text-dark fw-bold mx-4"></label>
                                <label id="total_descuento" class="text-dark fw-bold mx-4"></label>
                                <label id="total_costo" class="text-dark fw-bold mx-4"></label>
                                <label id="total_utilidad" class="text-dark fw-bold mx-4"></label>
                                <label id="total_margen" class="text-dark fw-bold mx-4"></label>
                            </div>
                        </div>
                    </div>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            
                            <div class="table-responsive" style="height: 60vh; overflow: auto; table-layout: fixed;">
                                <table class="table table-bordered table-striped" id="tbl_reporteprincipal"  cellspacing="0">
                                    <thead class="bg-primary text-white" style=" position: sticky; top: 0;">
                                        <tr>
                                            <th class="px-1">#</th>
                                            <th class="px-1">Serie</th>
                                            <th class="px-1">Folio</th>
                                            <th class="px-1" style="width: 100px">Fecha</th>
                                            <th class="px-1">Codigo</th>
                                            <th class="px-1">Cliente</th>
                                            <th class="px-1">Codigo</th>
                                            <th class="px-1">Agente</th>
                                            <th class="px-1" style="text-align: right;">Neto($)</th>
                                            <th class="px-1" style="text-align: right;">Descuento($)</th>
                                            <th class="px-1" style="text-align: right;">Costo($)</th>
                                            <th class="px-1" style="text-align: right;">Utilidad($)</th>
                                            <th class="px-1" style="text-align: right;">%</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_tblreporteprincipal">
                                        <tr >
                                        <td colspan="14" class="text-center">AÚN NO SE SOLICITA NINGÚN REGISTRO</td>
                                      </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                     <!-- End DataTales Example -->
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
        <h5 class="modal-title text-white" id="TipoConcepto">Conceptos de </h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-cols-2 my-1">
          <div class="col">
            <div class="form-check my-1">
              <input class="mycheck2 form-check-input" type="checkbox" value="" id="chx_todos" />
              <label class="form-control-label mt-1 ml-2" for="chx_todos">TODO</label>
            </div>
          </div>
          <div class="col">
            <label>Personalizado</label>
            <select class="form-control" name="company" id="cbx_ConceptsPerStore" required>
              <option value="0">Personalizado (NINGUNO)</option>
              <?php 
                $ConceptosReutilizables->AgrgearCbxConceptosPorTiendaControlador();
               ?>
            </select>
          </div>
        </div>

        <select class="form-control" multiple id="cbx_sucursal" name="cbx_sucursal[]"> TO
          <?php 
            $ConceptosReutilizables->AgregarCbxSucursalControlador();
          ?>
        </select>
        <div class="m-2">
          <div style="height: 40vh; width: 100%; overflow: auto; table-layout: fixed;" >
            <table class="table table-hover border mt-3 table-responsive "  >
              <thead class="text-white text-center bg-primary" >
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
        <button type="button" class="btn btn-primary fw-bold" data-dismiss="modal">Guardar</button>
      </div>
    </div>
  </div>
</div>


<!-- MODAL PARA DUCMENTOS SOLOS -->
<div
class="modal fade"
id="mdl_reportutilitydocumentOnly"
data-backdrop="static"
data-keyboard="false"
tabindex="-1"
aria-labelledby="staticBackdropLabel"
aria-hidden="true"
>
<div class="modal-dialog modal-reporte">
  <div class="modal-content">
    <div class="modal-header bg-primary">
      <h5 class="modal-title text-white" id="staticBackdropLabel"><p id="dataValues"></p></h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="modal-body ">
        <div class="">
            <div class="container my-1 d-flex justify-content-around flex-wrap" >
            <label id="unit_total_filas" class="text-dark fw-bold mx-4"></label>
            <label id="unit_total_ventas" class="text-dark fw-bold mx-4"></label>
            <label id="unit_total_descuento" class="text-dark fw-bold mx-4"></label>
            <label id="unit_total_utilidad" class="text-dark fw-bold mx-4"></label>
            <label id="unit_total_margen" class="text-dark fw-bold mx-4"></label>
            <label id="unit_total_costo" class="text-dark fw-bold mx-4"></label>
            </div>
        </div>
        <hr>
      <div class="my-3 p-4 " style="height: 45vh; overflow: auto; table-layout: fixed;">
        <table class="table table-bordered table-striped" id="tbl_secundaria">
          <thead class="text-white bg-primary" >
            <tr>
              <th class="px-1">#</th>
              <th class="px-1">Codigo</th>
              <th class="px-1">Producto</th>
              <th class="px-1" style="text-align: right;">Unidades</th>
              <th class="px-1" style="text-align: right;">Neto($)</th>
              <th class="px-1" style="text-align: right;">Descuento($)</th>
              <th class="px-1" style="text-align: right;">Costo($)</th>
              <th class="px-1" style="text-align: right;">Utilidad($)</th>
              <th class="px-1" style="text-align: right;">%</th>
            </tr>
          </thead>
          <tbody id="tbody_tblsecundaria">
            <tr>
              <td colspan="13" class="text-center">AUN NO SE SOLICITA NINGUN DATO</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>






<!--************************************************-->
<!--             SCRIPT DE LA PAGINA                -->
<!--************************************************-->

<script src="controlador/js/ConceptosReutilizables.js"></script>
<script src="controlador/js/ventasconutilidad/UtilidadPorDocumentos.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        CambiarCbxConceptos();
        SeleccionarTodoCheck("chx_todos","tbody_tblconcepts");
        CheckboxSucu();
        ValidarFechasInicioFinFrm();
        EjecutarConsultaUtilidadPorDocumentos();
        BuscarFiltroAutocompletadoClienteInput("#inp_filter_client","#client");
        EjecutarSubConsultaUtilidadPorDocumentos();

 
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