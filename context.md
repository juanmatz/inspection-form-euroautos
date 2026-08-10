# Contexto del Sistema — Formulario de Inspección Técnica (Euro Autos)

## 📌 Descripción del Proyecto
El **Formulario de Inspección Técnica de Euro Autos** es un sistema web progresivo y automatizado diseñado para la toma de inventario y registro técnico de daños en la carrocería de vehículos en Medellín, Colombia. Permite a los operarios realizar inspecciones en tiempo real desde dispositivos móviles o de escritorio, registrar piezas afectadas, capturar fotografías en vivo, guardar borradores de forma persistente y generar reportes técnicos oficiales en PDF con distribución directa a Telegram y correo electrónico mediante automatización en n8n.

---

## 🛠 Arquitectura General y Tecnologías
- **Frontend**: Single Page Application (SPA) responsiva construida con HTML5, Vanilla CSS3 y Javascript (ES6+).
- **Backend**: PHP 8+ con API REST, consultas preparadas (MySQLi Prepared Statements) y cURL para integración remota.
- **Base de Datos**: MySQL (`vehiculos`, `inspecciones`, `inspecciones_detalle`, `usuarios`).
- **Almacenamiento Multimedia**: Cloudinary API (subida segura vía PHP sin almacenamiento permanente local).
- **Generación de PDF**: Browserless (imagen Docker `browserless/chrome`) — Chromium headless expuesto vía API HTTP, autenticado con token, accesible a través de Caddy. PHP llama directamente al endpoint `/pdf` vía cURL enviando el HTML generado determinísticamente.
- **Automatización e Integraciones (n8n Workflow)**:
  - Solo orquesta: recibe webhook, actualiza MySQL, distribuye PDF por Telegram y Gmail.
  - **Mensajería**: Bot interactivo de Telegram (inicio de sesión con `/cotizar`, lectura de matrícula con GPT-4o Vision).
  - **Email**: Integración Gmail OAuth2 para envío de informes a aseguradoras.

---

## 🚀 Funcionalidades Principales

### 1. Captura y Registro de Inspección (Frontend SPA)
- **Consulta de Vehículo**: Búsqueda por placa y autocompletado automático de datos técnicos y del propietario (`get_vehicle.php`).
- **Registro Detallado de Carrocería**:
  - Clasificación de piezas por categorías (Cabina, Parte Delantera, Trasera, Lados, etc.).
  - Selección de posiciones combinadas (ej. `DEL+IZQ`, `TRA+DER+SUP`, `CEN`) con diagrama visual SVG interactivo.
  - Asignación de acciones y niveles de daño mediante badges interactivos: `LEVE`, `MEDIA`, `FUERTE`, `CAMBIO`, `PINTURA`, `D/M` (Desmontaje/Montaje) y `REVISIÓN`.
  - Registro de observaciones por pieza y observaciones generales del operario.
- **Captura Fotográfica en Vivo**:
  - Botón de cámara en la barra superior (`capture="environment"` para uso en móviles).
  - Previsualización compacta de imágenes capturadas con opción de eliminación previa al envío.
  - Envío mediante `FormData` con subida a Cloudinary desde el backend.
- **Selección de Aseguradora** (widget obligatorio):
  - Aparece al presionar cualquier botón de envío (`#btn-send`, `#fab-send`, `#btn-send-drawer`).
  - Bloquea el envío hasta seleccionar entre: *Allianz*, *HDI*, *Mapfre*, *Personal*.
  - El valor seleccionado se incluye en el payload enviado al backend y a n8n.

### 2. Gestión de Borradores y Backend (PHP & MySQL)
- **Guardado Continuo de Borradores**: Registro en tiempo real del progreso ligado a un `UID` único (`save_draft.php`).
- **Recuperación de Estado**: Reanudación de inspecciones en curso (`get_draft.php` y webhook GET `/v1/inspecciones/obtener-borrador`).
- **Generación de PDF Determinista** (`send_inspection.php`):
  1. Valida y persiste los datos de vehículo en MySQL.
  2. Sube fotos a Cloudinary y recolecta URLs.
  3. Genera el HTML del reporte técnico de Euro Autos (agrupación de piezas, traducción de posiciones, badges) de forma completamente determinista en PHP, sin IA.
  4. Llama al endpoint `/pdf` de **Browserless** vía cURL con el HTML generado.
  5. Sube el PDF resultante a Cloudinary y extrae la URL pública.
  6. Envía el JSON final (con `pdf_url`, `urls_fotos`, `aseguradora`, `datos_vehiculo`) al webhook de n8n.
- **Modelo Relacional**:
  - `vehiculos`: Placa, Marca, Línea, Modelo, Color, VIN, Propietario, Cédula.
  - `inspecciones`: Estado (`WAITING_PLATE`, `COMPLETADO`), UID, `chat_id` de Telegram, Placa.
  - `inspecciones_detalle`: Payload JSON de piezas, observaciones y URLs fotográficas.
  - `usuarios`: `chat_id` y nombre de usuario de Telegram.

### 3. Workflow de n8n (Solo Orquestación — Sin IA para PDF)
- **Recepción por Webhook** (`POST /webhook/...`):
  - Validación `GateKeeper` del `UID` → actualización de estado a `COMPLETADO`.
- **Distribución en Telegram**:
  - Notificación al operario con el PDF adjunto, datos clave del vehículo, galería de fotos y enlace de edición/corrección.
  - Pregunta interactiva para autorizar el envío por correo.
  - Formulario dinámico para seleccionar aseguradora (si no se seleccionó desde el formulario).
- **Despacho por Email (Gmail)**:
  - PDF adjunto + mosaico fotográfico HTML hacia `siniestros@euroautos.co`.
  - Confirmación de despacho al bot de Telegram.
- **Bot de Telegram (Flujo de inicio)**:
  - Comando `/cotizar` → Genera UID → Solicita foto de matrícula.
  - Foto de matrícula → GPT-4o Vision extrae datos → Upsert en `vehiculos` e `inspecciones` → Envía link al formulario web.

### 4. Diseño Responsivo
- **Desktop (> 1024px)**: Panel lateral visible con lista de piezas seleccionadas y botón de envío.
- **Tablet (768px – 1024px)**: Panel lateral oculto; FAB (botón flotante naranja) siempre visible.
- **Móvil (< 600px)**: FAB visible, topbar compacto sin texto de marca ni descripción del vehículo.