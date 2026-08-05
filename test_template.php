<?php
// test_template.php
require_once __DIR__ . '/pdf_template.php';

$filasDemo = '
<tr class="cat-header"><td colspan="4">PARTE DELANTERA</td></tr>
<tr class="data-row">
  <td class="piece-name">Bancada</td>
  <td><span class="badge b-FUERTE">REPARACIÓN FUERTE</span></td>
  <td class="pos-cell">Central Derecho</td>
  <td class="nota-cell">Golpe estructural leve en esquina</td>
</tr>
<tr class="cat-header"><td colspan="4">CABINA Y PUERTAS</td></tr>
<tr class="data-row">
  <td class="piece-name">Espejo Retrovisor</td>
  <td><span class="badge b-CAMBIO">CAMBIO</span></td>
  <td class="pos-cell">Central Izquierdo</td>
  <td class="nota-cell">Lente fisurado</td>
</tr>
';

$obsDemo = '<li>Vehículo ingresa en grúa</li><li>Requiere alineación de chasis</li>';

$html = generarHtmlReporte([
    'placa'          => 'FVP680',
    'fechaHoy'       => date('d/m/Y'),
    'propietario'    => 'SASTOQUE SOTO LEIDY JOHANNA',
    'cedula'         => '38757397',
    'marcaLinea'     => 'RENAULT LOGAN',
    'modelo'         => '2020',
    'color'          => 'BLANCO GLACIAL',
    'vin'            => '9FB4SRC9BLM013543',
    'aseguradoraEsc' => 'HDI',
    'totalPiezas'    => 2,
    'filasHtml'      => $filasDemo,
    'obsHtml'        => $obsDemo,
]);

echo $html;
