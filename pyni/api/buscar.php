<?php
/**
 * API: Buscar estudiantes
 * Endpoint: GET /api/buscar.php?criterio=...
 * 
 * Formatos de búsqueda:
 * - código completo: "12345"
 * - estudiante (identificación): "12345678"
 * - nombres: "Juan Perez"
 * - asignacion-nivel-numero: "1-11-01"
 * - nivel-numero: "11-01"
 */

require_once __DIR__ . '/config.php';

// Configurar CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Método no permitido', 405);
}

$criterio = $_GET['criterio'] ?? '';

if (empty($criterio)) {
    jsonError('Debe proporcionar un criterio de búsqueda (código, estudiante o nombres)', 400);
}

$db = getDbConnection();
if (!$db) {
    jsonError('Error de conexión a la base de datos', 500);
}

try {
    $partes = explode('-', $criterio);
    $currentYear = date('Y');
    
    // Base SQL con JOIN para obtener última inscripción por estudiante
    $baseSql = "
        SELECT eg.*, s.sede
        FROM estugrupos eg
        INNER JOIN (
            SELECT codigo, MAX(year) AS max_year
            FROM estugrupos
            GROUP BY codigo
        ) AS ultimos ON eg.codigo = ultimos.codigo AND eg.year = ultimos.max_year
        LEFT JOIN sedes s ON eg.asignacion = s.ind
        WHERE eg.year = ?
    ";
    
    $params = [$currentYear];
    
    if (count($partes) === 3) {
        // Formato: asignacion-nivel-numero
        [$asignacion, $nivel, $numero] = $partes;
        $sql = $baseSql . " AND eg.asignacion = ? AND eg.nivel = ? AND eg.numero = ? ORDER BY eg.nombres";
        $params = array_merge($params, [$asignacion, $nivel, $numero]);
        
    } elseif (count($partes) === 2) {
        // Formato: nivel-numero
        [$nivel, $numero] = $partes;
        $sql = $baseSql . " AND eg.nivel = ? AND eg.numero = ? ORDER BY eg.nombres";
        $params = array_merge($params, [$nivel, $numero]);
        
    } else {
        // Búsqueda por código, identificación o nombres
        $sql = $baseSql . " AND (eg.codigo LIKE ? OR eg.estudiante LIKE ? OR eg.nombres LIKE ?) ORDER BY eg.nombres";
        $likeCriterio = "%{$criterio}%";
        $params = array_merge($params, [$likeCriterio, $likeCriterio, $likeCriterio]);
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $estudiantes = $stmt->fetchAll();
    
    if (empty($estudiantes)) {
        jsonError('Estudiante no encontrado', 404);
    }
    
    echo json_encode($estudiantes);
    
} catch (PDOException $e) {
    error_log("Error en búsqueda: " . $e->getMessage());
    jsonError('Error al buscar estudiantes', 500);
}
