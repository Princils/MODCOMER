    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/ventasconutilidad/UtilidadPorAgenteControlador.php";
    include_once "modelo/UsuariosYPermisosModelo.php";
    $UtilidadPorAgenteControlador = new UtilidadPorAgenteControlador();
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();
    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Utilidad por Agentes","Ventas Con Utilidad");
    if ($data) :
        ?>

        <title>SRComrcial - Utilidad Por Agentes</title>


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
                    <h1 class="h3 mb-2 text-gray-800">Utilidad por Agentes</h1>
                    <p class="mb-4">Muestra todos los <b>documentos</b> que se elijan en el apartado de <b>conceptos</b>, dando una vista general de cada documento, al darle <b>clic</b> en alguno, muestra un <b>reporte más detallado</b> sobre el documento específico</b></p>

                     <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form class="filtros-compactos" id="frm_principal" action="POST" autocomplete="off">
                                <div class="row">
                                    <div class="col-12 col-md-4 my-2">
                                        <label class="form-label"><small style="font-size: 10px;" id="startagent" >Agente Inicial</small></label>
                                        <input type="text" name="startagentval" id="inp_filter_startagent" class="form-control form-control-sm">
                                        <label class="form-label"><small style="font-size: 10px;" id="endagent">Agente Final</small></label>
                                        <input type="text" name="endagentval" id="inp_filter_endagent" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-12 col-md-4  my-2">
                                        <label class="form-label">Fecha Inicial</label>
                                        <input type="date" class="form-control form-control-sm py-1" name="startdate" id="startdate">
                                        <label class="form-label">Fecha Final</label>
                                        <input type="date" disabled class="form-control form-control-sm py-1" name="endate" id="endate">
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
                                    <div class="col-12 col-md-4  my-2">
                                        <button type="button" class="btn btn-primary btn-sm my-3 fw-bold px-1 w-100" id="mdl_clasificationsclientes" data-toggle="modal" data-target="#ModalClasificacionesClients"  >Clasificación Clientes</button>
                                    </div>
                                    <div class="col-12 col-md-4  my-2">
                                        <button type="button" class="btn btn-primary btn-sm my-3 fw-bold px-1 w-100" id="mdl_clasificationsproducto" data-toggle="modal" data-target="#ModalClasificacionesProducts"  >Clasificación Producto</button>
                                    </div>
                                    <div class="col-12 col-md-4  my-2">
                                        <button type="button" class="btn btn-primary btn-sm my-3 fw-bold px-1 w-100" id="mdl_clasificationsagentes" data-toggle="modal" data-target="#ModalClasificacionesAgent"  >Clasificación Agente</button>
                                    </div>
                                    <div class="col-12">
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
                            
                            <div class="table-responsive" style="height: 60vh; overflow: auto; table-layout: fixed;">
                                <table class="table table-bordered table-striped" id="tbl_reporteprincipal"  cellspacing="0">
                                    <thead class="bg-primary text-white" style=" position: sticky; top: 0;">
                                        <tr>
                                            <th class="px-1">#</th>
                                            <th class="px-1">Codigó</th>
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




<div class="modal fade" id="ModalClasificacionesClients"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" style="min-width: 80%;">
    <div class="modal-content" >
      <div class="modal-header bg-primary" >
        <h5 class="modal-title text-white" id="TipoConcepto">Clasificación de Clientes</h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row row-cols-sm-2 row-cols-1">
          <div class="col">
            <small>Clasificacion 1 del Cliente</small>
            <select class="form-control" multiple size="5" id="cbx_clas1client" name="cbx_clas1client[]">
              <?php 
              $ConceptosReutilizables->AgregarCbxClasificacion1ClienteControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Tipo Cliente</small>
            <select class="form-control" multiple size="5" id="cbx_typeclient" name="cbx_typeclient[]">
              <?php 
              $ConceptosReutilizables->AddCbxTypoClienteControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Agente</small>
            <select class="form-control" multiple size="5" id="cbx_agent" name="cbx_agent[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClienteControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Zona</small>
            <select class="form-control" multiple size="5" id="cbx_zone" name="cbx_zone[]">
              <?php 
              $ConceptosReutilizables->AddCbxZonaControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasificacion 5 del Cliente</small>
            <select class="form-control" multiple size="5" id="cbx_clas5client" name="cbx_clas5client[]">
              <?php 
              $ConceptosReutilizables->AddCbxClasificacion5ClienteControlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasificacion 6 del Cliente</small>
            <select class="form-control" multiple size="5" id="cbx_clas6client" name="cbx_clas6client[]">
              <?php 
              $ConceptosReutilizables->AddCbxClasificacion6ClienteControlador();
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark fw-bold bg-primary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="ModalClasificacionesProducts"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" style="min-width: 80%;">
    <div class="modal-content" >
      <div class="modal-header bg-primary" >
        <h5 class="modal-title text-white" id="TipoConcepto">Clasificación de Productos</h5>
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
        <button type="button" class="btn btn-dark fw-bold bg-primary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="ModalClasificacionesAgent"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" style="min-width: 80%;">
    <div class="modal-content" >
      <div class="modal-header bg-primary" >
        <h5 class="modal-title text-white" id="TipoConcepto">Clasificación de Agentes</h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row row-cols-sm-2 row-cols-1">
          <div class="col">
            <small>Agente</small>
            <select class="form-control" multiple size="5" id="cbxAgent1" name="cbxAgent1[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgente1Controlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasification Agente 2</small>
            <select class="form-control" multiple size="5" id="agentClasification2" name="agentClasification2[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClasificacion2Controlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasification Agente 3</small>
            <select class="form-control" multiple size="5" id="agentClasification3" name="agentClasification3[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClasificacion3Controlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasification Agente 4</small>
            <select class="form-control" multiple size="5" id="agentClasification4" name="agentClasification4[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClasificacion4Controlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasification Agente 5</small>
            <select class="form-control" multiple size="5" id="agentClasification5" name="agentClasification5[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClasificacion5Controlador();
              ?>
            </select>
          </div>
          <div class="col">
            <small>Clasification Agente 6</small>
            <select class="form-control" multiple size="5" id="agentClasification6" name="agentClasification6[]">
              <?php 
              $ConceptosReutilizables->AddCbxAgenteClasificacion6Controlador();
              ?>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark fw-bold bg-primary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

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
id="Mdl_UtilidadPorAgentesSoloAgentes"
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
            <label id="unitdoc_total_filas" class="text-dark fw-bold mx-4"></label>
            <label id="unitdoc_total_ventas" class="text-dark fw-bold mx-4"></label>
            <label id="unitdoc_total_descuento" class="text-dark fw-bold mx-4"></label>
            <label id="unitdoc_total_costo" class="text-dark fw-bold mx-4"></label>
            <label id="unitdoc_total_utilidad" class="text-dark fw-bold mx-4"></label>
            <label id="unitdoc_total_margen" class="text-dark fw-bold mx-4"></label>
            </div>
        </div>
        <hr>
      <div class="my-3 p-4 " style="height: 45vh; overflow: auto; table-layout: fixed;">
        <table class="table table-bordered table-striped" id="tbl_SoloDocumentos">
          <thead class="text-white bg-primary" >
            <tr>
              <th class="px-1">#</th>
              <th class="py-1">Serie</th>
              <th class="py-1">Folio</th>
              <th class="py-1">Agente</th>
              <th class="py-1">Cliente</th>
              <th class="px-1" style="text-align: right;">Neto($)</th>
              <th class="px-1" style="text-align: right;">Descuento($)</th>
              <th class="px-1" style="text-align: right;">Costo($)</th>
              <th class="px-1" style="text-align: right;">Utilidad($)</th>
              <th class="px-1" style="text-align: right;">%</th>
            </tr>
          </thead>
          <tbody id="tbody_tblSoloDocumentos">
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

<!-- MODAL PARA PRODUCTOS SOLOS -->
<div
class="modal fade"
id="Mdl_UtilidadPorAgentesSoloProductos"
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
        <div class="container my-4 d-flex justify-content-center flex-wrap totals_min" >
          <label id="unitpro_total_filas" class="fw-bold text-dark m-3">totalfilas</label>
          <label id="unitpro_total_ventas" class="fw-bold text-dark m-3">total_ventas</label>
          <label id="unitpro_total_descuento" class="fw-bold text-dark m-3">total_descuento</label>
          <label id="unitpro_total_costo" class="fw-bold text-dark m-3">total_costo</label>
          <label id="unitpro_total_utilidad" class="fw-bold text-dark m-3">total_utilidad</label>
          <label id="unitpro_total_margen" class="fw-bold text-dark m-3">total_margen</label>
        </div>
        <hr>
      <div class="my-3 p-4 " style="height: 45vh; overflow: auto; table-layout: fixed;">
        <table class="table table-bordered table-striped" id="tbl_SoloProductos">
          <thead class="text-white bg-primary" >
            <tr>
              <th class="px-1">#</th>
              <th class="py-1">Código</th>
              <th class="py-1">Producto</th>
              <th class="py-1" style="text-align: right;">Unidades</th>
              <th class="px-1" style="text-align: right;">Neto($)</th>
              <th class="px-1" style="text-align: right;">Descuento($)</th>
              <th class="px-1" style="text-align: right;">Costo($)</th>
              <th class="px-1" style="text-align: right;">Utilidad($)</th>
              <th class="px-1" style="text-align: right;">%</th>
            </tr>
          </thead>
          <tbody id="tbody_tblSoloProductos">
            <tr>
              <td colspan="9" class="text-center">AUN NO SE SOLICITA NINGUN DATO</td>
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



<!-- MODAL PARA ELEGIR TIPO DE REPORTE -->
<div
class="modal fade "
id="mdl_select"
data-backdrop="static"
data-keyboard="false"
tabindex="-1"
aria-labelledby="staticBackdropLabel"
aria-hidden="true"
>
<div class="modal-dialog modal-dialog-centered" >
  <div class="modal-content">
    <div class="modal-header bg-primary">
      <h5 class="modal-title text-white fw-bold" id="staticBackdropLabel">Codigo de cliente: <label id="CodigoCliente"></label> <label id="idcliente" class="d-none" ></label></h5>
        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="modal-body">
      <div class="d-flex justify-content-center flex-wrap">
        

        <button type="button"  id="EjecutarConsultaUtilidadPorAgenteSoloDocumentos" data-toggle='modal' data-target='#Mdl_UtilidadPorAgentesSoloAgentes' class="btn btn-primary p-3 m-3 fw-bold" data-dismiss="modal">Documentos</button>
        <button type="button"  id="EjecutarConsultaUtilidadPorAgenteSoloProductos" data-toggle='modal' data-target='#Mdl_UtilidadPorAgentesSoloProductos' class="btn btn-primary p-3 m-3 fw-bold" data-dismiss="modal">Productos</button>
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
<script src="controlador/js/ventasconutilidad/UtilidadPorAgente.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        CambiarCbxConceptos();
        SeleccionarTodoCheck("chx_todos","tbody_tblconcepts");
        CheckboxSucu();
        ValidarFechasInicioFinFrm();
        BuscarFiltroAutocompletadoAgenteInput("#inp_filter_startagent","#startagent");
        BuscarFiltroAutocompletadoAgenteInput("#inp_filter_endagent","#endagent");
        EjecutarConsultaUtilidadPorAgente();
        AsignarDataUtilidadPorAgente();
        EjecutarConsultaUtilidadPorAgenteSoloDocumentos();
        EjecutarConsultaUtilidadPorAgenteSoloProductos();

 
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