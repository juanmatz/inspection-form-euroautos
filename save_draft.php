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
$piezasInput = isset($data['piezas']) && is_array($data['piezas']) ? $data['piezas'] : [];
$obs        = isset($data['observaciones']) ? trim($data['observaciones']) : '';
$timestamp  = date('Y-m-d H:i:s');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);

    if ($conn->connect_error) {
        throw new Exception("Error conexión DB: " . $conn->connect_error);
    }

    // 1. Rescatar las urls_fotos existentes en la DB para no borrarlas al auto-guardar
    $existingPhotos = [];
    $stmtCheck = $conn->prepare("SELECT datos_json, urls_fotos FROM inspecciones_detalle WHERE uid = ? LIMIT 1");
    if ($stmtCheck) {
        $stmtCheck->bind_param("s", $uid);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($rowCheck = $resCheck->fetch_assoc()) {
            if (!empty($rowCheck['urls_fotos'])) {
                $decoded = is_string($rowCheck['urls_fotos']) ? json_decode($rowCheck['urls_fotos'], true) : $rowCheck['urls_fotos'];
                if (is_array($decoded)) $existingPhotos = $decoded;
            }
            if (empty($existingPhotos) && !empty($rowCheck['datos_json'])) {
                $pDb = json_decode($rowCheck['datos_json'], true);
                if (isset($pDb['__urls_fotos__']) && is_array($pDb['__urls_fotos__'])) {
                    $existingPhotos = $pDb['__urls_fotos__'];
                }
            }
        }
        $stmtCheck->close();
    }

    // Preservar las fotos recibidas o existentes
    $incomingPhotos = isset($piezasInput['__urls_fotos__']) && is_array($piezasInput['__urls_fotos__']) ? $piezasInput['__urls_fotos__'] : [];
    $mergedPhotos = array_values(array_unique(array_filter(array_merge($incomingPhotos, $existingPhotos))));

    if (!empty($mergedPhotos)) {
        $piezasInput['__urls_fotos__'] = $mergedPhotos;
    }

    $piezasJson = json_encode($piezasInput, JSON_UNESCAPED_UNICODE);
    $urlsFotosJson = json_encode($mergedPhotos, JSON_UNESCAPED_UNICODE);

    // 2. Guardar en inspecciones_detalle actualizando tanto datos_json como urls_fotos
    $sql = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, urls_fotos, ultima_actualizacion)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                datos_json           = VALUES(datos_json),
                observaciones        = VALUES(observaciones),
                urls_fotos           = VALUES(urls_fotos),
                ultima_actualizacion = VALUES(ultima_actualizacion)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssss", $uid, $piezasJson, $obs, $urlsFotosJson, $timestamp);
        $stmt->execute();
        $stmt->close();
    } else {
        // Fallback por si la columna urls_fotos no existe en la tabla
        $sqlFallback = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, ultima_actualizacion)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            datos_json           = VALUES(datos_json),
                            observaciones        = VALUES(observaciones),
                            ultima_actualizacion = VALUES(ultima_actualizacion)";
        $stmtF = $conn->prepare($sqlFallback);
        if ($stmtF) {
            $stmtF->bind_param("ssss", $uid, $piezasJson, $obs, $timestamp);
            $stmtF->execute();
            $stmtF->close();
        }
    }
    $conn->close();

    echo json_encode(['status' => 'ok', 'saved_at' => $timestamp]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>