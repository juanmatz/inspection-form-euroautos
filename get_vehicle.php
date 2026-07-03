<?php
// Desactivar salida de errores HTML para no romper el JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once 'db_config.php';

try {
    // 1. Crear conexión
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);

    // 2. Obtener y validar el ID de la URL
    $uid = isset($_GET['id']) ? trim($_GET['id']) : '';

    if (empty($uid)) {
        throw new Exception('No se proporcionó un ID de cotización válido', 400);
    }

    // 3. Consulta segura (Relacional 3FN)
    $sql = "SELECT v.* FROM vehiculos v 
            JOIN inspecciones i ON v.placa = i.placa 
            WHERE i.uid = ? LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // IMPORTANTE: Como ahora son columnas individuales en la DB, 
        // las devolvemos directamente como un objeto.
        echo json_encode(['datos_vehiculo' => json_encode($row)]);
    } else {
        throw new Exception('Vehículo no vinculado a esta inspección aún', 404);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Capturar cualquier error (conexión, SQL, lógica) y devolver JSON limpio
    $code = $e->getCode() ?: 500;
    // Si el código es numérico y válido HTTP, usarlo. Si no, 500.
    if (!is_int($code) || $code < 100 || $code > 599) $code = 500;
    
    http_response_code($code);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>