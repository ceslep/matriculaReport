<?php
/**
 * Script de diagnóstico para TCPDF
 */

// Aumentar memoria
ini_set('memory_limit', '1024M');
error_reporting(E_ALL);

// Iniciar buffer para evitar "headers already sent"
ob_start();

// Cargar TCPDF
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    ob_end_clean();
    die("ERROR: No se encontró vendor/autoload.php");
}

require_once $autoloadPath;

try {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Test');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Hola Mundo - PDF de prueba', 0, 1, 'C');
    
    // Limpiar buffer antes de enviar PDF
    ob_end_clean();
    $pdf->Output('test.pdf', 'I');
    
} catch (Exception $e) {
    ob_end_clean();
    die("ERROR: " . $e->getMessage());
}
