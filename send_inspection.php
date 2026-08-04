<?php
header('Content-Type: application/json');

require_once 'db_config.php';

// URL Profesional configurada en n8n
$n8n_url = "https://n8n.srv989344.hstgr.cloud/webhook/v1/inspecciones/generar-pdf";

// Si es FormData, los datos vienen en $_POST['datos']
$datosRaw = isset($_POST['datos']) ? $_POST['datos'] : '';

// Fallback por si envían JSON crudo
if (empty($datosRaw)) {
    $datosRaw = file_get_contents('php://input');
}

$data = json_decode($datosRaw, true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sin datos en la inspección']);
    exit;
}

// ---------------------------------------------------------
// 1. GUARDAR EN BASE DE DATOS LOCAL
// ---------------------------------------------------------
try {
    if (isset($data['uid']) && isset($data['datos_vehiculo'])) {
        $uid = $data['uid'];
        $v = $data['datos_vehiculo'];
        
        $placa = strtoupper(trim($v['placa']));
        $marca = trim($v['marca']);
        $linea = trim($v['linea']);
        $modelo = trim($v['modelo']);
        $color = isset($v['color']) ? trim($v['color']) : '';
        $vin = isset($v['vin']) ? trim($v['vin']) : '';
        $propietario = isset($v['propietario']) ? trim($v['propietario']) : '';
        $cedula = isset($v['cedula_propietario']) ? trim($v['cedula_propietario']) : '';

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset(DB_CHARSET);

        if ($conn->connect_error) {
            throw new Exception("Error conexión DB: " . $conn->connect_error);
        }

        // A. Actualizar/Insertar Vehículo
        $sqlVehiculo = "INSERT INTO vehiculos (placa, marca, linea, modelo, color, vin, propietario, cedula_propietario) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        marca = VALUES(marca), 
                        linea = VALUES(linea), 
                        modelo = VALUES(modelo),
                        color = VALUES(color),
                        vin = VALUES(vin),
                        propietario = VALUES(propietario),
                        cedula_propietario = VALUES(cedula_propietario)";
        
        $stmtV = $conn->prepare($sqlVehiculo);
        $stmtV->bind_param("ssssssss", $placa, $marca, $linea, $modelo, $color, $vin, $propietario, $cedula);
        if (!$stmtV->execute()) {
            throw new Exception("Error guardando vehículo: " . $stmtV->error);
        }
        $stmtV->close();

        // B. Actualizar Inspección (Vincular nueva placa si cambió)
        $sqlInsp = "UPDATE inspecciones SET placa = ? WHERE uid = ?";
        $stmtI = $conn->prepare($sqlInsp);
        $stmtI->bind_param("ss", $placa, $uid);
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

// ---------------------------------------------------------
// 2. SUBIR FOTOS A CLOUDINARY
// ---------------------------------------------------------
// Conservar fotos previamente subidas si existen en el payload o en la base de datos
$urls_fotos = [];
if (isset($data['urls_fotos']) && is_array($data['urls_fotos'])) {
    $urls_fotos = array_values(array_filter($data['urls_fotos']));
} else if (isset($data['piezas']) && is_array($data['piezas']) && isset($data['piezas']['__urls_fotos__']) && is_array($data['piezas']['__urls_fotos__'])) {
    $urls_fotos = array_values(array_filter($data['piezas']['__urls_fotos__']));
}

// Consultar la base de datos local para rescatar fotos anteriores del mismo UID
if (isset($data['uid'])) {
    try {
        $connCheck = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$connCheck->connect_error) {
            $connCheck->set_charset(DB_CHARSET);
            $stmtCheck = $connCheck->prepare("SELECT * FROM inspecciones_detalle WHERE uid = ? LIMIT 1");
            if ($stmtCheck) {
                $stmtCheck->bind_param("s", $data['uid']);
                $stmtCheck->execute();
                $resCheck = $stmtCheck->get_result();
                if ($rowCheck = $resCheck->fetch_assoc()) {
                    $existingDbUrls = [];
                    if (!empty($rowCheck['urls_fotos'])) {
                        if (is_string($rowCheck['urls_fotos'])) {
                            $decoded = json_decode($rowCheck['urls_fotos'], true);
                            if (is_array($decoded)) $existingDbUrls = $decoded;
                        } else if (is_array($rowCheck['urls_fotos'])) {
                            $existingDbUrls = $rowCheck['urls_fotos'];
                        }
                    }
                    if (empty($existingDbUrls) && !empty($rowCheck['datos_json'])) {
                        $pLocal = json_decode($rowCheck['datos_json'], true);
                        if (isset($pLocal['__urls_fotos__']) && is_array($pLocal['__urls_fotos__'])) {
                            $existingDbUrls = $pLocal['__urls_fotos__'];
                        }
                    }
                    $urls_fotos = array_values(array_unique(array_filter(array_merge($urls_fotos, $existingDbUrls))));
                }
                $stmtCheck->close();
            }
            $connCheck->close();
        }
    } catch (Exception $e) {
        // Ignorar error de DB local
    }
}

// Obtener la placa limpia del vehículo para usarla como prefijo de los nombres de archivo
$placa_vehiculo = 'SIN_PLACA';
if (isset($data['datos_vehiculo']['placa'])) {
    $placa_vehiculo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['datos_vehiculo']['placa']));
    if (empty($placa_vehiculo)) {
        $placa_vehiculo = 'SIN_PLACA';
    }
}

if (isset($_FILES['fotos']) && is_array($_FILES['fotos']['tmp_name'])) {
    $num_files = count($_FILES['fotos']['tmp_name']);
    for ($i = 0; $i < $num_files; $i++) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // Saltar archivos con error
        }

        $tmpName = $_FILES['fotos']['tmp_name'][$i];
        $fileName = $_FILES['fotos']['name'][$i];
        $fileSize = $_FILES['fotos']['size'][$i];

        // A. Validar tamaño (máximo 6MB)
        if ($fileSize > 6 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => "La foto '$fileName' excede el tamaño máximo permitido de 6MB"]);
            exit;
        }

        // B. Validar tipo MIME estricto (JPEG, PNG, WEBP)
        $mimeType = mime_content_type($tmpName);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            http_response_code(400);
            echo json_encode(['error' => "El archivo '$fileName' no es una imagen válida (Formatos permitidos: JPG, PNG, WEBP)"]);
            exit;
        }

        // C. Subir a Cloudinary usando REST API firmado (Mitigación RCE)
        $timestamp = time();
        // Generar un public_id único con prefijo de la placa para evitar colisiones
        $publicId = $placa_vehiculo . "_foto_" . ($i + 1) . "_" . bin2hex(random_bytes(3));

        // Parámetros ordenados alfabéticamente para la firma (folder, public_id, timestamp)
        $params_to_sign = "folder=inspecciones&public_id=" . $publicId . "&timestamp=" . $timestamp;
        $signature = sha1($params_to_sign . CLOUDINARY_API_SECRET);

        $cloudinary_url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/image/upload";

        $ch = curl_init($cloudinary_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file' => new CURLFile($tmpName, $mimeType, $fileName),
            'timestamp' => $timestamp,
            'folder' => 'inspecciones',
            'public_id' => $publicId,
            'api_key' => CLOUDINARY_API_KEY,
            'signature' => $signature
        ]);

        $res_cloud = curl_exec($ch);
        $http_code_cloud = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code_cloud >= 200 && $http_code_cloud < 300) {
            $data_cloud = json_decode($res_cloud, true);
            if (isset($data_cloud['secure_url'])) {
                $urls_fotos[] = $data_cloud['secure_url'];
            }
        } else {
            $err_cloud = json_decode($res_cloud, true);
            $err_msg = isset($err_cloud['error']['message']) ? $err_cloud['error']['message'] : 'Error de comunicación con Cloudinary';
            http_response_code($http_code_cloud ?: 500);
            echo json_encode(['error' => "Fallo al subir imagen a Cloudinary: " . $err_msg]);
            exit;
        }
    }
}

// Limpiar y consolidar las URLs de fotos sin duplicados
$urls_fotos = array_values(array_unique(array_filter($urls_fotos)));

// Inyectar las URLs de las fotos en el payload definitivo para n8n
$data['urls_fotos'] = $urls_fotos;

// Guardar/Actualizar las URLs de fotos en la tabla local inspecciones_detalle
try {
    if (isset($data['uid'])) {
        $uid = $data['uid'];
        $piezas = isset($data['piezas']) ? $data['piezas'] : [];
        if (is_array($piezas)) {
            $piezas['__urls_fotos__'] = $urls_fotos;
        }
        $piezasJson = json_encode($piezas, JSON_UNESCAPED_UNICODE);
        $obs = isset($data['observaciones']) ? trim($data['observaciones']) : '';
        $timestamp = date('Y-m-d H:i:s');

        $connDetalle = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$connDetalle->connect_error) {
            $connDetalle->set_charset(DB_CHARSET);
            $urlsFotosJson = json_encode($urls_fotos, JSON_UNESCAPED_UNICODE);
            
            // 1. Intentar guardar en la columna urls_fotos
            $sqlDetalle = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, urls_fotos, ultima_actualizacion)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE
                               datos_json           = VALUES(datos_json),
                               observaciones        = VALUES(observaciones),
                               urls_fotos           = VALUES(urls_fotos),
                               ultima_actualizacion = VALUES(ultima_actualizacion)";
            $stmtD = $connDetalle->prepare($sqlDetalle);
            if ($stmtD) {
                $stmtD->bind_param("sssss", $uid, $piezasJson, $obs, $urlsFotosJson, $timestamp);
                $stmtD->execute();
                $stmtD->close();
            } else {
                // Fallback por si la columna urls_fotos no existe aún en la tabla MySQL
                $sqlFallback = "INSERT INTO inspecciones_detalle (uid, datos_json, observaciones, ultima_actualizacion)
                                VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                    datos_json           = VALUES(datos_json),
                                    observaciones        = VALUES(observaciones),
                                    ultima_actualizacion = VALUES(ultima_actualizacion)";
                $stmtF = $connDetalle->prepare($sqlFallback);
                if ($stmtF) {
                    $stmtF->bind_param("ssss", $uid, $piezasJson, $obs, $timestamp);
                    $stmtF->execute();
                    $stmtF->close();
                }
            }
            $connDetalle->close();
        }
    }
} catch (Exception $e) {
    // Si ocurre algún problema en el guardado secundario local, no se interrumpe el flujo principal hacia n8n
}

// ---------------------------------------------------------
// 3. ENVIAR A N8N
// ---------------------------------------------------------
$n8n_payload = json_encode($data, JSON_UNESCAPED_UNICODE);

$ch = curl_init($n8n_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $n8n_payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-KEY: ' . API_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Retornar la respuesta de n8n o un error genérico si n8n falla
if ($httpCode >= 200 && $httpCode < 300) {
    echo $response;
} else {
    $code = ($httpCode >= 100 && $httpCode <= 599) ? $httpCode : 500;
    http_response_code($code);
    echo $response ?: json_encode(['error' => 'Error al comunicar con n8n']);
}
?>
