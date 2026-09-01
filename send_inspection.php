<?php
header('Content-Type: application/json');

require_once 'db_config.php';

// URL del webhook de n8n (solo orquestación — ya NO genera PDF)
$n8n_url = "https://n8n.srv989344.hstgr.cloud/webhook/28542699-8d18-4623-a095-acd19465ae78";

// ─────────────────────────────────────────────────────────────────
// 0. PARSEAR EL PAYLOAD DE ENTRADA
// ─────────────────────────────────────────────────────────────────
$datosRaw = isset($_POST['datos']) ? $_POST['datos'] : '';
if (empty($datosRaw)) {
    $datosRaw = file_get_contents('php://input');
}
$data = json_decode($datosRaw, true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sin datos en la inspección']);
    exit;
}

// ─────────────────────────────────────────────────────────────────
// 1. GUARDAR EN BASE DE DATOS LOCAL
// ─────────────────────────────────────────────────────────────────
try {
    if (isset($data['uid']) && isset($data['datos_vehiculo'])) {
        $uid = $data['uid'];
        $v = $data['datos_vehiculo'];

        $placa       = strtoupper(trim($v['placa'] ?? ''));
        $marca       = trim($v['marca'] ?? '');
        $linea       = trim($v['linea'] ?? '');
        $modelo      = trim($v['modelo'] ?? '');
        $color       = trim($v['color'] ?? '');
        $vin         = trim($v['vin'] ?? '');
        $propietario = trim($v['propietario'] ?? '');
        $cedula      = trim($v['cedula_propietario'] ?? '');

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset(DB_CHARSET);

        if ($conn->connect_error) {
            throw new Exception("Error conexión DB: " . $conn->connect_error);
        }

        // A. Actualizar/Insertar Vehículo
        $sqlVehiculo = "INSERT INTO vehiculos (placa, marca, linea, modelo, color, vin, propietario, cedula_propietario)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        marca = VALUES(marca), linea = VALUES(linea), modelo = VALUES(modelo),
                        color = VALUES(color), vin = VALUES(vin),
                        propietario = VALUES(propietario), cedula_propietario = VALUES(cedula_propietario)";
        $stmtV = $conn->prepare($sqlVehiculo);
        $stmtV->bind_param("ssssssss", $placa, $marca, $linea, $modelo, $color, $vin, $propietario, $cedula);
        if (!$stmtV->execute()) {
            throw new Exception("Error guardando vehículo: " . $stmtV->error);
        }
        $stmtV->close();

        $kmStr = trim($data['kilometraje'] ?? '');
        $kmVal = ($kmStr !== '') ? intval($kmStr) : null;
        $ubStr = trim($data['ubicacion'] ?? '');
        $ubVal = ($ubStr !== '') ? $ubStr : null;
        $asegStr = trim($data['aseguradora'] ?? '');
        $asegVal = ($asegStr !== '') ? $asegStr : null;
        $sevStr = trim($data['severidad'] ?? '');
        $sevVal = ($sevStr !== '') ? $sevStr : null;

        // B. Vincular placa, kilometraje, ubicacion, aseguradora y severidad a la inspección
        $sqlInsp = "UPDATE inspecciones SET placa = ?, kilometraje = ?, ubicacion = ?, aseguradora = ?, severidad = ? WHERE uid = ?";
        $stmtI = $conn->prepare($sqlInsp);
        $stmtI->bind_param("sissss", $placa, $kmVal, $ubVal, $asegVal, $sevVal, $uid);
        if (!$stmtI->execute()) {
            throw new Exception("Error actualizando inspección: " . $stmtI->error);
        }
        $stmtI->close();
        $conn->close();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error guardando en DB: ' . $e->getMessage()]);
    exit;
}

// ─────────────────────────────────────────────────────────────────
// 2. SUBIR FOTOS A CLOUDINARY
// ─────────────────────────────────────────────────────────────────
$urls_fotos = [];
if (isset($data['urls_fotos']) && is_array($data['urls_fotos'])) {
    $urls_fotos = array_values(array_filter($data['urls_fotos']));
}

// Asegurar que $urls_fotos sea un arreglo de objetos {url, nota}
foreach ($urls_fotos as &$f) {
    if (is_string($f)) $f = ['url' => $f, 'nota' => ''];
}
unset($f);

// Rescatar fotos anteriores del mismo UID desde la BD local
if (isset($data['uid'])) {
    try {
        $connCheck = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$connCheck->connect_error) {
            $connCheck->set_charset(DB_CHARSET);
            $stmtCheck = $connCheck->prepare("SELECT urls_fotos, datos_json FROM inspecciones_detalle WHERE uid = ? LIMIT 1");
            if ($stmtCheck) {
                $stmtCheck->bind_param("s", $data['uid']);
                $stmtCheck->execute();
                $resCheck = $stmtCheck->get_result();
                if ($rowCheck = $resCheck->fetch_assoc()) {
                    $existingDbUrls = [];
                    if (!empty($rowCheck['urls_fotos'])) {
                        $decoded = json_decode($rowCheck['urls_fotos'], true);
                        if (is_array($decoded)) $existingDbUrls = $decoded;
                    }
                    if (empty($existingDbUrls) && !empty($rowCheck['datos_json'])) {
                        $pLocal = json_decode($rowCheck['datos_json'], true);
                        if (isset($pLocal['__urls_fotos__']) && is_array($pLocal['__urls_fotos__'])) {
                            $existingDbUrls = $pLocal['__urls_fotos__'];
                        }
                    }
                    
                    // Asegurar formato {url, nota} para existingDbUrls
                    foreach ($existingDbUrls as &$f) {
                        if (is_string($f)) $f = ['url' => $f, 'nota' => ''];
                    }
                    unset($f);
                    
                    // Combinar ambos arrays evitando duplicados por URL
                    $merged = array_merge($urls_fotos, $existingDbUrls);
                    $unique = [];
                    foreach ($merged as $item) {
                        if (!isset($item['url']) || empty($item['url'])) continue;
                        if (!isset($unique[$item['url']])) {
                            $unique[$item['url']] = $item;
                        }
                    }
                    $urls_fotos = array_values($unique);
                }
                $stmtCheck->close();
            }
            $connCheck->close();
        }
    } catch (Exception $e) { /* Ignorar */ }
}

// Prefijo de placa para nombres de archivo Cloudinary
$placa_vehiculo = 'SIN_PLACA';
if (!empty($data['datos_vehiculo']['placa'])) {
    $placa_vehiculo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['datos_vehiculo']['placa']));
    if (empty($placa_vehiculo)) $placa_vehiculo = 'SIN_PLACA';
}

// Subir nuevas fotos
if (isset($_FILES['fotos']) && is_array($_FILES['fotos']['tmp_name'])) {
    $num_files = count($_FILES['fotos']['tmp_name']);
    for ($i = 0; $i < $num_files; $i++) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $tmpName  = $_FILES['fotos']['tmp_name'][$i];
        $fileName = $_FILES['fotos']['name'][$i];
        $fileSize = $_FILES['fotos']['size'][$i];

        if ($fileSize > 6 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => "La foto '$fileName' excede los 6MB"]);
            exit;
        }

        $mimeType     = mime_content_type($tmpName);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            http_response_code(400);
            echo json_encode(['error' => "El archivo '$fileName' no es una imagen válida (JPG, PNG o WEBP)"]);
            exit;
        }

        $timestamp = time();
        $publicId  = $placa_vehiculo . "_foto_" . ($i + 1) . "_" . bin2hex(random_bytes(3));
        $params_to_sign = "folder=inspecciones&public_id=" . $publicId . "&timestamp=" . $timestamp;
        $signature = sha1($params_to_sign . CLOUDINARY_API_SECRET);

        $ch = curl_init("https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file'      => new CURLFile($tmpName, $mimeType, $fileName),
            'timestamp' => $timestamp,
            'folder'    => 'inspecciones',
            'public_id' => $publicId,
            'api_key'   => CLOUDINARY_API_KEY,
            'signature' => $signature,
        ]);
        $res_cloud       = curl_exec($ch);
        $http_code_cloud = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code_cloud >= 200 && $http_code_cloud < 300) {
            $data_cloud = json_decode($res_cloud, true);
            if (isset($data_cloud['secure_url'])) {
                $nota = isset($_POST['notas_fotos_nuevas'][$i]) ? trim($_POST['notas_fotos_nuevas'][$i]) : '';
                $urls_fotos[] = [
                    'url' => $data_cloud['secure_url'],
                    'nota' => $nota
                ];
            }
        } else {
            $err_cloud = json_decode($res_cloud, true);
            $err_msg   = $err_cloud['error']['message'] ?? 'Error de comunicación con Cloudinary';
            http_response_code($http_code_cloud ?: 500);
            echo json_encode(['error' => "Fallo al subir imagen: " . $err_msg]);
            exit;
        }
    }
}

// Evitar duplicados por URL al final
$unique = [];
foreach ($urls_fotos as $item) {
    if (!isset($item['url']) || empty($item['url'])) continue;
    if (!isset($unique[$item['url']])) {
        $unique[$item['url']] = $item;
    }
}
$urls_fotos = array_values($unique);
$data['urls_fotos'] = $urls_fotos;

// ─────────────────────────────────────────────────────────────────
// 3. GUARDAR/ACTUALIZAR BORRADOR EN inspecciones_detalle
// ─────────────────────────────────────────────────────────────────
try {
    if (isset($data['uid'])) {
        $uid      = $data['uid'];
        $piezas   = is_array($data['piezas'] ?? null) ? $data['piezas'] : [];
        $obs      = trim($data['observaciones'] ?? '');
        $tsNow    = date('Y-m-d H:i:s');
        $piezasJson    = json_encode($piezas, JSON_UNESCAPED_UNICODE);
        $urlsFotosJson = json_encode($urls_fotos, JSON_UNESCAPED_UNICODE);

        $connD = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$connD->connect_error) {
            $connD->set_charset(DB_CHARSET);
            $sqlD = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, urls_fotos, ultima_actualizacion)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                         datos_json           = VALUES(datos_json),
                         observaciones        = VALUES(observaciones),
                         urls_fotos           = VALUES(urls_fotos),
                         ultima_actualizacion = VALUES(ultima_actualizacion)";
            $stmtD = $connD->prepare($sqlD);
            if ($stmtD) {
                $stmtD->bind_param("sssss", $uid, $piezasJson, $obs, $urlsFotosJson, $tsNow);
                $stmtD->execute();
                $stmtD->close();
            }
            $connD->close();
        }
    }
} catch (Exception $e) { /* No interrumpir el flujo */ }

// ─────────────────────────────────────────────────────────────────
// 4. GENERAR HTML DEL REPORTE (DETERMINISTA — SIN IA)
// ─────────────────────────────────────────────────────────────────
$vehiculo      = $data['datos_vehiculo'] ?? [];
$piezasRaw     = $data['piezas'] ?? [];
$observaciones = trim($data['observaciones'] ?? '');
$aseguradora   = trim($data['aseguradora'] ?? '');
$severidad     = trim($data['severidad'] ?? '');
$kilometraje   = trim($data['kilometraje'] ?? '');
$ubicacion     = trim($data['ubicacion'] ?? '');
$fechaHoy      = date('d/m/Y');

// Mapa de posiciones
$mapPos = [
    'DEL' => 'Delantero', 'TRA' => 'Trasero',
    'IZQ' => 'Izquierdo', 'DER' => 'Derecho',
    'CEN' => 'Central',   'SUP' => 'Superior', 'INF' => 'Inferior',
];

function traducirPosicion(string $comboKey, array $mapPos): string {
    if (trim($comboKey) === '') return '—';
    $parts = explode('+', $comboKey);
    return implode(' ', array_map(fn($k) => $mapPos[trim($k)] ?? $k, $parts));
}

// Mapa de badges
$mapBadges = [
    'LEVE'               => ['class' => 'b-LEVE',    'label' => 'REPARACION LEVE'],
    'MEDIA'              => ['class' => 'b-MEDIA',   'label' => 'REPARACION MEDIA'],
    'FUERTE'             => ['class' => 'b-FUERTE',  'label' => 'REPARACION FUERTE'],
    'CAMBIO'             => ['class' => 'b-CAMBIO',  'label' => 'CAMBIO'],
    'PINTURA'            => ['class' => 'b-PINTURA', 'label' => 'PINTURA'],
    'DESMONTAJE_MONTAJE' => ['class' => 'b-DM',      'label' => 'D/M'],
    'REVISION'           => ['class' => 'b-REV',     'label' => 'REVISIÓN'],
];

// — Preprocesamiento: agrupar por categoría + nombre base (ignorar sufijo #N)
$categorias   = [];
$piezasUnicas = [];

foreach ($piezasRaw as $key => $val) {
    if (strpos($key, '::') === false) continue;
    [$cat, $fullNombre] = explode('::', $key, 2);
    $nombreBase = preg_replace('/#\d+$/', '', $fullNombre);

    $piezasUnicas[$cat . '::' . $nombreBase] = true;
    if (!isset($categorias[$cat])) $categorias[$cat] = [];
    if (!isset($categorias[$cat][$nombreBase])) {
        $categorias[$cat][$nombreBase] = ['lados' => [], 'notas' => $val['notas'] ?? ''];
    }

    // Fusionar lados
    foreach (($val['lados'] ?? []) as $comboKey => $acciones) {
        if (!isset($categorias[$cat][$nombreBase]['lados'][$comboKey])) {
            $categorias[$cat][$nombreBase]['lados'][$comboKey] = [];
        }
        if (empty($acciones)) {
            $categorias[$cat][$nombreBase]['lados'][$comboKey][] = [
                'accion' => 'REVISIÓN',
                'nota'   => $val['notas'] ?? ''
            ];
        } else {
            foreach ($acciones as $accion) {
                $categorias[$cat][$nombreBase]['lados'][$comboKey][] = $accion;
            }
        }
    }
}

$totalPiezas = count($piezasUnicas);

// — Generar filas <tr>
$filasHtml = '';
foreach ($categorias as $catName => $piezasObj) {
    $catNameEsc = htmlspecialchars($catName, ENT_QUOTES);
    $filasHtml .= "<tr class=\"cat-header\"><td colspan=\"4\">{$catNameEsc}</td></tr>\n";

    foreach ($piezasObj as $pieceName => $pieceData) {
        $pieceNameEsc = htmlspecialchars($pieceName, ENT_QUOTES);
        foreach ($pieceData['lados'] as $comboKey => $acciones) {
            $posTraducida = traducirPosicion($comboKey, $mapPos);
            $posEsc       = htmlspecialchars($posTraducida, ENT_QUOTES);

            foreach ($acciones as $itemAccion) {
                $accionKey  = $itemAccion['accion'] ?? '';
                $badge      = $mapBadges[$accionKey] ?? ['class' => 'b-REV', 'label' => htmlspecialchars($accionKey, ENT_QUOTES)];
                $nota       = trim($itemAccion['nota'] ?? $pieceData['notas'] ?? '');
                $notaHtml   = $nota ? htmlspecialchars($nota, ENT_QUOTES) : '';

                $filasHtml .= "
        <tr class=\"data-row\">
          <td class=\"piece-name\">{$pieceNameEsc}</td>
          <td class=\"pos-cell\">{$posEsc}</td>
          <td><span class=\"badge {$badge['class']}\">{$badge['label']}</span></td>
          <td class=\"nota-cell\">{$notaHtml}</td>
        </tr>";
            }
        }
    }
}

// — Observaciones
$obsHtml = '';
if ($observaciones !== '') {
    $lineas = preg_split('/[\n;]+/', $observaciones);
    foreach ($lineas as $l) {
        $l = trim($l);
        if ($l !== '') $obsHtml .= '<li>' . htmlspecialchars($l, ENT_QUOTES) . '</li>' . "\n";
    }
}
if ($obsHtml === '') $obsHtml = '<li>Sin observaciones adicionales</li>';

// ─────────────────────────────────────────────────────────────────
// 4b. VARIABLES Y GENERACIÓN DE HTML USANDO LA PLANTILLA EXTERNA
// ─────────────────────────────────────────────────────────────────
$placa          = htmlspecialchars($vehiculo['placa']              ?? 'N/A', ENT_QUOTES);
$marcaLinea     = htmlspecialchars(trim(($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['linea'] ?? '')), ENT_QUOTES);
$modelo         = htmlspecialchars($vehiculo['modelo']             ?? 'N/A', ENT_QUOTES);
$color          = htmlspecialchars($vehiculo['color']              ?? 'N/A', ENT_QUOTES);
$vin            = htmlspecialchars($vehiculo['vin']                ?? 'N/A', ENT_QUOTES);
$propietario    = htmlspecialchars($vehiculo['propietario']        ?? 'N/A', ENT_QUOTES);
$cedula         = htmlspecialchars($vehiculo['cedula_propietario'] ?? 'N/A', ENT_QUOTES);
$aseguradoraEsc = htmlspecialchars($aseguradora ?: 'N/A',           ENT_QUOTES);
$severidadEsc   = htmlspecialchars($severidad ?: 'N/A',             ENT_QUOTES);
$kilometrajeEsc = htmlspecialchars($kilometraje ?: 'N/A',           ENT_QUOTES);
$ubicacionEsc   = htmlspecialchars($ubicacion ?: 'N/A',             ENT_QUOTES);

require_once __DIR__ . '/pdf_template.php';

$htmlReporte = generarHtmlReporte([
    'placa'          => $placa,
    'fechaHoy'       => $fechaHoy,
    'propietario'    => $propietario,
    'cedula'         => $cedula,
    'marcaLinea'     => $marcaLinea,
    'modelo'         => $modelo,
    'color'          => $color,
    'vin'            => $vin,
    'aseguradoraEsc' => $aseguradoraEsc,
    'severidadEsc'   => $severidadEsc,
    'kilometrajeEsc' => $kilometrajeEsc,
    'ubicacionEsc'   => $ubicacionEsc,
    'totalPiezas'    => $totalPiezas,
    'filasHtml'      => $filasHtml,
    'obsHtml'        => $obsHtml,
]);


// ─────────────────────────────────────────────────────────────────
// 5. GENERAR PDF VÍA BROWSERLESS
// ─────────────────────────────────────────────────────────────────
if (empty(BROWSERLESS_URL) || empty(BROWSERLESS_TOKEN)) {
    http_response_code(500);
    echo json_encode(['error' => 'Browserless no configurado. Añade BROWSERLESS_URL y BROWSERLESS_TOKEN al .env']);
    exit;
}

$browserlessPayload = json_encode([
    'html'    => $htmlReporte,
    'options' => [
        'printBackground' => true,
        'format'          => 'A4',
        'margin'          => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0'],
    ],
]);

$ch = curl_init(BROWSERLESS_URL . '/pdf?token=' . BROWSERLESS_TOKEN);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $browserlessPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cache-Control: no-cache',
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$pdfBinario      = curl_exec($ch);
$http_code_bl    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_bl   = curl_error($ch);
curl_close($ch);

if ($curl_error_bl || $http_code_bl < 200 || $http_code_bl >= 300 || empty($pdfBinario)) {
    $errDetail = $curl_error_bl ?: "HTTP $http_code_bl";
    http_response_code(500);
    echo json_encode(['error' => "Fallo al generar PDF con Browserless: $errDetail"]);
    exit;
}

// ─────────────────────────────────────────────────────────────────
// 6. GUARDAR PDF EN EL SERVIDOR LOCAL
// ─────────────────────────────────────────────────────────────────
$pdfDir = __DIR__ . '/reportes_pdf';
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0755, true);
}

$pdfPublicId = 'reporte_' . $placa_vehiculo . '_' . date('Ymd_His');
$pdfFileName = $pdfPublicId . '.pdf';
$pdfFilePath = $pdfDir . '/' . $pdfFileName;

// Guardar el PDF binario
file_put_contents($pdfFilePath, $pdfBinario);

// Construir URL pública para que n8n pueda descargarlo
// Se asume que el dominio base es equiturismo.co y el proyecto está en la misma ruta
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
// Extraer la ruta base del script actual (ej. /euroautos/formulario-inspeccion-tecnica)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$pdf_url = $protocol . $domainName . $basePath . '/reportes_pdf/' . $pdfFileName;

// ─────────────────────────────────────────────────────────────────
// 7. ENVIAR A N8N (solo orquestación)
// ─────────────────────────────────────────────────────────────────
$n8nPayload = json_encode([
    'uid'           => $data['uid'] ?? '',
    'pdf_url'       => $pdf_url,
    'urls_fotos'    => $urls_fotos,
    'aseguradora'   => $aseguradora,
    'severidad'     => $severidad,
    'kilometraje'   => $kilometraje,
    'ubicacion'     => $ubicacion,
    'datos_vehiculo'=> $data['datos_vehiculo'] ?? [],
    'observaciones' => $data['observaciones'] ?? '',
    'total_piezas'  => $totalPiezas,
], JSON_UNESCAPED_UNICODE);

$ch = curl_init($n8n_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $n8nPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-KEY: ' . API_KEY,
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$responseN8n = curl_exec($ch);
$httpCodeN8n = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCodeN8n >= 200 && $httpCodeN8n < 300) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'pdf_url' => $pdf_url]);
} else {
    // Si n8n devolvió "No item to return was found", significa que el webhook sí se recibió y activó la ejecución
    if ($responseN8n && strpos($responseN8n, 'No item to return was found') !== false) {
        http_response_code(200);
        echo json_encode(['ok' => true, 'pdf_url' => $pdf_url, 'notice' => 'Webhook n8n recibido']);
    } else {
        $code = ($httpCodeN8n >= 100 && $httpCodeN8n <= 599) ? $httpCodeN8n : 500;
        http_response_code($code);
        echo $responseN8n ?: json_encode(['error' => 'Error al comunicar con n8n']);
    }
}
?>
