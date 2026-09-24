    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "modelo/ventasconutilidad/UtilidadPorClasificacionModelo.php";
    include_once "modelo/UsuariosYPermisosModelo.php";
    $ConceptosReutilizables = new ConceptosReutilizablesControlador();
    $ControladorPrincipal = new ControladorPrincipal();

    $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Utilidad por Clasificación","Ventas Con Utilidad");
    if ($data) :
        ?>

        <title>MODCOMERCIAL - Utilidad Por Clasificación</title>


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
                    <h1 class="h3 mb-2 text-gray-800">Utilidad por Clasificación</h1>
                    <p class="mb-4">Consulta ventas, descuentos, costos, utilidad y margen por clasificación de productos, agrupados por agente, cliente o ambos. Haz clic en una fila para consultar sus productos.</p>

                     <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form class="filtros-compactos filtros-clasificacion" id="frm_principal" method="post" autocomplete="off">
                                <div class="row">
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
                                        <button type="button" class="btn btn-primary btn-sm my-3 fw-bold px-1 w-100" id="mdl_clasificationsagentes" data-toggle="modal" data-target="#ModalClasificacionesAgent"  >Clasificación Agente</button>
                                    </div>

<div class="col-12 col-md-4 my-2">
<label for="modo">Agrupar por</label>
<select id="modo" name="modo" class="form-control form-control-sm"><option value="0">Ambos</option><option value="1">Agente</option><option value="2">Cliente</option></select>
<div id="filtro_agente"><label for="inp_filter_agent">Agente (vacío: todos)</label><input id="inp_filter_agent" class="form-control form-control-sm" placeholder="Código o nombre"></div>
<div id="filtro_cliente"><label for="inp_filter_client">Cliente (vacío: todos)</label><input id="inp_filter_client" class="form-control form-control-sm" placeholder="Código o razón social"></div>
</div>
<div class="col-12 col-md-4 my-2">
<label for="clasificacion">Clasificación de productos</label>
<select id="clasificacion" name="clasificacion" class="form-control form-control-sm">
<?php foreach (UtilidadPorClasificacionModelo::Clasificaciones() as $clasificacion): ?>
<option value="<?= (int)$clasificacion['CIDCLASIFICACION'] ?>"><?= htmlspecialchars($clasificacion['CNOMBRECLASIFICACION'], ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
</select>
<div class="form-check mt-3"><input type="checkbox" id="servicios" checked class="form-check-input"><label for="servicios" class="form-check-label">Incluir servicios</label></div>
<div class="form-check mt-3"><input type="checkbox" id="ceros" class="form-check-input"><label for="ceros" class="form-check-label">Suprimir ceros</label></div>
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
                                    <thead class="text-white bg-primary" >
                                        <tr>
                                        <th class="px-1">#</th>
                                        <th class="px-1">Clasificación</th><th class="px-1">Agente</th><th class="px-1">Razón social</th>
                                        <th class="px-1" style="text-align: right;">Neto($)</th>
                                        <th class="px-1" style="text-align: right;">Descuento($)</th>
                                        <th class="px-1" style="text-align: right;">Costo($)</th>
                                        <th class="px-1" style="text-align: right;">Utilidad($)</th>
                                        <th class="px-1" style="text-align: right;">%</th><th class="px-1" style="text-align: right;">Total ventas</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_tblreporteprincipal">
                                        <tr >
                                        <td colspan="10" class="text-center">AÚN NO SE SOLICITA NINGÚN REGISTRO</td>
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
        <h5 class="modal-title text-white" id="TituloClasificacionesClientes">Clasificación de Clientes</h5>
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

<div class="modal fade" id="ModalClasificacionesAgent"  data-backdrop="static" data-keyboard="false" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog" style="min-width: 80%;">
    <div class="modal-content" >
      <div class="modal-header bg-primary" >
        <h5 class="modal-title text-white" id="TituloClasificacionesAgentes">Clasificación de Agentes</h5>
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


<!-- MODAL PARA PRODUCTOS SOLOS -->
<div
class="modal fade"
id="Mdl_UtilidadPorClasificacionProductos"
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
      <div class="my-2 p-1 " style="height: 60vh; overflow: auto; table-layout: fixed;">
        <table class="table table-bordered table-striped" id="tbl_SoloProductos" style="width: 100%;">
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



<script src="controlador/js/ConceptosReutilizables.js"></script>
<script src="controlador/js/ventasconutilidad/UtilidadPorClasificacion.js"></script>
<script>
$(function () {
CambiarCbxConceptos();
SeleccionarTodoCheck("chx_todos", "tbody_tblconcepts");
CheckboxSucu();
ValidarFechasInicioFinFrm();
InicializarUtilidadPorClasificacion();
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