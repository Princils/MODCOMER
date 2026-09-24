    <?php 
        include_once "controlador/LoginControlador.php";
        include_once "modelo/LoginModelo.php";
        include_once "modelo/InicializadorTablasModelo.php";
        $LoginControlador = new LoginControlador();
        $LoginControlador->IniciarSesionControlador();

        if (isset($_GET['error'])) {
            if ($_GET['error']= '1') {
                ?>
                <script type="text/javascript">
                 Swal.fire({
                    title: 'Datos Incorrectos',
                    text: 'Contraseña o usuario incorrecto intente nuevamente',
                    icon: 'error',
                    showConfirmButton: false,
                    confirmButtonText: 'Continuar',
                    timer: 1500
                })
            </script>
                <?php
            }
        }
    ?>


    <title>SRComrcial - Login</title>

    <div class="vh-100  row justify-content-center align-items-center bg-gradient-primary">

        <div class="container">

            <!-- Outer Row -->
            <div class="row justify-content-center ">

                <div class="col-xl-10 col-lg-12 col-md-9 ">

                    <div class="card o-hidden border-0 shadow-lg my-5">
                        <div class="card-body p-0">
                            <!-- Nested Row within Card Body -->
                            <div class="row">
                                <div class="col-lg-6 d-none d-lg-block "><img src="vista/imagenes/loginimg.jpg" class="w-100" alt=""></div>
                                <div class="col-lg-6">
                                    <div class="p-5">   
                                        <div class="text-center">
                                            <img src="vista/imagenes/coproi.jpeg" class="coproi-access-logo" alt="Soluciones COPROI" width="1197" height="360">
                                            <h1 class="h4 text-gray-900 mb-4">MODCOMERCIAL</h1>
                                        </div>
                                        <form class="user" method="POST" id="login">
                                            <div class="form-group">
                                                <input name="usuario" type="text" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Tu Usuario">
                                            </div>
                                            <div class="form-group">
                                                <input name="password" type="password" class="form-control form-control-user"
                                                id="password" placeholder="Contraseña">
                                            </div>
                                            <button type="submit" name="verrificarsession" class="btn btn-primary btn-user btn-block">
                                                Iniciar Sesión
                                            </button>
                                            
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

