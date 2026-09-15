<?php
include_once ('../../vista/vendor/fpdf/fpdf.php');

class PDF extends FPDF
{
    private $headerTitle; // Nuevo atributo para almacenar el título de la cabecera
    private $headerFields; // Nuevo atributo para almacenar la información de los campos del encabezado

    public function setHeaderTitle($title)
    {
        $this->headerTitle = $title;
        $this->typerow = 1;
    }

    public function setHeaderTitleL($title)
    {
        $this->headerTitle = $title;
        $this->typerow = 2;
    }


    public function setHeaderFields($fields)
    {
        $this->headerFields = $fields;
    }

    function drawHeader()
    {
        if (!empty($this->headerFields)) {
            // Encabezado de la tabla
            $this->SetFont('Arial', 'B', 10);
            foreach ($this->headerFields as $field) {
                $this->Cell($field['width'], 15, $field['title'], 0, 0, $field['align']);
            }
            $this->Ln();
        }
    }

    function Header()
    {
        if ($this->typerow == 1) {
            // Logo
            $this->Image('../../vista/imagenes/reporte.png', 10, 8, 25);
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            // Movernos a la derecha
            // Título personalizado
            $this->Cell(0, 15, 'SRComercial', 0, 0, 'C');
            // Salto de línea
            $this->Ln(15);
            // Movernos a la derecha
            // Título personalizado
            $this->Cell(0, 10, $this->headerTitle, 0, 0, 'C');
            // Salto de línea
            $this->Ln(20);

            // Dibujar el encabezado de la tabla si se proporcionaron campos
            $this->drawHeader();
        }else{
            // Logo
            $this->Image('../../vista/imagenes/reporte.png', 10, 8, 25);
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            // Movernos a la derecha
            // Título personalizado
            $this->Cell(0, 15, 'SRComercial', 0, 0, 'C');
            // Salto de línea
            $this->Ln(15);
            // Movernos a la derecha
            // Título personalizado
            $this->Cell(0, 10, $this->headerTitle, 0, 0, 'C');
            // Salto de línea
            $this->Ln(20);

            // Dibujar el encabezado de la tabla si se proporcionaron campos
            $this->drawHeader();            
        }

    }


    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}

?>