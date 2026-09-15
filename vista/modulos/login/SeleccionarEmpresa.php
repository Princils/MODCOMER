    <?php 

        //******************************************
        //      CARGAR CONTROLADOR DE LA PAGINA
        //******************************************

        include_once "controlador/SeleccionarEmpresaControlador.php";
        include_once "modelo/SeleccionarEmpresaModelo.php";
        include_once "modelo/InicializadorTablasModelo.php";
        $SeleccionarEmpresaControlador = new SeleccionarEmpresaControlador();
        $SeleccionarEmpresaControlador->IniciarCompanyControlador();

    ?>
    <title>SRComrcial - ConexionBDD</title>

    <div class=" row justify-content-center align-items-center bg-gradient-primary vh-100  ">

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
                                            <h1 class="h4 text-gray-900 mb-4">Sistema Reporteador Comercial <br> Seleccionar Empresa</h1>
                                        </div>
                                        <form class="user" method="POST" id="frm_seleccionarempresa" autocomplete="off">
                                            <div class="row row-cols-md-2 row-cols-1 justify-content-around">
                                                <div class="col">
                                                    <div class="form-group">
                                                         <select class="form-control" name="company" id="cbx_selectcompany" required>
                                                            <option value="0">Seleccione una empresa</option>
                                                            <?php 
                                                                $SeleccionarEmpresaControlador->AgregearCBXCompanyControlador();
                                                             ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <button type="submit" disabled id="btn_seleccionarempresa" name="btn_seleccionarempresa" class="btn btn-primary btn-user btn-block">
                                                            Elegir
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



<!--************************************************-->
<!--             SCRIPT DE LA PAGINA                -->
<!--************************************************-->
<script src="controlador/js/SeleccionarEmpresaControlador.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        DesabilitarSubmit("cbx_selectcompany","btn_seleccionarempresa");
    });
</script>


