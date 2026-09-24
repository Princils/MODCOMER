<?php
$ControladorPrincipal = new ControladorPrincipal();
$ConceptosReutilizables = new ConceptosReutilizablesControlador();
$permitido = $ControladorPrincipal->ValidarReporteUsuarioControlador('Revisión de Margenes de Protección', 'Compras');
if ($permitido):
    if (empty($_SESSION['csrf_margenes'])) {
        $_SESSION['csrf_margenes'] = bin2hex(random_bytes(32));
    }
?>
<title>MODCOMERCIAL - Márgenes de protección</title>
<div id="wrapper">
    <?php include 'vista/menus/navbar_lateral.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include 'vista/menus/navbar_top.php'; ?>
            <div class="container-fluid reporte-compacto">
                <h1 class="h3 mb-2 text-gray-800">Revisión de márgenes de protección</h1>
                <p>Compara el margen actual con el recomendado según la lista elegida, el costo histórico y la existencia al corte. Los precios y márgenes actuales corresponden al catálogo vigente.</p>
                <div class="card shadow mb-3">
                    <div class="card-body">
                        <form class="filtros-compactos" id="frm_margenes" method="post" autocomplete="off">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_margenes'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="row">
                                <div class="col-12 col-md-3">
                                    <label for="producto_inicial">Producto inicial</label>
                                    <input id="producto_inicial" class="form-control form-control-sm" placeholder="Vacío: desde el primero">
                                    <label for="producto_final" class="mt-2">Producto final</label>
                                    <input id="producto_final" class="form-control form-control-sm" placeholder="Vacío: hasta el último">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label for="endate">Fecha de corte</label>
                                    <input type="date" id="endate" name="endate" class="form-control form-control-sm" required>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" id="noinac" name="noinac" class="form-check-input" checked>
                                        <label for="noinac" class="form-check-label">Incluir inactivos</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label for="cbx_listprice">Lista de precios</label>
                                    <select id="cbx_listprice" name="cbx_listprice" class="form-control form-control-sm">
                                        <?php for ($lista = 1; $lista <= 5; $lista++): ?>
                                            <option value="<?= $lista ?>">Lista de precios <?= $lista ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" id="chxno0" name="chxno0" class="form-check-input" checked>
                                        <label for="chxno0" class="form-check-label">Suprimir precios en cero</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-toggle="modal" data-target="#ModalClasificaciones">Clasificaciones</button>
                                    <button type="submit" id="calcular_margenes" class="btn btn-success btn-sm w-100 mb-2" disabled>Calcular</button>
                                    <button type="button" id="preparar_ceros" class="btn btn-outline-danger btn-sm w-100 mb-2" disabled>Poner márgenes en cero</button>
                                    <button type="button" id="preparar_actualizacion" class="btn btn-outline-primary btn-sm w-100">Actualizar márgenes (lista 4)</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="small mb-2">El margen recomendado se redondea hacia abajo en pasos de 0.5 puntos. “Requiere subir/bajar” se refiere al margen de protección; no cambia las listas de precios.</p>
                <div id="resumen_margenes" class="mb-2" aria-live="polite"></div>
                <div class="card shadow mb-4">
                    <div class="card-body p-2">
                        <div class="table-responsive" style="max-height: 65vh; overflow: auto;">
                            <table id="tbl_margenes" class="table table-bordered table-striped" style="width: 100%;">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>#</th><th>Código</th><th>Producto</th><th>UM</th>
                                        <th>Precio</th><th>Costo</th><th>Margen actual (%)</th><th>Recomendado (%)</th>
                                        <th>Estatus</th><th>Precio 1</th><th>Precio 2</th><th>Precio 3</th>
                                        <th>Precio 4</th><th>Precio 5</th><th>Existencia</th>
                                    </tr>
                                </thead>
                                <tbody><tr><td colspan="15">AÚN NO SE SOLICITA NINGÚN REGISTRO</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalClasificaciones" tabindex="-1" aria-labelledby="titulo_clasificaciones" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 id="titulo_clasificaciones" class="modal-title">Clasificaciones de productos</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php
                    $clasificaciones = array(
                        array('cbx_LineGeneral', 'Línea general', 'AgregarCbxLineaGeneralControlador'),
                        array('cbx_LineDetailed', 'Línea detallada', 'AgregarCbxLineaDetalladaControlador'),
                        array('cbx_CommissionIndicator', 'Indicador de comisión', 'AgregarCbxIndicadorComisionControlador'),
                        array('cbx_TypeClassification', 'Tipo de clasificación', 'AgregarCbxTipoClasificacionControlador'),
                        array('cbx_Rotation', 'Rotación', 'AgregarCbxRotacionControlador'),
                        array('cbx_DailyReview', 'Revisión diaria', 'AgregarCbxRevisionDiariaControlador')
                    );
                    foreach ($clasificaciones as $clasificacion): ?>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="<?= $clasificacion[0] ?>"><?= $clasificacion[1] ?></label>
                            <select id="<?= $clasificacion[0] ?>" class="form-control" multiple size="5">
                                <?php $ConceptosReutilizables->{$clasificacion[2]}(); ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Guardar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalCambiosMargenes" tabindex="-1" data-backdrop="static" aria-labelledby="titulo_cambios" aria-hidden="true">
    <div class="modal-dialog modal-reporte">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 id="titulo_cambios" class="modal-title">Vista previa de cambios</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <p id="alcance_cambios"></p>
                <p id="cantidad_cambios" class="font-weight-bold"></p>
                <div class="table-responsive" style="max-height: 60vh; overflow: auto;">
                    <table id="tbl_cambios_margenes" class="table table-bordered" style="width: 100%;">
                        <thead class="bg-primary text-white"><tr><th>Código</th><th>Producto</th><th>Precio</th><th>Costo</th><th>Margen anterior (%)</th><th>Margen nuevo (%)</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="aplicar_margenes" class="btn btn-danger">Confirmar y aplicar cambios</button>
            </div>
        </div>
    </div>
</div>
<script src="controlador/js/compras/MargenesProteccion.js?v=<?= filemtime(__DIR__ . '/../../../../controlador/js/compras/MargenesProteccion.js') ?>"></script>
<?php else: ?>
<div class="container py-4">
    <div class="alert alert-warning">No tienes acceso a Márgenes de protección. Si se acaba de agregar el reporte, vuelve a iniciar sesión para actualizar tus permisos.</div>
    <a class="btn btn-primary" href="index.php?vista=Dashboard">Volver al inicio</a>
</div>
<?php endif; ?>
