<?php 
$ControladorPrincipal = new ControladorPrincipal(); 
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    


    <!-- Custom Icono for this template -->
    <link rel="shortcut icon" href="vista/imagenes/inconopagina.svg">

    <!-- Custom styles for this template -->
    <link href="vista/css/styleplantilla.css" rel="stylesheet">
    <link href="vista/css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>" rel="stylesheet">



    <!--sweetalert2-->
    <link rel="stylesheet" href="vista/vendor/sweetalert2/sweetalert2.min.css">
    <script src="vista/vendor/sweetalert2/sweetalert2.min.js"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="vista/vendor/jquery/jquery.min.js"></script>
    <script src="vista/vendor/jquery/jquery_ui.min.js"></script>
    <script src="vista/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- MULTISELECT -->
    <script type="text/javascript" src="vista/vendor/Multiselect-Boostrap/docs/js/prettify.min.js"></script>
    <link rel="stylesheet" href="vista/vendor/Multiselect-Boostrap/dist/css/bootstrap-multiselect.css" type="text/css">
    <script type="text/javascript" src="vista/vendor/Multiselect-Boostrap/dist/js/bootstrap-multiselect.js"></script>

    <!-- Custom fonts for this template -->
    <link href="vista/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

    <!-- Datatables -->
    <link href="vista/js/demo/DataTables/datatables.min.css" rel="stylesheet">
     
    <script src="vista/js/demo/DataTables/datatables.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            window.prettyPrint() && prettyPrint();
        });
    </script>

    <script src="vista/js/script.js"></script>

</head>

<body class="sidebar-toggled">
    <div id="loading-screen" style="display: none;">
        <img src="vista/imagenes/loadersvg.svg">
    </div>
    <?php
        $ControladorPrincipal->LinkPaginaControlador();
    ?>


    

</body>

</html>


<!-- Core plugin JavaScript-->
<script src="vista/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="vista/js/jsplantilla.min.js"></script>



