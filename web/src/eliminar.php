<?php
require_once __DIR__ . '/api_client.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de pieza no válido.'));
    exit;
}

$error = null;

// Obtener datos de la pieza para mostrar confirmación
$getRes = api_get_pieza($id);
if (!$getRes['success']) {
    $errMsg = $getRes['error'] ?: "La pieza con ID #{$id} no existe.";
    header('Location: index.php?error=' . urlencode($errMsg));
    exit;
}

$pieza = $getRes['data'];

// Si se envía la confirmación vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = api_delete_pieza($id);

    if ($response['success']) {
        header('Location: index.php?msg=eliminado');
        exit;
    } else {
        $error = $response['error'] ?: 'No se pudo eliminar la pieza arqueológica.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Pieza #<?= $id ?> | Patrimonio Cultural</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div class="header-title">
            <h1>Dirección Nacional de Patrimonio Cultural</h1>
            <p>Confirmación de Eliminación de Registro</p>
        </div>
        <a href="index.php" class="btn btn-secondary">
            ← Volver al Listado
        </a>
    </div>
</header>

<main class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="confirmation-box">
            <h2 class="section-title" style="color: var(--btn-danger-bg); margin-bottom: 12px;">
                ¿Confirmar Eliminación?
            </h2>
            <p>Esta acción eliminará de forma permanente el registro del vestigio arqueológico en la base de datos.</p>

            <?php if ($error): ?>
                <div class="alert alert-error" style="text-align: left;">
                    <strong>✕ Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <dl class="item-preview">
                <dt>Identificador:</dt>
                <dd>#<?= htmlspecialchars((string)$pieza['id']) ?></dd>

                <dt>Objeto / Pieza:</dt>
                <dd><strong><?= htmlspecialchars($pieza['nombre_tipo_objeto']) ?></strong></dd>

                <dt>Sitio del Hallazgo:</dt>
                <dd><?= htmlspecialchars($pieza['sitio_hallazgo']) ?></dd>

                <dt>Fecha de Hallazgo:</dt>
                <dd><?= htmlspecialchars($pieza['fecha_hallazgo']) ?></dd>

                <dt>Estado de Conservación:</dt>
                <dd><?= htmlspecialchars($pieza['estado_conservacion']) ?></dd>
            </dl>

            <form method="POST" action="eliminar.php?id=<?= $id ?>" style="margin-top: 24px; display: flex; justify-content: center; gap: 16px;">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-danger">Sí, Eliminar Definitivamente</button>
            </form>
        </div>
    </div>
</main>

</body>
</html>
