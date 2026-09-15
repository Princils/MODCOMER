    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/ventasconutilidad/UtilidadPorProductosControlador.php";
    include_once "modelo/UsuariosYPermisosModelo.php";
    $UtilidadPorProductosControlador = new UtilidadPorProductosControlador();
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();

    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Utilidad por Productos","Ventas Con Utilidad");
    if ($data) :
        ?>

        <title>SRComrcial - Utilidad Por Productos</title>


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
                    <h1 class="h3 mb-2 text-gray-800">Utilidad por Productos</h1>
                    <p class="mb-4">Muestra <b>todos los Productos que vendieron</b> entre las fechas seleccionadas, otorgando una vista hacia <b>cantidad y totales</b>, de manera paralela al darle clic en algún producto, muestra los <b>documentos donde se extraen</b> los datos. Si se selecciona algún <b>agente</b>, mostrará únicamente los <b>productos vendidos por ese agente</b>.</p>

                     <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form id="frm_utilidadporproductos" action="POST" autocomplete="off">
                                <div class="row mb-3">
                                    <div class="col-12 col-md-4 my-2 ">
                                        <label class="form-label"><small style="font-size: 10px;" id="startproduct" >Producto Inicial</small></label>
                                        <input type="text" name="startproductval" id="inp_filter_startproduct" class="form-control form-control-sm">
                                        <label class="form-label"><small style="font-size: 10px;" id="endproduct">Producto Final</small></label>
                                        <input type="text" name="endproductval" id="inp_filter_endproduct" class="form-control form-control-sm">
                                        <label class="form-label"><small style="font-size: 10px;" id="startagent" >Agente</small></label>
                                        <input type="text" name="agentval" id="inp_filter_startagent" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-4 my-2 ">
                                        <label class="form-label">Fecha Inicial</label>
                                        <input type="date" class="form-control form-control-sm py-1" name="startdate" id="startdate">
                                        <label class="form-label">Fecha Final</label>
                                        <input type="date" disabled class="form-control form-control-sm py-1" name="endate" id="endate">
                                        <div class="form-check m-4">
                                          <input class="form-check-input mycheck2 my-1" checked type="checkbox" value="chxserv" name="chxserv" id="chxserv" />
                                          <label class="form-check-label ml-3 mt-1" for="chxserv">Incluir Servicios</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 my-2  ">
                                        <label class="form-label">Documento</label>
                                        <select class="form-control form-control-sm" id="cbx_document" name="cbx_document">
                                          <option value="0">(TODOS)</option>
                                          <?php 
                                          $ConceptosReutilizables->AgregarCbxDocumentosControlador();
                                          ?>
                                        </select>
                                        <label class="form-label d-block">Conceptos</label>
                                        <button type="button" class="btn w-100 btn-sm btn-primary  fw-bold btn_validatedate" id="mdl_concepts" data-toggle="modal" data-target="#ModalConceptos" disabled>Ver</button>
                                        <button type="button" class="btn btn-primary btn-sm my-3 fw-bold px-1 w-100" id="mdl_clasifications" data-toggle="modal" data-target="#ModalClasificaciones"  >Clasificaciones</button>
                                    </div>
                                    <div class="col-12  my-2 ">
                                        <button type="button" id="btn_calculate" disabled name="btn_calculate" class="btn_validatedate btn btn-success w-100  fw-bold BtnCalcularReportePrincipal" >Calcular</button>
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
                            
                            <div class="table-responsive" style="height: 60vh;">
                                <table class="table table-bordered table-striped" id="tbl_reporteprincipal"  cellspacing="0">
                                    <thead class="bg-primary text-white" style=" position: sticky; top: 0;">
                                        <tr>
                                            <th class="py-1">#</th>
                                            <th class="py-1">Codigo</th>
                                            <th class="py-1 " >Producto</th>
                                            <th class="py-1" style="text-align: right;">Pzas</th>
                                            <th class="py-1" style="text-align: right;">Neto($)</th>
                                            <th class="py-1" style="text-align: right;">Descuento($)</th>
                                            <th class="py-1" style="text-align: right;">Costo($)</th>
                                            <th class="py-1" style="text-align: right;">Utilidad($)</th>
                                            <th class="py-1" style="text-align: right;">%</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_tblreporteprincipal">
                                        <tr >
                                        <td colspan="11" class="text-center">AÚN NO SE SOLICITA NINGÚN REGISTRO</td>
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




<div class="modal fade" id="ModalClasificaciones"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" style="min-width: 80%;">
    <div class="modal-content" >
      <div class="modal-header bg-primary" >
        <h5 class="modal-title text-white" id="TipoConcepto">Clasificaciones </h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-cols-md-2 row-cols-1">
          <div class="col">
            <small>Linea General</small>
            <select class="form-control" multiple size="5" id="cbx_LineGeneral" name="cbx_LineGeneral[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxLineaGeneralControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Linea Detallada</small>
            <select class="form-control" multiple size="5" id="cbx_LineDetailed" name="cbx_LineDetailed[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxLineaDetalladaControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Indicador Comision</small>
            <select class="form-control" multiple size="5" id="cbx_CommissionIndicator" name="cbx_CommissionIndicator[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxIndicadorComisionControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Tipo Clasificación</small>
            <select class="form-control" multiple size="5" id="cbx_TypeClassification" name="cbx_TypeClassification[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxTipoClasificacionControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Rotacion</small>
            <select class="form-control" multiple size="5" id="cbx_Rotation" name="cbx_Rotation[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxRotacionControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Revision Diaria</small>
            <select class="form-control" multiple size="5" id="cbx_DailyReview" name="cbx_DailyReview[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxRevisionDiariaControlador();
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-dark fw-bold" data-dismiss="modal" style="background-color: #08207c ">Guardar</button>
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
    <div class="modal-body">
      <div class="container my-1 d-flex justify-content-center flex-wrap">
        <label id="unit_total_filas" class="fw-bold text-dark mx-4"></label>
        <label id="unit_total_ventas" class="fw-bold text-dark mx-4"></label>
        <label id="unit_total_descuento" class="fw-bold text-dark mx-4"></label>
        <label id="unit_total_utilidad" class="fw-bold text-dark mx-4"></label>
        <label id="unit_total_margen" class="fw-bold text-dark mx-4"></label>
        <label id="unit_total_costo" class="fw-bold text-dark mx-4"></label>
      </div>
      <hr>
      <div class="my-3 mx-4" id="div_tblreportutilitydocumentOnly" style="height: 45vh; overflow: auto; table-layout: fixed;">
        <table class="table table-bordered table-striped" cellspacing="0" id="tbl_secundaria">
          <thead class="text-white bg-primary" style="position: sticky; top: 0;">
            <tr>
              <th class="py-1">#</th>
              <th class="py-1">Serie</th>
              <th class="py-1">Folio</th>
              <th class="py-1">Fecha</th>
              <th class="py-1">Codigo</th>
              <th class="py-1">Cliente</th>
              <th class="py-1">Codigo</th>
              <th class="py-1">Agente</th>
              <th class="py-1" style="text-align: right;">Unidades</th>
              <th class="py-1">UM</th>
              <th class="py-1" style="text-align: right;">Neto($)</th>
              <th class="py-1" style="text-align: right;">Descuento($)</th>
              <th class="py-1" style="text-align: right;">Costo($)</th>
              <th class="py-1" style="text-align: right;">Utilidad($)</th>
              <th class="py-1" style="text-align: right;">%</th>
            </tr>
          </thead>
          <tbody id="tbody_tblsecundaria">
            <tr>
              <td colspan="16" class="text-center">AUN NO SE SOLICITA NINGUN DATO</td>
            </tr>
            <tr>
            </tbody>
          </table>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button"  class="btn btn-danger fw-bold" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



<!--************************************************-->
<!--             SCRIPT DE LA PAGINA                -->
<!--************************************************-->

<script src="controlador/js/ConceptosReutilizables.js"></script>
<script src="controlador/js/ventasconutilidad/UtilidadPorProductos.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        CambiarCbxConceptos();
        SeleccionarTodoCheck("chx_todos","tbody_tblconcepts");
        CheckboxSucu();
        ValidarFechasInicioFinFrm();
        EjecutarConsultaUtilidadPorProductos();
        BuscarFiltroAutocompletadoAgenteInput("#inp_filter_startagent","#startagent");
        BuscarFiltroAutocompletadoProductoInput("#inp_filter_startproduct","#startproduct");
        BuscarFiltroAutocompletadoProductoInput("#inp_filter_endproduct","#endproduct");
        EjecutarSubConsultaUtilidadPorProductos();

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