    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

    include_once "controlador/ConexionBDControlador.php";
    $ControladorPrincipal = new ControladorPrincipal();
    $validacion = ControladorPrincipal::VerificarConexion();
    $ConexionBDControlador = new ConexionBDControlador();
    $ConexionBDControlador->GuardarConexionServerBd();
    if (isset($_SESSION['codigo_usuario']) && $validacion) {
        $data = $ControladorPrincipal->ValidarReporteUsuarioControlador("Conexión a Base de Datos","Configuración");
    }else{
        $data = true;
    }

    if ($data) :

        ?>

        <title>SRComrcial - ConexionBDD</title>


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

                <div class=" row justify-content-center align-items-center <?php echo (!$validacion ? "bg-gradient-primary vw-100 vh-100" : '')?> ">

                    <div class="container">

                        <!-- Outer Row -->
                        <div class="row justify-content-center ">

                            <div class="col-xl-10 col-lg-12 col-md-9 ">

                                <div class="card o-hidden border-0 shadow-lg my-5">
                                    <div class="card-body p-0">
                                        <!-- Nested Row within Card Body -->
                                        <div class="row justify-content-around">
                                            <div class="col-lg-12">
                                                <div class="p-5">   
                                                    <div class="text-center">
                                                        <h1 class="h4 text-gray-900 mb-4">MODCOMERCIAL <br> Parametros Base de Datos</h1>
                                                    </div>
                                                    <form class="user" method="POST" id="frm_conexiondb" autocomplete="off">
                                                        <div class="row row-cols-md-2 row-cols-1">
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <label class="form-label">Servidor</label>
                                                                    <input name="servidor" type="text" class="form-control form-control-user"
                                                                    id="servidor" aria-describedby="emailHelp"
                                                                    placeholder="IP.SERVIDOR\NOMBRESERVIDOR" value="<?php echo (isset($_SESSION['id_usuario'])? $_SESSION['server'] : '')?>">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="form-label">Base de datos</label>
                                                                    <input name="basededatos" type="text" class="form-control form-control-user"
                                                                    id="basededatos" aria-describedby="emailHelp"
                                                                    placeholder="Alguna base de datos" value="<?php echo (isset($_SESSION['id_usuario'])? $_SESSION['database'] : '')?>">
                                                                </div>
                                                            </div>
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <label class="form-label">Usuario</label>
                                                                    <input name="usuario" type="text" class="form-control form-control-user"
                                                                    id="usuario" placeholder="Usuariox2" value="<?php echo (isset($_SESSION['id_usuario'])? $_SESSION['userbd'] : '')?>">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="form-label">Contraseña</label>
                                                                    <input name="password" type="password" class="form-control form-control-user"
                                                                    id="password" placeholder="123456abc" value="<?php echo (isset($_SESSION['id_usuario'])? $_SESSION['password'] : '')?>">
                                                                </div>
                                                            </div>
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <button type="button" onclick="ProbarConexionServerBd()" class="btn btn-primary btn-user btn-block">
                                                                        Probar Conexión
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <button type="submit" name="btn_conectarservirdorbd" class="btn btn-primary btn-user btn-block">
                                                                        Conectar Servidor
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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



    <!--************************************************-->
    <!--             SCRIPT DE LA PAGINA                -->
    <!--************************************************-->
    <script src="controlador/js/ConexionBD.js"></script>

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


