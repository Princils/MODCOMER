<?php 
require_once "configuracion/Licencia.php";

$estadoLicencia = Licencia::ValidarEstadoLicencia();
$mensajeEstado = $estadoLicencia['mensaje'];
$fechaVencimientoActual = $estadoLicencia['fecha'];

// Formatear la fecha a dd/mm/yyyy para la vista si existe
$fechaFormateada = ($fechaVencimientoActual !== 'N/A' && !empty($fechaVencimientoActual)) 
    ? date('d/m/Y', strtotime($fechaVencimientoActual)) 
    : 'N/A';

// Procesar actualización
$respuestaRenovacion = null;
if (isset($_POST['btn_actualizar_licencia'])) {
    $nuevaFecha = $_POST['nueva_fecha'];
    $claveAdmin = $_POST['clave_admin'];

    $respuestaRenovacion = Licencia::ActualizarLicencia($nuevaFecha, $claveAdmin);
}
?>

<title>SRComercial - Renovación de Licencia</title>

<!-- Full Screen Gradient Background -->
<div class="bg-gradient-primary vw-100 vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-7 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-2">Sistema Reporteador Comercial</h1>
                                <h2 class="h6 text-danger font-weight-bold mb-4">Renovación de Licencia / Vencimiento</h2>
                            </div>

                            <!-- Alerta de estado con la fecha incluida -->
                            <div class="alert <?php echo ($estadoLicencia['valido'] ? 'alert-success' : 'alert-warning'); ?> text-center" role="alert">
                                <i class="fas <?php echo ($estadoLicencia['valido'] ? 'fa-check-circle' : 'fa-exclamation-triangle'); ?> mr-2"></i>
                                <b><?php echo htmlspecialchars($mensajeEstado); ?></b>
                                <br>
                                <small class="d-block mt-1">Fecha de Vencimiento: <b><?php echo $fechaFormateada; ?></b></small>
                            </div>

                            <form class="user mt-4" method="POST" autocomplete="off">
                                <div class="form-group">
                                    <label class="form-label text-gray-800 font-weight-bold">Nueva Fecha de Vencimiento</label>
                                    <input type="date" name="nueva_fecha" class="form-control form-control-user" required value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                                </div>

                                <div class="form-group">
                                    <label class="form-label text-gray-800 font-weight-bold">Contraseña de Administración</label>
                                    <input type="password" name="clave_admin" class="form-control form-control-user" placeholder="Ingrese contraseña máster" required>
                                </div>

                                <button type="submit" name="btn_actualizar_licencia" class="btn btn-primary btn-user btn-block mt-4">
                                    Actualizar y Reactivar Sistema
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- SweetAlert para respuestas -->
<?php if ($respuestaRenovacion !== null): ?>
    <?php if ($respuestaRenovacion['status'] === true): ?>
        <script type="text/javascript">
            Swal.fire({
                title: '¡LICENCIA ACTUALIZADA!',
                text: '<?php echo $respuestaRenovacion['mensaje']; ?>',
                icon: 'success',
                showConfirmButton: true,
                confirmButtonText: 'Ir al Login',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {      
                    window.location.href = "index.php?vista=login";   
                }
            });
        </script>
    <?php else: ?>
        <script type="text/javascript">
            Swal.fire({
                title: 'Error de Validación',
                text: '<?php echo addslashes($respuestaRenovacion['mensaje']); ?>',
                icon: 'error',
                confirmButtonText: 'Reintentar'
            });
        </script>
    <?php endif; ?>
<?php endif; ?>