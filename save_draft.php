<?php
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once 'db_config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data) || empty($data['uid'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos o UID faltante']);
    exit;
}

$uid        = trim($data['uid']);
$piezas     = isset($data['piezas']) ? json_encode($data['piezas'], JSON_UNESCAPED_UNICODE) : '{}';
$obs        = isset($data['observaciones']) ? trim($data['observaciones']) : '';
$timestamp  = date('Y-m-d H:i:s');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);

    if ($conn->connect_error) {
        throw new Exception("Error conexión DB: " . $conn->connect_error);
    }

    // Target: inspecciones_detalle (uid PK, datos_json, observaciones, ultima_actualizacion)
    // UPSERT: crea el registro si no existe, actualiza si ya existe
    $sql = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, ultima_actualizacion)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                datos_json           = VALUES(datos_json),
                observaciones        = VALUES(observaciones),
                ultima_actualizacion = VALUES(ultima_actualizacion)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conn->error);
    }

    $stmt->bind_param("ssss", $uid, $piezas, $obs, $timestamp);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['status' => 'ok', 'saved_at' => $timestamp]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>