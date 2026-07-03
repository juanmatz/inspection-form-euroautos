Quiero implementar la carga de imágenes en la inspección de Euro Autos de forma global. Agregaremos un botón con un ícono de cámara en la Topbar (dentro de .topbar-actions, al lado de #btn-open-notes). Al presionarlo, debe activar un <input type="file" multiple accept="image/*" capture="environment"> oculto para que el inspector pueda capturar una o varias fotos en tiempo real con la cámara de su dispositivo durante el proceso. Las fotos capturadas deben acumularse en el estado de la aplicación, mostrar una vista previa compacta para que puedan revisarse o eliminarse, subirse a Cloudinary desde PHP y sus URLs finales anexarse al flujo que va hacia n8n.

Archivos clave a modificar:

@index.html -> Agregar el botón de la cámara en la barra superior, el input con capture="environment" y un contenedor modal o sección colapsable (debajo de los campos del vehículo) para mostrar las previsualizaciones de las fotos tomadas.

@app.js -> Modificar la clase principal InspectionApp. Debe manejar un arreglo global this.fotos = [] para almacenar los archivos binarios (Blob/File) capturados en cada disparo de la cámara. Además, el método submitFinal() debe refactorizarse para enviar los datos usando FormData en lugar de JSON crudo, permitiendo transmitir las piezas, los datos del vehículo y los archivos binarios en la misma petición.

@send_inspection.php -> Adaptar el backend para recibir el payload mixto. Debe decodificar el string del JSON, procesar la base de datos local utilizando los Prepared Statements existentes de MySQLi, e iterar sobre el arreglo de $_FILES['fotos']. Cada imagen temporal debe subirse a Cloudinary usando su flujo seguro en el backend, recolectar las URLs generadas, inyectarlas en el payload definitivo y enviarlo todo por cURL hacia n8n.

Requerimientos Críticos de Seguridad:

Mitigación de RCE (Ejecución Remota de Código): No almacenes las fotos físicamente en el almacenamiento permanente de nuestro hosting. Súbelas a la API de Cloudinary usando directamente la ruta temporal de PHP (tmp_name) para que se destruyan inmediatamente al terminar el script.

Validación Estricta de Binarios: En el código PHP, valida cada archivo usando mime_content_type() para asegurar que son estructuras de imagen reales (JPEG, PNG, WEBP) y rechaza cualquier extensión camuflada. Limita el peso a un máximo de 6MB por fotografía.

Integridad Relacional: No alteres el funcionamiento actual de guardado del vehículo ni la actualización de la placa en la base de datos local.

Aislamiento de Credenciales: Toda comunicación con Cloudinary y n8n debe ser firmada en el backend PHP; el cliente de JS jamás debe conocer las API Keys.

Por favor, genera los snippets de código correspondientes para implementar este flujo de captura en vivo de forma segura.