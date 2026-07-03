<?php
header('Content-Type: application/json');

require_once 'db_config.php';

$base_n8n_url = "https://n8n.srv989344.hstgr.cloud/webhook/v1/inspecciones/obtener-borrador";
$uid = isset($_GET['id']) ? $_GET['id'] : '';
$n8n_url = $base_n8n_url . "?id=" . urlencode($uid);

$ch = curl_init($n8n_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: ' . API_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Fallo de conexión con el servidor de borradores']);
} else if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode ?: 500);
    echo $response ?: json_encode(['error' => 'Error al obtener el borrador de n8n']);
} else {
    echo $response;
}
?>
