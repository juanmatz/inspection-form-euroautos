<?php
/**
 * pdf_template.php — Plantilla HTML para reporte de inspección técnica
 *
 * ⚠️  SI MODIFICAS ESTE ARCHIVO, ACTUALIZA EL CHANGELOG.md
 *
 * Uso desde send_inspection.php:
 *   require_once 'pdf_template.php';
 *   $html = generarHtmlReporte([
 *       'placa'          => 'FVP680',
 *       'fechaHoy'       => '23/07/2026',
 *       'propietario'    => 'NOMBRE APELLIDO',
 *       'cedula'         => '12345678',
 *       'marcaLinea'     => 'RENAULT LOGAN',
 *       'modelo'         => '2020',
 *       'color'          => 'BLANCO GLACIAL',
 *       'vin'            => '9FB4SR5BLM013543',
 *       'aseguradoraEsc' => 'HDI',
 *       'totalPiezas'    => 3,
 *       'filasHtml'      => '<tr>...</tr>',
 *       'obsHtml'        => '<li>...</li>',
 *   ]);
 *
 * Estructura de $filasHtml esperada (generada en send_inspection.php):
 *   Fila de categoría: <tr class="cat-header"><td colspan="4">NOMBRE CATEGORIA</td></tr>
 *   Fila de datos:     <tr class="data-row">
 *                        <td class="piece-name">Pieza</td>
 *                        <td class="pos-cell">Delantero Izquierdo</td>
 *                        <td><span class="badge b-FUERTE">FUERTE</span></td>
 *                        <td class="nota-cell">nota opcional</td>
 *                      </tr>
 */

declare(strict_types=1);

function generarHtmlReporte(array $vars): string
{
    extract($vars, EXTR_SKIP);

    // Valores por defecto
    $placa          = $placa          ?? 'N/A';
    $fechaHoy       = $fechaHoy       ?? date('d/m/Y');
    $propietario    = $propietario    ?? 'N/A';
    $cedula         = $cedula         ?? 'N/A';
    $marcaLinea     = $marcaLinea     ?? 'N/A';
    $modelo         = $modelo         ?? 'N/A';
    $color          = $color          ?? 'N/A';
    $vin            = $vin            ?? 'N/A';
    $aseguradoraEsc = $aseguradoraEsc ?? 'N/A';
    $totalPiezas    = (int)($totalPiezas ?? 0);
    $filasHtml      = $filasHtml ?? '<tr class="data-row"><td colspan="4" style="color:#9ca3af;font-style:italic;text-align:center;padding:16px">Sin piezas registradas</td></tr>';
    $obsHtml        = $obsHtml   ?? '<li>Sin observaciones adicionales</li>';
    $labelPiezas    = $totalPiezas === 1 ? 'pieza' : 'piezas';

    ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
/* ── RESET ── */
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
  color: #1a1a2e;
  background: #ffffff;
  font-size: 12px;
  line-height: 1.5;
  padding: 28px 36px 20px 36px;
}

/* ── CONTROL DE SALTOS DE PÁGINA ──
   Reglas críticas para evitar cortes visuales feos:
   - cat-header no se separa de su primera data-row
   - data-row nunca se parte en dos páginas
   - las observaciones permanecen juntas
   - el footer no queda "flotando" solo
*/
.report-header   { page-break-inside: avoid; page-break-after: avoid; }
.section-title   { page-break-after: avoid; }
.cat-header      { page-break-inside: avoid; page-break-after: avoid; }
.data-row        { page-break-inside: avoid; }
.obs-box         { page-break-inside: avoid; }
.doc-footer      { page-break-inside: avoid; }

/* ── ENCABEZADO ── */
.report-header   { margin-bottom: 20px; }

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 14px;
  border-bottom: 2px solid #004a99;
  margin-bottom: 14px;
}

.header-left { display: flex; align-items: center; gap: 13px; }

.logo-box {
  width: 44px; height: 44px;
  background: #004a99;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.brand-name { font-size: 19px; font-weight: 800; color: #004a99; letter-spacing: -0.3px; }
.brand-sub  { font-size: 9px; color: #6b7280; letter-spacing: 1.8px; text-transform: uppercase; margin-top: 3px; }

.placa-chip { text-align: right; }

.placa-big {
  font-size: 26px; font-weight: 900;
  color: #004a99; letter-spacing: 3px;
  background: #EAF2FF;
  padding: 6px 18px; border-radius: 8px;
  border: 1.5px solid #B5D4F4;
  display: inline-block;
  font-family: 'Courier New', Courier, monospace;
}

.placa-date { font-size: 10px; color: #6b7280; margin-top: 6px; text-align: right; }

/* ── DATOS DEL VEHÍCULO ── */
.header-data {
  display: grid; 
  grid-template-columns: repeat(6, 1fr);
  background: #F0F4FF;
  border-radius: 8px; border: 1px solid #D3D8E8;
  overflow: hidden;
}

.hd-item {
  display: flex; flex-direction: column;
  padding: 10px 14px;
  border-right: 1px solid #D3D8E8;
  border-bottom: 1px solid #D3D8E8;
}

/* Bordes para la grilla (Fila 1: 4 ítems, Fila 2: 3 ítems, Fila 3: 2 ítems) */
.hd-item:nth-child(4), .hd-item:nth-child(7), .hd-item:nth-child(9) { border-right: none; }
.hd-item:nth-child(n+8) { border-bottom: none; }

/* Asignación de columnas (6 en total) */
.hd-prop { grid-column: span 2; }
.hd-ced  { grid-column: span 1; }
.hd-veh  { grid-column: span 2; }
.hd-mod  { grid-column: span 1; }

.hd-col  { grid-column: span 2; }
.hd-vin  { grid-column: span 2; }
.hd-aseg { grid-column: span 2; }

.hd-km   { grid-column: span 3; }
.hd-ubic { grid-column: span 3; }

.hd-label {
  font-size: 8px; color: #6b7280;
  letter-spacing: 0.8px; text-transform: uppercase;
  margin-bottom: 3px; font-weight: 600;
}

.hd-value {
  font-size: 11px; font-weight: 700; color: #1a1a2e;
  line-height: 1.3;
  word-wrap: break-word;
}

.hd-vin .hd-value {
  font-family: 'Courier New', Courier, monospace;
  letter-spacing: 0.5px; font-size: 10px;
}

/* ── TÍTULO DE SECCIÓN ── */
.section-title {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 22px; margin-bottom: 12px;
}

.section-title-left { display: flex; align-items: center; gap: 9px; }

.section-bar {
  width: 4px; height: 20px;
  background: #004a99; border-radius: 2px; flex-shrink: 0;
}

.section-label { font-size: 13px; font-weight: 700; color: #004a99; }

.badge-total {
  background: #004a99; color: #fff;
  font-size: 9.5px; font-weight: 700;
  padding: 3px 12px; border-radius: 20px; letter-spacing: 0.3px;
}

/* ── TABLA ── */
.table-wrap {
  border: 1px solid #D3D8E8;
  border-radius: 10px; overflow: hidden;
  margin-bottom: 18px;
}

.items-table { width: 100%; border-collapse: collapse; font-size: 11.5px; }

.items-table thead tr { background: #004a99; }

.items-table th {
  color: #ffffff; padding: 10px 14px;
  text-align: left; font-size: 10px;
  font-weight: 600; letter-spacing: 0.6px; text-transform: uppercase;
}

/* Anchos de columna (4 columnas) */
.items-table th:nth-child(1) { width: 26%; }
.items-table th:nth-child(2) { width: 22%; }
.items-table th:nth-child(3) { width: 32%; }
.items-table th:nth-child(4) { width: 20%; }

/* Fila de categoría */
.cat-header td {
  background: #E8EEF8; color: #004a99;
  font-size: 9px; font-weight: 700;
  letter-spacing: 2px; text-transform: uppercase;
  padding: 6px 14px; border-top: 1px solid #C8D4EA;
}

.items-table tbody tr.cat-header:first-child td { border-top: none; }

/* Fila de datos */
.data-row td {
  padding: 9px 14px; vertical-align: middle;
  border-top: 1px solid #EEF0F8;
}

.piece-name { font-weight: 700; color: #1a1a2e; }
.pos-cell   { color: #4b5563; font-size: 11px; }
.nota-cell  { 
  font-style: italic; color: #9ca3af; font-size: 10.5px; 
  word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;
}

/* ── BADGES ── */
.badge {
  display: inline-block;
  padding: 3px 9px; border-radius: 20px;
  font-size: 9.5px; font-weight: 700;
  letter-spacing: 0.2px;
  margin-right: 4px; margin-bottom: 2px;
  white-space: nowrap;
}

.b-LEVE    { background: #d4edda; color: #155724; }
.b-MEDIA   { background: #fff3cd; color: #856404; }
.b-FUERTE  { background: #f8d7da; color: #721c24; }
.b-CAMBIO  { background: #cce5ff; color: #004085; }
.b-PINTURA { background: #e2d9f3; color: #4a235a; }
.b-DM      { background: #fde3c8; color: #7d3a00; }
.b-REV     { background: #e2e3e5; color: #383d41; }

/* ── OBSERVACIONES ── */
.obs-box {
  padding: 12px 16px;
  background: #F7F9FC; border-radius: 8px;
  border-left: 3px solid #CBD0E0;
  margin-bottom: 24px;
}

.obs-label {
  font-size: 9px; font-weight: 700; color: #6b7280;
  letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 8px;
}

.obs-list { 
  margin: 0; padding-left: 18px; font-size: 11.5px; color: #4b5563; 
  word-wrap: break-word; overflow-wrap: break-word; word-break: break-word;
}
.obs-list li { margin-bottom: 5px; }

/* ── PIE DE PÁGINA ── */
.doc-footer {
  border-top: 1px solid #E5EAF5; padding-top: 10px;
  display: flex; justify-content: space-between; align-items: center;
}

.doc-footer span { font-size: 9.5px; color: #9ca3af; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<div class="report-header">
  <div class="header-top">
    <div class="header-left">
      <div class="logo-box">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="3" width="15" height="13" rx="2"/>
          <path d="M16 8h4l3 5v3h-7V8z"/>
          <circle cx="5.5" cy="18.5" r="2.5"/>
          <circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
      </div>
      <div>
        <div class="brand-name">EURO AUTOS</div>
        <div class="brand-sub">Reporte Técnico · Medellín, Colombia</div>
      </div>
    </div>
    <div class="placa-chip">
      <div class="placa-big"><?= htmlspecialchars($placa, ENT_QUOTES) ?></div>
      <div class="placa-date">Fecha: <?= htmlspecialchars($fechaHoy, ENT_QUOTES) ?></div>
    </div>
  </div>

  <div class="header-data">
    <div class="hd-item hd-prop">
      <span class="hd-label">Propietario</span>
      <span class="hd-value"><?= htmlspecialchars($propietario, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-ced">
      <span class="hd-label">Cédula / NIT</span>
      <span class="hd-value"><?= htmlspecialchars($cedula, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-veh">
      <span class="hd-label">Vehículo</span>
      <span class="hd-value"><?= htmlspecialchars($marcaLinea, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-mod">
      <span class="hd-label">Modelo</span>
      <span class="hd-value"><?= htmlspecialchars($modelo, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-col">
      <span class="hd-label">Color</span>
      <span class="hd-value"><?= htmlspecialchars($color, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-vin">
      <span class="hd-label">VIN</span>
      <span class="hd-value"><?= htmlspecialchars($vin, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-aseg">
      <span class="hd-label">Aseguradora</span>
      <span class="hd-value"><?= htmlspecialchars($aseguradoraEsc, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-km">
      <span class="hd-label">Kilometraje</span>
      <span class="hd-value"><?= htmlspecialchars($kilometrajeEsc, ENT_QUOTES) ?></span>
    </div>
    <div class="hd-item hd-ubic">
      <span class="hd-label">Ubicación</span>
      <span class="hd-value"><?= htmlspecialchars($ubicacionEsc, ENT_QUOTES) ?></span>
    </div>
  </div>
</div>

<!-- ══ TABLA DE INSPECCIÓN ══ -->
<div class="section-title">
  <div class="section-title-left">
    <div class="section-bar"></div>
    <span class="section-label">Inspección de Carrocería</span>
  </div>
  <span class="badge-total"><?= $totalPiezas ?> <?= $labelPiezas ?></span>
</div>

<div class="table-wrap">
  <table class="items-table">
    <thead>
      <tr>
        <th>Pieza</th>
        <th>Posición</th>
        <th>Tipo de Reparación</th>
        <th>Notas Técnicas</th>
      </tr>
    </thead>
    <tbody>
      <?= $filasHtml ?>
    </tbody>
  </table>
</div>

<!-- ══ OBSERVACIONES ══ -->
<div class="obs-box">
  <div class="obs-label">Observaciones Generales</div>
  <ul class="obs-list">
    <?= $obsHtml ?>
  </ul>
</div>

<!-- ══ PIE DE PÁGINA ══ -->
<div class="doc-footer">
  <span>EuroAutos S.A.S — Documento generado automáticamente</span>
  <span>Medellín, Colombia</span>
</div>

</body>
</html>
<?php
    return ob_get_clean();
}
