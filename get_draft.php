<?php
header('Content-Type: application/json');

require_once 'db_config.php';

$base_n8n_url = "https://n8n.srv989344.hstgr.cloud/webhook/v1/inspecciones/obtener-borrador";
$uid = isset($_GET['id']) ? trim($_GET['id']) : '';

$n8n_data = null;
if (!empty($uid)) {
    $n8n_url = $base_n8n_url . "?id=" . urlencode($uid);
    $ch = curl_init($n8n_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . API_KEY
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($response)) {
        $n8n_data = json_decode($response, true);
    }
}

// Consultar la base de datos MySQL local para extraer piezas, observaciones y urls_fotos
$local_data = null;
$local_urls_fotos = [];

if (!empty($uid)) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn->connect_error) {
            $conn->set_charset(DB_CHARSET);
            // SELECT * para incluir la columna urls_fotos si existe
            $stmt = $conn->prepare("SELECT * FROM inspecciones_detalle WHERE uid = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $uid);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $piezasLocal = json_decode($row['datos_json'], true) ?: [];
                    
                    // 1. Obtener urls_fotos desde la columna urls_fotos de MySQL si existe
                    if (!empty($row['urls_fotos'])) {
                        if (is_string($row['urls_fotos'])) {
                            $decoded = json_decode($row['urls_fotos'], true);
                            if (is_array($decoded)) $local_urls_fotos = $decoded;
                        } else if (is_array($row['urls_fotos'])) {
                            $local_urls_fotos = $row['urls_fotos'];
                        }
                    }
                    
                    // 2. Si estaba vacía, intentar obtener urls_fotos dentro del objeto de piezas
                    if (empty($local_urls_fotos) && !empty($piezasLocal['__urls_fotos__'])) {
                        $local_urls_fotos = $piezasLocal['__urls_fotos__'];
                    }
                    if (empty($local_urls_fotos) && !empty($piezasLocal['urls_fotos'])) {
                        $local_urls_fotos = $piezasLocal['urls_fotos'];
                    }

                    $local_data = [
                        'piezas' => $piezasLocal,
                        'observaciones' => isset($row['observaciones']) ? $row['observaciones'] : '',
                        'urls_fotos' => $local_urls_fotos
                    ];
                }
                $stmt->close();
            }
            $conn->close();
        }
    } catch (Exception $e) {
        // Ignorar error DB local
    }
}

// Consolidar la respuesta para el cliente
$final_response = null;

if ($n8n_data) {
    $item = is_array($n8n_data) && isset($n8n_data[0]) ? $n8n_data[0] : $n8n_data;
    
    // Extraer urls_fotos desde n8n si las contiene
    $n8n_urls = [];
    if (!empty($item['urls_fotos'])) {
        if (is_string($item['urls_fotos'])) {
            $n8n_urls = json_decode($item['urls_fotos'], true) ?: [];
        } else if (is_array($item['urls_fotos'])) {
            $n8n_urls = $item['urls_fotos'];
        }
    } else if (isset($item['piezas']) && is_array($item['piezas']) && !empty($item['piezas']['__urls_fotos__'])) {
        $n8n_urls = $item['piezas']['__urls_fotos__'];
    }

    // Combinar las URLs de n8n y de la DB local sin duplicados
    $merged_raw = array_merge($n8n_urls, $local_urls_fotos);
    $unique = [];
    foreach ($merged_raw as $item) {
        if (is_string($item) && !empty(trim($item))) {
            if (!isset($unique[$item])) {
                $unique[$item] = ['url' => $item, 'nota' => ''];
            }
        } elseif (is_array($item) && !empty($item['url'])) {
            if (!isset($unique[$item['url']])) {
                $unique[$item['url']] = $item;
            }
        }
    }
    $combined_urls = array_values($unique);

    $item['urls_fotos'] = $combined_urls;
    if (isset($item['piezas']) && is_array($item['piezas'])) {
        $item['piezas']['__urls_fotos__'] = $combined_urls;
    }

    $final_response = $item;
} else if ($local_data) {
    $unique = [];
    foreach ($local_urls_fotos as $item) {
        if (is_string($item) && !empty(trim($item))) {
            if (!isset($unique[$item])) {
                $unique[$item] = ['url' => $item, 'nota' => ''];
            }
        } elseif (is_array($item) && !empty($item['url'])) {
            if (!isset($unique[$item['url']])) {
                $unique[$item['url']] = $item;
            }
        }
    }
    $local_data['urls_fotos'] = array_values($unique);
    $final_response = $local_data;
}

if ($final_response) {
    echo json_encode($final_response, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Borrador no encontrado']);
}
?>
