# Contexto del Sistema — Formulario de Inspección Técnica (Euro Autos)

## 📌 Descripción del Proyecto
El **Formulario de Inspección Técnica de Euro Autos** es un sistema web progresivo y automatizado diseñado para la toma de inventario y registro técnico de daños en la carrocería de vehículos en Medellín, Colombia. Permite a los operarios realizar inspecciones en tiempo real desde dispositivos móviles o de escritorio, registrar piezas afectadas, capturar fotografías en vivo, guardar borradores de forma persistente y generar reportes técnicos oficiales en PDF con distribución directa a Telegram y correo electrónico mediante automatización en n8n y modelos de IA.

---

## 🛠 Arquitectura General y Tecnologías
- **Frontend**: Single Page Application (SPA) responsiva construida con HTML5, Vanilla CSS3 y Javascript (ES6+).
- **Backend**: PHP 8+ con API REST, consultas preparadas (MySQLi Prepared Statements) y cURL para integración remota.
- **Base de Datos**: MySQL (`vehiculos`, `inspecciones`, `inspecciones_detalle`).
- **Almacenamiento Multimedia**: Cloudinary API (subida segura vía PHP sin almacenamiento permanente local).
- **Automatización e Integraciones (n8n Workflow)**:
  - **Agente de IA**: Claude Sonnet (vía OpenRouter) para procesamiento de datos, traducción de nomenclaturas y maquetación HTML.
  - **Conversión PDF**: API `HTML to PDF` (`htmlcsstopdf`).
  - **Mensajería**: Bot interactivo de Telegram.
  - **Email**: Integración Gmail OAuth2 para envío de informes a aseguradoras.

---

## 🚀 Funcionalidades Principales

### 1. Captura y Registro de Inspección (Frontend SPA)
- **Consulta de Vehículo**: Búsqueda por placa y autocompletado automático de datos técnicos y del propietario (`get_vehicle.php`).
- **Registro Detallado de Carrocería**:
  - Clasificación de piezas por categorías (Cabina, Parte Delantera, Trasera, Lados, etc.).
  - Selección de posiciones combinadas (ej. `DEL+IZQ`, `TRA+DER+SUP`, `CEN`).
  - Asignación de acciones y niveles de daño mediante badges interactivos: `LEVE`, `MEDIA`, `FUERTE`, `CAMBIO`, `PINTURA`, `D/M` (Desmontaje/Montaje) y `REVISIÓN`.
  - Registro de observaciones por pieza y observaciones generales del operario.
- **Captura Fotográfica en Vivo**:
  - Botón de cámara en la barra superior (`capture="environment"` para uso en móviles).
  - Previsualización compacta de imágenes capturadas con opción de eliminación previa al envío.
  - Envío mediante `FormData` con subida instantánea a Cloudinary desde el backend.

### 2. Gestión de Borradores y Backend (PHP & MySQL)
- **Guardado Continuo de Borradores**: Registro en tiempo real del progreso ligado a un `UID` único (`save_draft.php`).
- **Recuperación de Estado**: Reanudación de inspecciones en curso (`get_draft.php` y webhook GET `/v1/inspecciones/obtener-borrador`).
- **Modelo Relacional**:
  - `vehiculos`: Almacena información del automóvil (Placa, Marca, Línea, Modelo, Color, VIN, Propietario, Cédula).
  - `inspecciones`: Control del estado global (`PENDIENTE`, `COMPLETADO`) vinculado a `UID` y `chat_id` de Telegram.
  - `inspecciones_detalle`: Guarda el payload JSON de piezas, observaciones y array de URLs fotográficas.

### 3. Workflow de Automatización y Generación de PDF (n8n Workflow)
- **Recepción por Webhook** (`POST /v1/inspecciones/generar-pdf`):
  - Validación de seguridad `GateKeeper` del `UID`.
  - Actualización de registros locales a estado `COMPLETADO`.
- **Generación de Reporte por Agente de IA**:
  - Agrupa piezas duplicadas (remueve sufijos internos `#N`), expande acciones a filas independientes y traduce combinaciones de posición a texto legible (ej. `DEL+IZQ` → *Delantero Izquierdo*).
  - Renderiza los datos dentro de una plantilla HTML corporativa de Euro Autos con diseño moderno.
  - Ejecuta la herramienta `HTML to PDF` para producir el documento final.
- **Flujo Interactivo en Telegram**:
  - Notificación inmediata al operario indicando el inicio del procesamiento.
  - Envío del PDF generado junto a datos clave del vehículo, enlaces a fotos tomadas y enlace directo para edición/corrección.
  - Pregunta interactiva para autorizar el envío del informe por correo electrónico.
  - Formulario dinámico en chat para seleccionar la aseguradora destinataria (*Allianz*, *HDI*, *Mapfre*, *Personal*).
- **Despacho Automatizado por Email (Gmail)**:
  - Envío del reporte técnico adjunto en PDF con cuerpo HTML que incluye un mosaico fotográfico de la inspección hacia `siniestros@euroautos.co`.
  - Confirmación de despacho notificada al bot de Telegram.