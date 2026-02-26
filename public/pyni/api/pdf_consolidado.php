<?php
/**
 * API: Generar PDF consolidado de múltiples estudiantes
 * Endpoint: GET /api/pdf_consolidado.php?codigos[]=...&codigos[]=...
 * Una página por estudiante
 */

ini_set('memory_limit', '512M');
ob_start();

require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}

$codigos = $_GET['codigos'] ?? [];

if (empty($codigos) || !is_array($codigos)) {
    http_response_code(400);
    echo 'Códigos no proporcionados';
    exit;
}

$db = getDbConnection();
if (!$db) {
    http_response_code(500);
    echo 'Error de conexión a la base de datos';
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    $sql = "SELECT eg.*, s.sede 
            FROM estugrupos eg 
            LEFT JOIN sedes s ON eg.asignacion = s.ind 
            WHERE eg.codigo IN ($placeholders)
            ORDER BY eg.year DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($codigos);
    $estudiantes = $stmt->fetchAll();

    if (empty($estudiantes)) {
        http_response_code(404);
        echo 'No se encontraron estudiantes';
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al buscar estudiantes: " . $e->getMessage());
    http_response_code(500);
    echo 'Error al buscar estudiantes';
    exit;
}

$tcpdfPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($tcpdfPath)) {
    $tcpdfPath = dirname(__DIR__) . '/vendor/autoload.php';
}

if (file_exists($tcpdfPath)) {
    require_once $tcpdfPath;
} else {
    http_response_code(500);
    echo 'Error: TCPDF no está instalado';
    exit;
}

$ML = 8;
$MR = 8;
$MT = 8;
$MB = 13;

$pdf = new TCPDF('P', 'mm', 'LEGAL', true, 'UTF-8', false);
$pdf->SetCreator('IEOccidente');
$pdf->SetAuthor('Institución Educativa de Occidente');
$pdf->SetTitle('Registros de Matrícula');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.3);
$pdf->SetMargins($ML, $MT, $MR);
$pdf->SetAutoPageBreak(true, $MB);

$pageWidth = $pdf->getPageWidth();
$innerWidth = $pageWidth - $ML - $MR;

function generateStudentPage(TCPDF $pdf, array $estudiante, float $ML, float $MR, float $MT, float $MB, float $innerWidth): void
{
    $pdf->AddPage();
    
    $headerRowH = 30;
    $headerRow2H = 6;
    $totalHeaderH = $headerRowH + $headerRow2H;
    
    $cW1 = 28;
    $cW3 = 32;
    $cW2 = $innerWidth - $cW1 - $cW3;
    
    $xC1 = $ML;
    $xC2 = $ML + $cW1;
    $xC3 = $ML + $cW1 + $cW2;
    
    $escudoPath = __DIR__ . '/images/escudo.jpg';
    if (file_exists($escudoPath)) {
        $imgW = 22;
        $imgH = 26;
        $imgX = $xC1 + ($cW1 - $imgW) / 2;
        $pdf->Image($escudoPath, $imgX, $MT + 2, $imgW, $imgH, 'JPG');
    }
    
    $pdf->SetXY($xC2, $MT + 1);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->Cell($cW2, 4, 'SECRETARIA DE EDUCACION DEL DEPARTAMENTO DE CALDAS', 0, 2, 'C');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell($cW2, 4.5, 'INSTITUCION EDUCATIVA DE OCCIDENTE', 0, 2, 'C');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell($cW2, 3.5, 'NIT 890802641-2  DANE 117042000561', 0, 2, 'C');
    $pdf->SetFont('helvetica', '', 5.5);
    $pdf->MultiCell($cW2, 2.8, 
        "INSTITUCION EDUCATIVA DE OCCIDENTE DE ANSERMA CALDAS PLANTEL OFICIAL APROBADO POR RESOLUCION No\n" .
        "4859-6 DE JUNIO 23 DE 2017, RESOLUCION DE FUSION No 00507 DE MARZO 6 DE 2003 EMANADA DE LA SECRETARIA\n" .
        "DE EDUCACION DEPARTAMENTAL Y SEGUN PLAN DE ESTUDIOS LEY 115 Y DECRETO 1860.",
        0, 'C', false, 0);
    
    $pdf->SetXY($xC3, $MT + 2);
    $pdf->SetFont('helvetica', '', 7);
    $pageNum = $pdf->getPage();
    $pdf->Cell($cW3, 3.5, "Pagina: $pageNum", 0, 2, 'C');
    $pdf->SetX($xC3);
    $pdf->Cell($cW3, 3.5, 'Codigo: ' . ($estudiante['codigo'] ?? ''), 0, 2, 'C');
    $pdf->SetX($xC3);
    $pdf->Cell($cW3, 3.5, 'GAFMIR-40-02', 0, 2, 'C');
    $pdf->SetX($xC3);
    $pdf->Cell($cW3, 3.5, 'v.02', 0, 2, 'C');
    $pdf->SetX($xC3);
    $pdf->Cell($cW3, 3.5, '26/01/2012', 0, 2, 'C');
    
    $yFila2 = $MT + $headerRowH;
    $pdf->SetXY($xC1, $yFila2);
    $pdf->SetFont('helvetica', '', 6);
    $pdf->Cell($cW1, $headerRow2H, '', 0, 0, 'C');
    
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell($cW2, $headerRow2H, 'REGISTRO DE MATRICULA (SIMAT - EDUADMIN)', 0, 0, 'C');
    
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell($cW3, $headerRow2H / 2, 'DEPENDENCIA', 0, 2, 'C');
    $pdf->SetX($xC3);
    $pdf->Cell($cW3, $headerRow2H / 2, 'SECRETARIA', 0, 0, 'C');
    
    $yH0 = $MT;
    $yH1 = $MT + $headerRowH;
    $yH2 = $MT + $totalHeaderH;
    
    $pdf->Rect($xC1, $yH0, $innerWidth, $totalHeaderH);
    $pdf->Line($xC1, $yH1, $xC1 + $innerWidth, $yH1);
    $pdf->Line($xC2, $yH0, $xC2, $yH2);
    $pdf->Line($xC3, $yH0, $xC3, $yH2);
    
    $pdf->SetXY($ML, $yH2 + 3);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetX($ML);
    $pdf->Cell($innerWidth, 6, 'Informacion del Estudiante', 0, 1, 'C');
    $pdf->Ln(0.5);
    
    $yStart = $pdf->GetY();
    
    $wL = 38;
    $wV = 60;
    $wR = 38;
    $wVR = $innerWidth - $wL - $wV - $wR;
    $rowH = 3.5;
    
    $row4 = function (string $l1, string $v1, string $l2, string $v2) use ($pdf, $ML, $wL, $wV, $wR, $wVR, $rowH): void {
        $pdf->SetX($ML);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell($wL, $rowH, $l1, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($wV, $rowH, $v1, 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell($wR, $rowH, $l2, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($wVR, $rowH, $v2, 0, 1, 'L');
    };
    
    $row2 = function (string $l1, string $v1) use ($pdf, $ML, $wL, $innerWidth, $rowH): void {
        $pdf->SetX($ML);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell($wL, $rowH, $l1, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($innerWidth - $wL, $rowH, $v1, 0, 1, 'L');
    };
    
    $row4('Código:', $estudiante['codigo'] ?? 'N/A', 'Año:', $estudiante['year'] ?? 'N/A');
    $row4('Identificacion:', $estudiante['estudiante'] ?? 'N/A', 'Tipo de sangre:', $estudiante['tipoSangre'] ?? 'N/A');
    $row4('Nombres:', $estudiante['nombres'] ?? 'N/A', 'Genero:', $estudiante['genero'] ?? 'N/A');
    $row4('Email Estudiante:', $estudiante['email_estudiante'] ?? 'N/A', 'Sede:', $estudiante['sede'] ?? 'N/A');
    $row4('Fecha de Nacimiento:', $estudiante['fecnac'] ?? 'N/A', 'Edad:', $estudiante['edad'] ?? 'N/A');
    $row2('Lugar de Nacimiento:', $estudiante['lugarNacimiento'] ?? 'N/A');
    $row4('Tipo de Documento:', $estudiante['tdei'] ?? 'N/A', 'Fecha de Expedicion:', $estudiante['fechaExpedicion'] ?? 'N/A');
    $row2('Lugar de Expedicion:', $estudiante['lugarExpedicion'] ?? 'N/A');
    
    $yEnd = $pdf->GetY();
    $pdf->Rect($ML, $yStart - 0.5, $innerWidth, $yEnd - $yStart + 1);
    $pdf->Ln(2);
    
    $yStart2 = $pdf->GetY();
    
    $row4('Telefono 1:', $estudiante['telefono1'] ?? 'N/A', 'Telefono 2:', $estudiante['telefono2'] ?? 'N/A');
    $row4('Direccion:', $estudiante['direccion'] ?? 'N/A', 'Zona y lugar:', $estudiante['lugar'] ?? 'N/A');
    $row4('Nivel Sisben:', $estudiante['sisben'] ?? 'N/A', 'Estrato:', $estudiante['estrato'] ?? 'N/A');
    $row4('RGSS:', ($estudiante['eps'] ?? 'N/A') . ' - Activo:', '', $estudiante['activo'] ?? 'N/A');
    $row4('Banda:', $estudiante['banda'] ?? 'N/A', 'Desertor:', $estudiante['desertor'] ?? 'N/A');
    $row4('Estado anterior:', $estudiante['eanterior'] ?? 'N/A', 'Estado:', $estudiante['estado'] ?? 'N/A');
    
    $yEnd2 = $pdf->GetY();
    $pdf->Rect($ML, $yStart2 - 0.5, $innerWidth, $yEnd2 - $yStart2 + 1);
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetX($ML);
    $pdf->Cell($innerWidth, 6, 'Informacion Academica', 0, 1, 'C');
    $pdf->Ln(0.5);
    
    $yAc = $pdf->GetY();
    $row4('Asignacion:', $estudiante['asignacion'] ?? 'N/A', 'Nivel:', $estudiante['nivel'] ?? 'N/A');
    $row2('Numero:', $estudiante['numero'] ?? 'N/A');
    $row2('Sede Actual:', $estudiante['sede'] ?? 'N/A');
    $yAcEnd = $pdf->GetY();
    $pdf->Rect($ML, $yAc - 0.5, $innerWidth, $yAcEnd - $yAc + 1);
    $pdf->Ln(2);
    
    if (!empty($estudiante['institucion_externa'])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX($ML);
        $pdf->Cell($innerWidth, 6, 'Institucion Externa', 0, 1, 'C');
        $pdf->Ln(0.5);
        $yIE = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetX($ML);
        $pdf->MultiCell($innerWidth, 2.5, $estudiante['institucion_externa'], 0, 'L');
        $yIEEnd = $pdf->GetY();
        $pdf->Rect($ML, $yIE - 0.5, $innerWidth, $yIEEnd - $yIE + 1);
        $pdf->Ln(2);
    }
    
    if (!empty($estudiante['otraInformacion'])) {
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetX($ML);
        $pdf->Cell($innerWidth, 6, 'Otra Informacion', 0, 1, 'C');
        $pdf->Ln(0.5);
        $yOI = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetX($ML);
        $pdf->MultiCell($innerWidth, 2.5, $estudiante['otraInformacion'], 0, 'L');
        $yOIEnd = $pdf->GetY();
        $pdf->Rect($ML, $yOI - 0.5, $innerWidth, $yOIEnd - $yOI + 1);
        $pdf->Ln(2);
    }
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetX($ML);
    $pdf->Cell($innerWidth, 6, 'Padres y Acudientes', 0, 1, 'C');
    $pdf->Ln(0.5);
    
    $yPad = $pdf->GetY();
    
    $pL = 38;
    $pV = 58;
    $pR = 44;
    $pVR = $innerWidth - $pL - $pV - $pR;
    
    $rowPad = function (string $l1, string $v1, string $l2, string $v2) use ($pdf, $ML, $pL, $pV, $pR, $pVR, $rowH): void {
        $pdf->SetX($ML);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell($pL, $rowH, $l1, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($pV, $rowH, $v1, 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell($pR, $rowH, $l2, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell($pVR, $rowH, $v2, 0, 1, 'L');
    };
    
    $rowPad('Padre:', $estudiante['padre'] ?? 'N/A', 'Identificacion Padre:', $estudiante['padreid'] ?? 'N/A');
    $rowPad('Ocupacion:', str_replace('_', ' ', $estudiante['ocupacionpadre'] ?? 'N/A'), 'Telefono Padre:', $estudiante['telefonopadre'] ?? 'N/A');
    $rowPad('Madre:', $estudiante['madre'] ?? 'N/A', 'Identificacion Madre:', $estudiante['madreid'] ?? 'N/A');
    $rowPad('Ocupacion:', str_replace('_', ' ', $estudiante['ocupacionmadre'] ?? 'N/A'), 'Telefono Madre:', $estudiante['telefonomadre'] ?? 'N/A');
    $rowPad('Acudiente:', $estudiante['acudiente'] ?? 'N/A', 'Identificacion Acudiente:', $estudiante['idacudiente'] ?? 'N/A');
    $rowPad('Parentesco Acudiente:', $estudiante['parentesco'] ?? 'N/A', 'Telefono:', $estudiante['telefono_acudiente'] ?? 'N/A');
    
    $yPadEnd = $pdf->GetY();
    $pdf->Rect($ML, $yPad - 0.5, $innerWidth, $yPadEnd - $yPad + 1);
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetX($ML);
    $pdf->Cell($innerWidth, 6, 'Informacion Referencial', 0, 1, 'C');
    $pdf->Ln(0.5);
    
    $yRef = $pdf->GetY();
    $row4('Victima de Conflicto:', $estudiante['victimaConflicto'] ?? 'N/A', 'Desplazado de:', $estudiante['lugarDesplazamiento'] ?? 'N/A');
    $row4('Fecha desplazamiento:', $estudiante['fechaDesplazamiento'] ?? 'N/A', 'H.E.D.:', $estudiante['HED'] ?? 'N/A');
    $row2('Etnia:', $estudiante['etnia'] ?? 'N/A');
    $row2('Discapacidad:', $estudiante['discapacidad'] ?? 'N/A');
    $yRefEnd = $pdf->GetY();
    $pdf->Rect($ML, $yRef - 0.5, $innerWidth, $yRefEnd - $yRef + 1);
    
    $pdf->Ln(5);
    
    $fW = $innerWidth / 3;
    $yFirmas = $pdf->GetY() + 2;
    
    $hEspacioFirma1 = 18;
    $hLineaEtiqueta = 8;
    $hTextoAcudiente = 18;
    $hEspacioFirma2 = 18;
    
    $yLineaEstudiante = $yFirmas + $hEspacioFirma1;
    
    $pdf->SetXY($ML, $yLineaEstudiante);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell($fW, 4, '_______________________', 0, 0, 'C');
    
    $pdf->SetXY($ML + $fW, $yLineaEstudiante);
    $pdf->Cell($fW, 4, '_______________________', 0, 0, 'C');
    
    $qrPath = __DIR__ . '/images/qrcode.jpg';
    if (file_exists($qrPath)) {
        $qrSize = 22;
        $qrX = $ML + 2 * $fW + ($fW - $qrSize) / 2;
        $pdf->Image($qrPath, $qrX, $yFirmas, $qrSize, $qrSize, 'JPG');
    }
    
    $yEtiquetas = $yLineaEstudiante + 5;
    
    $pdf->SetXY($ML, $yEtiquetas);
    $pdf->Cell($fW, 4, 'Estudiante', 0, 0, 'C');
    
    $pdf->SetXY($ML + $fW, $yEtiquetas);
    $pdf->Cell($fW, 4, 'Padre o Acudiente', 0, 0, 'C');
    
    $textoPadre = "Manifiesto que he sido informado como acudiente y me comprometo a\n" .
        "acceder y descargar el Manual de Convivencia vigente a traves\n" .
        "del siguiente enlace en la pagina oficial de la institucion educativa:\n" .
        "https://iedeoccidente.edu.co/documentos/manual_de_convivencia_ieo.pdf\n" .
        "o el QR dado en la parte inferior";
    
    $pdf->SetXY($ML + $fW, $yEtiquetas + 5);
    $pdf->SetFont('helvetica', '', 5.5);
    $pdf->MultiCell($fW, 2.5, $textoPadre, 0, 'C');
    
    $yInicioRector = $yFirmas + $hEspacioFirma1 + $hLineaEtiqueta + $hTextoAcudiente;
    
    $rectorPath = __DIR__ . '/images/rector.jpg';
    if (file_exists($rectorPath)) {
        $rW = 30;
        $rH = 16;
        $rX = $ML + ($fW - $rW) / 2;
        $pdf->Image($rectorPath, $rX, $yInicioRector, $rW, $rH, 'JPG');
    }
    
    $yLineaRector = $yInicioRector + $hEspacioFirma2;
    
    $pdf->SetXY($ML, $yLineaRector);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell($fW, 4, '_______________________', 0, 0, 'C');
    
    $pdf->SetXY($ML, $yLineaRector + 5);
    $pdf->Cell($fW, 4, 'Rector', 0, 0, 'C');
    
    $pdf->SetY($yLineaRector + 5 + 6);
    
    $yFoot = $pdf->GetY();
    
    $pdf->SetX($ML);
    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->Cell($innerWidth, 4, 'TELEFONOS: SECRETARIA 314 661 03 44  e-mail iedeoccidente@sedcaldas.gov.co', 0, 1, 'C');
    $pdf->SetX($ML);
    $pdf->Cell($innerWidth, 4, 'Cr 5 11-19 ANSERMA CALDAS', 0, 1, 'C');
    
    $yFootEnd = $pdf->GetY();
    $pdf->Rect($ML, $yFoot - 0.5, $innerWidth, $yFootEnd - $yFoot + 1);
}

foreach ($estudiantes as $estudiante) {
    generateStudentPage($pdf, $estudiante, $ML, $MR, $MT, $MB, $innerWidth);
}

ob_end_clean();

$filename = 'Estudiantes_Consolidado_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I');
