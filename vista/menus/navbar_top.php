<?php 
        $ControladorPrincipal = new ControladorPrincipal();
 ?>


                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow " id="page-top">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>



                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto justify-content-end">



                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow justify-content-end">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['nombre_usuario']; ?></span>
                                <img class="img-profile rounded-circle"
                                    src="plantilla_vista/img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <?php if ($ControladorPrincipal->ValidarReporteUsuarioControlador("Conexión a Base de Datos","Configuración")): ?>
                                <a class="dropdown-item" href="index.php?vista=conexion_bd">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Configuracion Base de datos
                                </a>
                                <?php endif ?>
                                <?php if ($ControladorPrincipal->ValidarReporteUsuarioControlador("Conceptos por Tienda","Configuración")): ?>
                                <a class="dropdown-item" href="index.php?vista=conceptosportienda">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Conceptos por Tienda
                                </a>
                                <?php endif ?>
                                <?php if ($ControladorPrincipal->ValidarReporteUsuarioControlador("Usuarios y Permisos","Configuración")): ?>
                                <a class="dropdown-item" href="index.php?vista=usuariosypermisos">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Usuarios y Permisos
                                </a>
                                <?php endif ?>

                                <!-- OPCIÓN PARA ASIGNAR NUEVA FECHA DE VENCIMIENTO DE LICENCIA -->
                                <?php if ($ControladorPrincipal->ValidarReporteUsuarioControlador("Usuarios y Permisos","Configuración")): ?>
                                <a class="dropdown-item" href="index.php?vista=asignarnuevovencimiento">
                                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Licencia y Vencimiento
                                </a>
                                <?php endif ?>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Salir de tablero
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">¿Seguro que quieres salir?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Selecciona Salir para Cerrar Sesión</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="index.php?vista=login">Salir</a>
                </div>
            </div>
        </div>
    </div>
                    <!-- End Logout Modal-->