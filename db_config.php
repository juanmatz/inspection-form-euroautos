<?php
// Buscar archivo .env en el directorio actual o en el superior (para mayor seguridad)
$envFile = '';
if (file_exists(__DIR__ . '/.env')) {
    $envFile = __DIR__ . '/.env';
} elseif (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
}

if (!empty($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorar líneas vacías y comentarios
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        // Dividir llave y valor por el primer signo '='
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Limpiar comillas iniciales y finales si existen
            $val = trim($val, '"\'');
            if (!defined($key)) {
                define($key, $val);
            }
        }
    }
}

// Fallbacks de conexión por defecto si no están definidos en el archivo .env
if (!defined('DB_HOST'))                  define('DB_HOST', 'localhost'); 
if (!defined('DB_NAME'))                  define('DB_NAME', 'u130504112_inspeccion_eur');
if (!defined('DB_USER'))                  define('DB_USER', 'u130504112_euro');
if (!defined('DB_PASS'))                  define('DB_PASS', '@Euro2026*'); 
if (!defined('DB_CHARSET'))               define('DB_CHARSET', 'utf8mb4');
if (!defined('API_KEY'))                  define('API_KEY', '@Euro2026*@_');
if (!defined('CLOUDINARY_CLOUD_NAME'))    define('CLOUDINARY_CLOUD_NAME', 'tu_cloud_name');
if (!defined('CLOUDINARY_API_KEY'))       define('CLOUDINARY_API_KEY', 'tu_api_key');
if (!defined('CLOUDINARY_API_SECRET'))    define('CLOUDINARY_API_SECRET', 'tu_api_secret');
?>
