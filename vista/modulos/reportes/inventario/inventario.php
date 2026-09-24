<?php
$permitido = ControladorPrincipal::ValidarReporteUsuarioControlador('Inventario', 'Inventario');
if ($permitido):
    $_SESSION['csrf_inventario'] = $_SESSION['csrf_inventario'] ?? bin2hex(random_bytes(32));
?>
<title>MODCOMERCIAL - Inventario</title>
<div id="wrapper">
    <?php include 'vista/menus/navbar_lateral.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include 'vista/menus/navbar_top.php'; ?>
        <div class="container-fluid reporte-compacto">
            <h1 class="h3 mb-2 text-gray-800">Inventario</h1>
            <p>Consulta existencias y costo promedio a la fecha de corte. Las 10 listas de precios corresponden al catálogo vigente.</p>
            <div class="card shadow mb-3"><div class="card-body">
                <form class="filtros-compactos" id="frm_inventario" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_inventario'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="inventario_inicial">Producto inicial</label>
                            <input id="inventario_inicial" name="inicial" class="form-control form-control-sm" placeholder="Vacío: desde el primero">
                            <label for="inventario_final" class="mt-2">Producto final</label>
                            <input id="inventario_final" name="final" class="form-control form-control-sm" placeholder="Vacío: hasta el último">
                            <div class="form-check mt-2"><input type="checkbox" name="soloexistencia" id="inventario_existencia" class="form-check-input" checked><label for="inventario_existencia" class="form-check-label">Solo existencia positiva</label></div>
                        </div>
                        <div class="col-md-4">
                            <label for="inventario_corte">Fecha de corte</label>
                            <input type="date" id="inventario_corte" name="endate" class="form-control form-control-sm" required>
                            <label for="inventario_estado" class="mt-2">Estado de productos</label>
                            <select id="inventario_estado" name="estado" class="form-control form-control-sm"><option value="1">Activos</option><option value="2">Inactivos</option><option value="3">Ambos</option></select>
                        </div>
                        <div class="col-md-4 pt-4">
                            <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-toggle="modal" data-target="#inventario_almacenes_modal">Almacenes</button>
                            <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-toggle="modal" data-target="#inventario_clasificaciones_modal">Clasificaciones</button>
                            <button type="submit" id="inventario_calcular" class="btn btn-success btn-sm w-100" disabled>Calcular</button>
                        </div>
                    </div>
                </form>
                <p class="small mt-3 mb-0" id="inventario_filtros">Cargando filtros…</p>
                <p class="small mb-0">Sin selección se incluyen todos los almacenes. Selecciona uno para consultar mínimos, máximos y reorden; con varios se muestran como “—”.</p>
            </div></div>
            <div id="inventario_resumen" class="mb-2" role="status" aria-live="polite"></div>
            <div class="card shadow mb-4"><div class="card-body p-2">
                <table id="tbl_inventario" class="table table-striped table-bordered table-sm" style="width:100%"><thead class="bg-primary text-white"><tr>
                    <th>#</th><th>Código</th><th>Producto</th><th>UM</th><th>Impuesto (%)</th><th>Familia</th><th>Detallada</th><th>Existencia</th><th>Costo promedio</th><th>Mínimo</th><th>Máximo</th><th>Reorden mín.</th><th>Reorden máx.</th>
                    <?php for ($lista = 1; $lista <= 10; $lista++): ?><th>Precio #<?= $lista ?></th><?php endfor; ?>
                </tr></thead><tbody></tbody></table>
            </div></div>
        </div>
    </div></div>
</div>
<div class="modal fade" id="inventario_almacenes_modal" tabindex="-1" aria-labelledby="inventario_titulo_almacenes" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 id="inventario_titulo_almacenes">Almacenes</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">&times;</button></div>
    <div class="modal-body"><label for="inventario_almacenes">Selecciona uno o varios almacenes</label><select id="inventario_almacenes" multiple size="10" class="form-control"></select><button type="button" class="btn btn-link limpiar-inventario" data-select="inventario_almacenes">Quitar selección (todos)</button></div>
    <div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Guardar</button></div>
</div></div></div>
<div class="modal fade" id="inventario_clasificaciones_modal" tabindex="-1" aria-labelledby="inventario_titulo_clasificaciones" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 id="inventario_titulo_clasificaciones">Clasificaciones</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">&times;</button></div>
    <div class="modal-body"><div class="row">
        <?php foreach (array(1=>'Línea general', 2=>'Línea detallada', 3=>'Tipo de clasificación', 4=>'Indicador de comisión', 5=>'Rotación', 6=>'Revisión diaria') as $numero=>$nombre): ?>
        <div class="col-md-6 mb-3"><label for="inventario_clasificacion<?= $numero ?>"><?= $nombre ?></label><select id="inventario_clasificacion<?= $numero ?>" multiple size="5" class="form-control"></select><button type="button" class="btn btn-link limpiar-inventario" data-select="inventario_clasificacion<?= $numero ?>">Quitar selección (todas)</button></div>
        <?php endforeach; ?>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Guardar</button></div>
</div></div></div>
<style>
#tbl_inventario th,#tbl_inventario td{white-space:nowrap;padding:3px 6px!important}
#tbl_inventario_wrapper{width:100%;min-width:0}
.inventario-scroll{width:100%;max-height:55vh;overflow:auto}
#tbl_inventario{border-collapse:separate;border-spacing:0;margin:0!important}
#tbl_inventario thead th{position:sticky;top:0;z-index:2;background:var(--coproi-dark, #13364b);padding-right:22px!important}
.ui-autocomplete{max-height:260px;overflow-y:auto;z-index:1060}
</style>
<script src="controlador/js/inventario/Inventario.js?v=<?= filemtime('controlador/js/inventario/Inventario.js') ?>"></script>
<?php else: ?>
<div class="container mt-5"><div class="alert alert-warning">No tienes permiso para Inventario. Vuelve a iniciar sesión si se acaba de agregar o solicita acceso al administrador.</div><a href="index.php?vista=Dashboard" class="btn btn-primary">Inicio</a></div>
<?php endif; ?>
