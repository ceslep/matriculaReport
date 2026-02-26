<?php
/**
 * Configuración de conexión a MySQL
 * Copia este archivo a tu hosting PHP y configura las variables de entorno
 * o edita los valores directamente aquí
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración - EDITAR ESTOS VALORES para tu hosting
$config = [
    'host'     => getenv('MYSQL_HOST') ?: 'localhost',
    'port'     => getenv('MYSQL_PORT') ?: 3306,
    'user'     => getenv('MYSQL_USER') ?: 'root',
    'password' => getenv('MYSQL_PASSWORD') ?: '',
    'database' => getenv('MYSQL_DB') ?: 'matricula',
];

/**
 * Obtiene una conexión a la base de datos
 * @return PDO|null
 */
function getDbConnection(): ?PDO {
    global $config;
    
    try {
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4",
            $config['host'],
            $config['port'],
            $config['database']
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, $config['user'], $config['password'], $options);
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        return null;
    }
}

/**
 * Devuelve error JSON y termina
 */
function jsonError(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}
