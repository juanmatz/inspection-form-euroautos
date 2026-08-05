# CHANGELOG — Formulario de Inspección Técnica (Euro Autos)

> [!IMPORTANT]
> **AVISO OBLIGATORIO PARA DESARROLLADORES**
> Este archivo es el registro oficial de versiones del proyecto.
> **Cada vez que se modifique cualquier archivo del proyecto, se debe actualizar este CHANGELOG** antes de hacer commit o desplegar a producción.
> El registro debe incluir: fecha, versión, archivos modificados y descripción clara del cambio.
> No hacerlo dificulta el diagnóstico de errores y la trazabilidad del sistema.

---

## Formato de entradas

```
## [vX.Y.Z] — YYYY-MM-DD
### Archivos modificados
### Cambios
### Notas técnicas (opcional)
```

## [4.3.1] — 2026-08-04

### Archivos modificados
- `pdf_template.php` — Se agregaron reglas de `word-wrap` y `word-break` a las notas de la tabla y a las observaciones generales para evitar desbordes visuales al ingresar textos largos sin espacios (solución de prueba de estrés).
- `app.js` — Se ocultó el botón "Nueva" en el widget flotante cuando el envío es exitoso, manteniendo únicamente la opción de cerrar (X).
- `index.html` — Actualización del cache buster de JavaScript a `app.js?v=4.3.0`.

---

## [4.3.0] — 2026-08-04

### Archivos modificados
- `send_inspection.php` — Se eliminó la subida del reporte PDF a Cloudinary. Ahora el PDF generado se guarda localmente en el servidor (`/reportes_pdf/`) y se envía la URL pública de este archivo a n8n. Cloudinary queda reservado exclusivamente para la subida de fotografías divididas en subcarpetas por vehículo.

---

## [4.2.0] — 2026-08-04

### Archivos modificados
- `send_inspection.php` — Generación de filas `<tr>` en la tabla del PDF cuando el operario selecciona una pieza y posición pero no elige una acción del menú desplegable (por defecto se asigna `REVISIÓN`).
- `pdf_template.php` — Ajuste de proporciones de flex y ajuste multilínea (`word-wrap: break-word`) para que los nombres largos de propietario, vehículo y color no se recorten con puntos suspensivos (`...`).

### Cambios
- **1. Inclusión Garantizada de Piezas en la Tabla del PDF:**
  - Cuando se agrega una pieza y se especifica la posición (ej: `Bómper` -> `Trasero + Central`), pero no se selecciona manualmente un tipo de daño del select, el sistema asigna automáticamente la categoría `REVISIÓN` para garantizar que la fila se renderice en el PDF.
- **2. Corrección de Recorte en Encabezado de Vehículo:**
  - Se eliminó el `text-overflow: ellipsis` del bloque de datos del vehículo.
  - Se ajustaron las proporciones de columna (Propietario flex 1.8, Vehículo flex 1.4, VIN flex 2.2) permitiendo que los nombres largos (ej. *SASTOQUE SOTO LEIDY JOHANNA*) quepan completos.

---

## [4.1.2] — 2026-08-04

### Archivos modificados
- `send_inspection.php` — Manejo del caso en que el webhook de n8n responde `"No item to return was found"`.

### Cambios
- **Manejo de Respuesta de n8n:**
  - Cuando n8n está configurado para responder al finalizar el flujo y el último nodo no retorna items, devuelve la cadena `{"code":0,"message":"No item to return was found"}`.
  - Como en este punto n8n **ya recibió exitosamente el payload** y el PDF **ya fue generado y subido a Cloudinary**, `send_inspection.php` ahora interpreta esta respuesta como **ÉXITO (HTTP 200)** para que el cliente web reciba la pantalla de confirmación exitosa.

---

## [4.1.1] — 2026-08-04

### Archivos modificados
- `send_inspection.php` — Corrección de firma criptográfica (`sha1`) para la subida del PDF generado a Cloudinary.

### Cambios
- **Corrección de Firma de Cloudinary (`Invalid Signature`):**
  - Se eliminó el parámetro `resource_type=raw` de la cadena a firmar `$pdfParamsSign` en la subida del PDF. Cloudinary no incluye `resource_type` en la cadena de firma de parámetros, por lo que provocaba un descalce entre la firma calculada y la esperada por la API de Cloudinary.

---

## [4.1.0] — 2026-08-04

### Archivos modificados
- `index.html` — Botón de topbar actualizado a formato de píldora destacada `ENVIAR ✓`. Notificación flotante superior en DOM (`#send-status-widget`) con botón de cierre.
- `app.css` — Transformación de `.send-status-widget` en una **Notificación Flotante Superior Simulado** (`top: 14px; left: 50%`, `z-index: 25000`) estilo Dynamic Island con desenfoque de fondo y borde glowing. Rediseño del botón superior derecho `.btn-topbar-send`.
- `app.js` — Inyección dinámica de datos del vehículo (Placa, Aseguradora) en la notificación flotante superior al enviar.

### Cambios
- **1. Notificación Flotante Superior Simulado (Sin permisos de navegador):**
  - La notificación de estado de envío ya no se ubica abajo. Ahora aparece centrada en la **parte superior de la pantalla** (`top: 14px`, `z-index: 25000`), flotando por encima de la barra superior, paneles y modales.
  - Muestra en tiempo real la Placa del vehículo y la Aseguradora seleccionada, además de la animación de envío y estado de confirmación / error.
  - Incluye botón de cierre `×` y botón de acción.
- **2. Botón Superior de Envío `ENVIAR ✓`:**
  - El botón superior derecho en la cabecera ahora muestra el texto destacado `ENVIAR ✓` con un degradado naranja resplandeciente, inmediatamente accesible en PC, Tablets y Smartphones en la parte superior.

---

## [4.0.0] — 2026-08-04

### Archivos modificados
- `app.css` — **RESTRUCTURACIÓN COMPLETA DE MAQUETACIÓN FLEXBOX**. Eliminada la dependencia frágil de `topbar` fija + `padding-top` calculado en JS. Se convirtió `body` en un contenedor flex vertical de `100dvh`, logrando que la cabecera, el área de piezas y el panel lateral con su botón de envío se ajusten al 100% de forma automática en PC, iPad/Tablets y Smartphones sin desbordamiento.
- `index.html` — Bump de versión a `v=4.0.0`.

### Cambios
- **1. Reestructuración Arquitectónica del Layout (Solución Definitiva a Botones Ocultos):**
  - Se cambió `html, body` a `display: flex; flex-direction: column; height: 100dvh; overflow: hidden;`.
  - `.topbar` pasa a `position: relative; flex-shrink: 0;`. Al abrir/cerrar campos del vehículo o fotos, la barra superior crece naturalmente sin tapar nada.
  - `.body-layout` pasa a `flex: 1; min-height: 0; display: flex; overflow: hidden;`. Ocupa dinámicamente el 100% del alto restante del dispositivo.
  - `.side-panel` en PC/Tablets pasa a `height: 100%; display: flex; flex-direction: column;`. Su pie (`.panel-foot`) con el botón **"Finalizar y Enviar"** queda **100% FIJO Y VISIBLE** en la parte inferior del panel.
- **2. Ajuste Fino para Tablets y Móviles:**
  - **Tablets (768px – 1024px, ej: iPad Air 820px):** Panel lateral visible (`width: 230px`), botón de envío `#btn-send` permanente en pantalla, FAB oculto.
  - **Móviles (< 768px):** Panel lateral oculto, FAB flotante visible (`z-index: 9999`). Al abrir el drawer móvil, el FAB se oculta para no solapar el botón del drawer.

---

## [3.4.0] — 2026-08-04

### Archivos modificados
- `app.css` — Habilitación del panel lateral `.side-panel` en Tablets (768px – 1024px, ej: iPad Air 820px). Eliminación de solapamiento de botones ocultando `.fab-send` al abrir la persiana móvil (`body.drawer-open`).
- `app.js` — Conmutación automática de la clase `drawer-open` en `body` al abrir/cerrar la persiana móvil.
- `index.html` — Bump de versión a `v=3.4.0`.

### Cambios
- **1. Solución de Solapamiento de Botones en Móvil:**
  - Al abrir la lista de piezas seleccionadas (`.drawer`), el botón flotante `.fab-send` se oculta automáticamente (`display: none !important`), dejando únicamente visible el botón **"Finalizar y Enviar"** del drawer.
- **2. Ajuste de Maquetación Responsiva para Tablets (iPad Air 820px / Tablets 768px - 1024px):**
  - En pantallas de tablet (≥ 768px), el panel lateral `.side-panel` vuelve a mostrarse en el costado derecho con la lista de piezas y el botón principal **"Finalizar y Enviar"** siempre visible.
  - Se desactiva el FAB y la persiana móvil en tablets para aprovechar la pantalla completa.

---

## [3.3.2] — 2026-08-04

### Archivos modificados
- `app.css` — Eliminación de `overflow: hidden` del `body`/`html` para evitar recortes del viewport en navegadores móviles/emuladores. Elevación de `z-index: 9999 !important` y degradado vibrante en `.fab-send`.
- `index.html` — Bump de versión a `v=3.3.2`.

### Cambios
- **Corrección de Capa Flotante (`.fab-send`):**
  - El botón flotante `.fab-send` ahora usa `z-index: 9999 !important;` y `position: fixed !important;` para estar garantizado por encima de todas las capas y contenedores.
  - Se removió el recambio de `overflow: hidden` del `body` que en ciertas vistas móviles recortaba elementos fijos inferiores.
  - Se le dio una apariencia destacada tipo gradiente con sombra glowing `0 8px 25px rgba(249, 115, 22, 0.65)` para ser inmediatamente visible en cualquier resolución.

---

## [3.3.1] — 2026-08-04

### Archivos modificados
- `app.css` — Implementación de `100dvh` (Dynamic Viewport Height) y soporte de `env(safe-area-inset-bottom)` para corregir recortes en dispositivos móviles y tablets. Reorganización de media queries para tablets (iPad Air 820px, Samsung S20 412px).
- `index.html` — Bump de versión a `v=3.3.1`.

### Cambios
- **Solución Definitiva de Botón Flotante (`.fab-send`) Recortado en Móviles/Tablets:**
  - Se sustituyó la altura rígida `100vh` por `100dvh` (Dynamic Viewport Height) en `body` y `.body-layout` para considerar las barras de navegación dinámicas de navegadores móviles (Chrome/Safari en Samsung Galaxy, iPad, Android/iOS).
  - Ajustada la posición del FAB con elevación garantizada y soporte de área segura: `bottom: calc(24px + env(safe-area-inset-bottom, 0px)); right: 20px; z-index: 350;`.
  - Unificadas las media queries responsivas a `@media (max-width: 1024px)` con `display: flex !important;` en `.fab-send` para asegurar su visibilidad permanente en todos los tamaños de tablets (iPad Air 820px, etc.) y móviles.

---

## [3.3.0] — 2026-08-04

### Archivos modificados
- `index.html` — Añadido botón `#btn-topbar-send` con icono de chulo `fa-check` en la esquina superior derecha (`.topbar-actions`). Bump de cache a `v=3.3.0`.
- `app.css` — Nuevos estilos para `.btn-topbar-send` (botón con borde e icono naranja brillante `#f97316` + efecto glow en hover).
- `app.js` — Vinculado `#btn-topbar-send` para ejecutar `this.openAseguradoraModal()`.

### Cambios
- **Nuevo Botón Accesible de Envío en Topbar:**
  - Se agregó un botón de icono con un chulo (check `✓`) de color naranja destacado en la esquina superior derecha de la topbar (`.topbar-actions`).
  - Siempre visible tanto en escritorio, tablet como móvil.
  - Ejecuta exactamente la misma lógica de envío que el botón principal y el FAB (abre la selección de Aseguradora y la confirmación).
  - Resuelve la dificultad de acceso al botón de envío en tablets/móviles al tener un punto de acceso directo y fijo en la cabecera.

---

## [3.2.0] — 2026-08-04

### Archivos modificados / creados
- `pdf_template.php` — **[NUEVO]** Plantilla HTML modular e independiente para generación de reportes PDF.
- `send_inspection.php` — Integración de `pdf_template.php` y eliminación de código HTML duplicado.

### Cambios

#### `pdf_template.php` (Nuevo archivo)
- **Separación de responsabilidades**: La lógica de renderizado HTML del PDF ahora está aislada en la función `generarHtmlReporte(array $vars): string`.
- **Estructura visual basada en reportes oficiales (RKN867 / FVP680)**:
  - Header institucional con logo de vehículo SVG, nombre de empresa, subtítulo y chip destacado con Placa y Fecha.
  - Grid horizontal flexible para datos del vehículo (Propietario, Cédula/NIT, Vehículo, Modelo, Color, VIN monospace, Aseguradora).
  - Tabla de 4 columnas exactas: `Pieza | Tipo de Reparación | Posición | Notas Técnicas`.
  - Badges cromáticos redondeados para cada tipo de acción (`LEVE`, `MEDIA`, `FUERTE`, `CAMBIO`, `PINTURA`, `D/M`, `REVISIÓN`).
  - Recuadro de Observaciones Generales y Footer institucional.
- **Control estricto de saltos de página PDF**:
  - `page-break-inside: avoid;` en `.report-header`, `.cat-header`, `.data-row`, `.obs-box` y `.doc-footer`.
  - `page-break-after: avoid;` en `.cat-header` para garantizar que un encabezado de categoría nunca quede solo al final de la página.

#### `send_inspection.php`
- Eliminado el bloque `heredoc` de HTML.
- Importa `pdf_template.php` y llama a `generarHtmlReporte()` pasando los datos procesados.
- Genera las filas HTML (`filasHtml`) en 4 columnas compatibles con la nueva plantilla.

---

## [3.1.0] — 2026-08-04

### Archivos modificados
- `send_inspection.php` — Reescritura completa
- `.env` — Variables nuevas
- `db_config.php` — Constantes nuevas
- `index.html` — Nuevo modal de aseguradora, bump de versiones CSS/JS
- `app.css` — Estilos de aseguradora + corrección responsive
- `app.js` — Lógica del modal de aseguradora, payload actualizado
- `context.md` — Documentación actualizada al estado actual del sistema

### Cambios

#### `send_inspection.php` — Reescritura completa (7 secciones)
- **Eliminada** la dependencia del `AI Agent` de n8n (Claude Sonnet / OpenRouter) para la generación del reporte.
- **Eliminada** la dependencia de la API paga `htmlcsstopdf`.
- **Nueva sección 4 — Generador HTML determinista en PHP**:
  - Agrupa piezas por nombre base ignorando el sufijo de instancia `#N` (ej. `Espejo#0` y `Espejo#1` → pieza única `Espejo` con sus posiciones separadas).
  - Fusiona los `lados` por `comboKey`; cada combinación `(pieza, comboKey, acción)` genera una fila `<tr>` independiente en el PDF.
  - Traduce claves de posición (`DEL`, `IZQ`, `CEN+DER`, etc.) a texto legible en español.
  - Renderiza badges de color por tipo de acción: `LEVE`, `MEDIA`, `FUERTE`, `CAMBIO`, `PINTURA`, `DESMONTAJE_MONTAJE`, `REVISION`.
  - Incluye campo **Aseguradora** en el encabezado del reporte.
  - Renderiza observaciones del operario como lista `<li>`, separando por `\n` o `;`.
- **Nueva sección 5 — Llamada a Browserless**:
  - `POST /pdf?token=BROWSERLESS_TOKEN` con el HTML generado.
  - Browserless (Docker `browserless/chrome`) devuelve el PDF binario.
  - Timeout de 30 segundos. Si falla, retorna HTTP 500 con mensaje de error.
- **Nueva sección 6 — Subida del PDF a Cloudinary**:
  - El binario PDF se guarda en un archivo temporal (`sys_get_temp_dir()`).
  - Se sube a Cloudinary como `raw` asset en la carpeta `reportes_pdf/`.
  - El archivo temporal se elimina después de la subida.
  - Se obtiene la `secure_url` pública del PDF generado.
- **Sección 7 — Envío a n8n simplificado**:
  - n8n ya no genera el PDF; solo recibe el payload con `{ uid, pdf_url, urls_fotos, aseguradora, datos_vehiculo, observaciones, total_piezas }`.
  - n8n se encarga únicamente de orquestar la distribución (Telegram + Gmail).

#### `.env`
- Agregadas dos variables nuevas:
  ```
  BROWSERLESS_URL=https://tu-dominio.com/browserless
  BROWSERLESS_TOKEN=tu_token_generado_con_openssl
  ```
  > Reemplazar con los valores reales del servidor antes de desplegar.

#### `db_config.php`
- Agregados dos fallbacks de constantes PHP:
  ```php
  if (!defined('BROWSERLESS_URL'))   define('BROWSERLESS_URL', '');
  if (!defined('BROWSERLESS_TOKEN')) define('BROWSERLESS_TOKEN', '');
  ```

#### `index.html`
- Nuevo modal `#aseguradora-modal` con 4 opciones: **Allianz**, **HDI**, **Mapfre**, **Personal**.
- El modal bloquea el envío hasta que se seleccione una opción (validación visual + mensaje de error).
- Versiones de caché actualizadas: `app.css?v=3.1.0` y `app.js?v=3.1.0`.

#### `app.css`
- Nuevas clases para el modal de aseguradora:
  - `.aseg-grid` — Grid 2x2 para las 4 opciones.
  - `.aseg-btn` — Botón de opción con hover y estado `.selected` (naranja con glow).
  - `.aseg-error` — Mensaje de error animado cuando no se selecciona ninguna opción.
- **Corrección responsiva para tablet**:
  - `@media (max-width: 1024px)`: oculta `.side-panel`, muestra `.fab-send` y `.mobile-panel-btn`.
  - `@media (max-width: 600px)`: comprime topbar (oculta `.brand-name` y `.vs-desc`), ajusta padding.
  - Reemplaza el breakpoint anterior `@media (max-width: 480px)` que no cubría tablets.

#### `app.js`
- `InspectionApp.constructor()`:
  - Nueva referencia `this._aseguradoraModal`.
  - Nueva propiedad `this.aseguradora = null`.
- Nuevo método `openAseguradoraModal()`:
  - Valida que haya al menos una pieza antes de abrir el modal.
  - Restaura el estado visual de selección previa si el usuario vuelve al modal.
- Nuevo método `closeAseguradoraModal()`.
- Los 3 botones de envío (`#btn-send`, `#fab-send`, `#btn-send-drawer`) ahora llaman a `openAseguradoraModal()` en lugar de `openConfirm()` directamente.
- `submitFinal()`: el payload ahora incluye `aseguradora: this.aseguradora`.
- `_bindGlobalActions()`: nuevos listeners para `.aseg-btn`, `#btn-confirm-aseg`, `#btn-cancel-aseg` y `#close-aseg`.

---

## [3.0.1] — anterior a 2026-08-04

### Archivos modificados
- `app.js`
- `send_inspection.php`
- `index.html`

### Cambios
- Implementación de captura fotográfica en vivo desde la topbar (`capture="environment"`).
- Subida de fotos a Cloudinary desde el backend PHP con validación MIME estricta (JPG, PNG, WEBP, máximo 6MB).
- Envío de inspección mediante `FormData` (soporte de archivos binarios + JSON).
- Previsualización de fotos capturadas con opción de eliminación antes del envío.
- Recuperación de URLs de fotos previas desde `inspecciones_detalle` al enviar borrador final.
- Guardado continuo de borradores enlazados al `UID` de sesión.

---

## [3.0.0] — anterior a 2026-08-04

### Cambios
- Arquitectura OOP completa en `app.js` (`InspectionApp`, `PartCard`, `PartInstance`, `SideSelector`, `SummaryPanel`, `AutoSaver`).
- Topbar fija con resumen de vehículo, campos colapsables, búsqueda y navegación por categorías.
- Panel lateral (desktop) con lista de piezas seleccionadas y botón de envío.
- Drawer deslizante (móvil) con lista de piezas y botón de envío.
- FAB visible en móvil.
- Modal de selección de lados con diagrama SVG interactivo (vista superior y lateral).
- Modal de notas/observaciones generales.
- Modal de confirmación de envío con resumen de piezas.
- Widget flotante de estado de envío (enviando / éxito / error).
- Integración con n8n mediante webhook autenticado con `X-API-KEY`.
- Flujo completo Telegram: notificación al operario con PDF adjunto y enlace de corrección.
