<?php
require_once __DIR__ . '/api_client.php';

$error = null;
$formData = [
    'nombre_tipo_objeto' => '',
    'sitio_hallazgo' => '',
    'latitud' => '',
    'longitud' => '',
    'fecha_hallazgo' => date('Y-m-d'),
    'estado_conservacion' => 'REGULAR',
    'descripcion' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización y captura de campos
    $formData['nombre_tipo_objeto'] = trim($_POST['nombre_tipo_objeto'] ?? '');
    $formData['sitio_hallazgo'] = trim($_POST['sitio_hallazgo'] ?? '');
    $formData['latitud'] = trim($_POST['latitud'] ?? '');
    $formData['longitud'] = trim($_POST['longitud'] ?? '');
    $formData['fecha_hallazgo'] = trim($_POST['fecha_hallazgo'] ?? '');
    $formData['estado_conservacion'] = trim($_POST['estado_conservacion'] ?? 'REGULAR');
    $formData['descripcion'] = trim($_POST['descripcion'] ?? '');

    // Construcción del payload para la API REST
    $payload = [
        'nombre_tipo_objeto' => $formData['nombre_tipo_objeto'],
        'sitio_hallazgo' => $formData['sitio_hallazgo'],
        'fecha_hallazgo' => $formData['fecha_hallazgo'],
        'estado_conservacion' => $formData['estado_conservacion'],
        'descripcion' => $formData['descripcion'] !== '' ? $formData['descripcion'] : null,
        'latitud' => $formData['latitud'] !== '' ? (float)$formData['latitud'] : null,
        'longitud' => $formData['longitud'] !== '' ? (float)$formData['longitud'] : null,
    ];

    $response = api_create_pieza($payload);

    if ($response['success']) {
        header('Location: index.php?msg=creado');
        exit;
    } else {
        $error = $response['error'] ?: 'No se pudo registrar la pieza.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Pieza | Patrimonio Cultural</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div class="header-title">
            <h1>Dirección Nacional de Patrimonio Cultural</h1>
            <p>Registro de Hallazgo Arqueológico en Campo</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            ← Volver al Listado
        </a>
    </div>
</header>

<main class="container">
    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <h2 class="section-title" style="margin-bottom: 20px;">Formulario de Registro de Pieza</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>✕ Error al registrar:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="crear.php">
            <div class="form-group">
                <label class="form-label" for="nombre_tipo_objeto">
                    Nombre o Tipo de Objeto <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="nombre_tipo_objeto" 
                    name="nombre_tipo_objeto" 
                    class="form-control" 
                    required 
                    placeholder="Ej. Vasija ceremonial trípode, Hacha de obsidiana..."
                    value="<?= htmlspecialchars($formData['nombre_tipo_objeto']) ?>"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="sitio_hallazgo">
                    Sitio Arqueológico / Ubicación del Hallazgo <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="sitio_hallazgo" 
                    name="sitio_hallazgo" 
                    class="form-control" 
                    required 
                    placeholder="Ej. Tazumal Sector B, Joya de Cerén Estructura 3..."
                    value="<?= htmlspecialchars($formData['sitio_hallazgo']) ?>"
                >
            </div>

            <div class="form-row">
                <div class="form-col form-group">
                    <label class="form-label" for="latitud">Latitud (grados decimales)</label>
                    <input 
                        type="number" 
                        step="any" 
                        min="-90" 
                        max="90" 
                        id="latitud" 
                        name="latitud" 
                        class="form-control" 
                        placeholder="Ej. 13.980300"
                        value="<?= htmlspecialchars((string)$formData['latitud']) ?>"
                    >
                    <div class="form-help">Rango permitido: -90.00 a 90.00</div>
                </div>

                <div class="form-col form-group">
                    <label class="form-label" for="longitud">Longitud (grados decimales)</label>
                    <input 
                        type="number" 
                        step="any" 
                        min="-180" 
                        max="180" 
                        id="longitud" 
                        name="longitud" 
                        class="form-control" 
                        placeholder="Ej. -89.674700"
                        value="<?= htmlspecialchars((string)$formData['longitud']) ?>"
                    >
                    <div class="form-help">Rango permitido: -180.00 a 180.00</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-group">
                    <label class="form-label" for="fecha_hallazgo">
                        Fecha del Hallazgo <span class="required">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha_hallazgo" 
                        name="fecha_hallazgo" 
                        class="form-control" 
                        required 
                        max="<?= date('Y-m-d') ?>"
                        value="<?= htmlspecialchars($formData['fecha_hallazgo']) ?>"
                    >
                </div>

                <div class="form-col form-group">
                    <label class="form-label" for="estado_conservacion">
                        Estado de Conservación <span class="required">*</span>
                    </label>
                    <select id="estado_conservacion" name="estado_conservacion" class="form-control" required>
                        <option value="EXCELENTE" <?= $formData['estado_conservacion'] === 'EXCELENTE' ? 'selected' : '' ?>>EXCELENTE</option>
                        <option value="REGULAR" <?= $formData['estado_conservacion'] === 'REGULAR' ? 'selected' : '' ?>>REGULAR</option>
                        <option value="FRAGMENTADO" <?= $formData['estado_conservacion'] === 'FRAGMENTADO' ? 'selected' : '' ?>>FRAGMENTADO</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción y Notas Morfológicas</label>
                <textarea 
                    id="descripcion" 
                    name="descripcion" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Detalles sobre materiales, motivos decorativos, estrato geológico o contexto de descubrimiento..."
                ><?= htmlspecialchars($formData['descripcion']) ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Registro de Pieza</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
