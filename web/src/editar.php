<?php
require_once __DIR__ . '/api_client.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de pieza no válido.'));
    exit;
}

$error = null;

// Obtener datos actuales de la pieza
$getRes = api_get_pieza($id);
if (!$getRes['success']) {
    $errMsg = $getRes['error'] ?: "La pieza con ID #{$id} no fue encontrada.";
    header('Location: index.php?error=' . urlencode($errMsg));
    exit;
}

$pieza = $getRes['data'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'nombre_tipo_objeto' => trim($_POST['nombre_tipo_objeto'] ?? ''),
        'sitio_hallazgo' => trim($_POST['sitio_hallazgo'] ?? ''),
        'latitud' => trim($_POST['latitud'] ?? ''),
        'longitud' => trim($_POST['longitud'] ?? ''),
        'fecha_hallazgo' => trim($_POST['fecha_hallazgo'] ?? ''),
        'estado_conservacion' => trim($_POST['estado_conservacion'] ?? 'REGULAR'),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
    ];

    $payload = [
        'nombre_tipo_objeto' => $formData['nombre_tipo_objeto'],
        'sitio_hallazgo' => $formData['sitio_hallazgo'],
        'fecha_hallazgo' => $formData['fecha_hallazgo'],
        'estado_conservacion' => $formData['estado_conservacion'],
        'descripcion' => $formData['descripcion'] !== '' ? $formData['descripcion'] : null,
        'latitud' => $formData['latitud'] !== '' ? (float)$formData['latitud'] : null,
        'longitud' => $formData['longitud'] !== '' ? (float)$formData['longitud'] : null,
    ];

    $response = api_update_pieza($id, $payload);

    if ($response['success']) {
        header('Location: index.php?msg=actualizado');
        exit;
    } else {
        $error = $response['error'] ?: 'No se pudo actualizar la pieza.';
        // Preservar datos ingresados
        $pieza = array_merge($pieza, $formData);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pieza #<?= $id ?> | Patrimonio Cultural</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div class="header-title">
            <h1>Dirección Nacional de Patrimonio Cultural</h1>
            <p>Modificación de Registro de Pieza Arqueológica</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            ← Volver al Listado
        </a>
    </div>
</header>

<main class="container">
    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <h2 class="section-title" style="margin-bottom: 20px;">
            Editar Pieza Arqueológica #<?= htmlspecialchars((string)$id) ?>
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>✕ Error al actualizar:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="editar.php?id=<?= $id ?>">
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
                    value="<?= htmlspecialchars($pieza['nombre_tipo_objeto'] ?? '') ?>"
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
                    value="<?= htmlspecialchars($pieza['sitio_hallazgo'] ?? '') ?>"
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
                        value="<?= htmlspecialchars((string)($pieza['latitud'] ?? '')) ?>"
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
                        value="<?= htmlspecialchars((string)($pieza['longitud'] ?? '')) ?>"
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
                        value="<?= htmlspecialchars($pieza['fecha_hallazgo'] ?? '') ?>"
                    >
                </div>

                <div class="form-col form-group">
                    <label class="form-label" for="estado_conservacion">
                        Estado de Conservación <span class="required">*</span>
                    </label>
                    <select id="estado_conservacion" name="estado_conservacion" class="form-control" required>
                        <?php $currentEstado = strtoupper($pieza['estado_conservacion'] ?? 'REGULAR'); ?>
                        <option value="EXCELENTE" <?= $currentEstado === 'EXCELENTE' ? 'selected' : '' ?>>EXCELENTE</option>
                        <option value="REGULAR" <?= $currentEstado === 'REGULAR' ? 'selected' : '' ?>>REGULAR</option>
                        <option value="FRAGMENTADO" <?= $currentEstado === 'FRAGMENTADO' ? 'selected' : '' ?>>FRAGMENTADO</option>
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
                ><?= htmlspecialchars($pieza['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</main>

</body>
</html>
