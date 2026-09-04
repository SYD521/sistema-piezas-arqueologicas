<?php
require_once __DIR__ . '/api_client.php';

// Obtener listado de piezas desde la API FastAPI
$response = api_get_piezas();
$piezas = $response['success'] ? $response['data'] : [];
$apiError = $response['error'];

// Mensajes de notificación
$msg = $_GET['msg'] ?? null;
$errorParam = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Piezas Arqueológicas | Patrimonio Cultural</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="app-header">
    <div class="header-container">
        <div class="header-title">
            <h1>Dirección Nacional de Patrimonio Cultural</h1>
            <p>Sistema de Registro de Vestigios y Piezas Arqueológicas en Campo</p>
        </div>
        <a href="crear.php" class="btn btn-secondary">
            + Registrar Nueva Pieza
        </a>
    </div>
</header>

<main class="container">
    <?php if ($msg === 'creado'): ?>
        <div class="alert alert-success">
            <strong>✓ Éxito:</strong> La pieza arqueológica ha sido registrada satisfactoriamente en el sistema.
        </div>
    <?php elseif ($msg === 'actualizado'): ?>
        <div class="alert alert-success">
            <strong>✓ Éxito:</strong> Los datos de la pieza arqueológica han sido actualizados correctamente.
        </div>
    <?php elseif ($msg === 'eliminado'): ?>
        <div class="alert alert-success">
            <strong>✓ Éxito:</strong> El registro de la pieza arqueológica ha sido eliminado.
        </div>
    <?php endif; ?>

    <?php if ($errorParam): ?>
        <div class="alert alert-error">
            <strong>✕ Error:</strong> <?= htmlspecialchars($errorParam) ?>
        </div>
    <?php endif; ?>

    <?php if ($apiError): ?>
        <div class="alert alert-error">
            <strong>✕ Advertencia de Conexión:</strong> No se pudo sincronizar con la API REST (<?= htmlspecialchars($apiError) ?>). Verifica que el servicio backend esté en ejecución.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Catálogo de Piezas Registradas</h2>
                <p class="form-help">Total de hallazgos registrados: <?= count($piezas) ?></p>
            </div>
            <a href="crear.php" class="btn btn-primary">
                + Nuevo Hallazgo
            </a>
        </div>

        <?php if (empty($piezas) && !$apiError): ?>
            <div style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                <p style="font-size: 1.1rem; margin-bottom: 12px;">No se encontraron piezas arqueológicas registradas aún.</p>
                <a href="crear.php" class="btn btn-primary">Registrar la primera pieza</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tipo de Objeto</th>
                            <th>Sitio de Hallazgo</th>
                            <th>Coordenadas</th>
                            <th>Fecha Hallazgo</th>
                            <th>Estado</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($piezas as $pieza): ?>
                            <?php 
                                $estado = strtoupper($pieza['estado_conservacion'] ?? 'REGULAR');
                                $badgeClass = match($estado) {
                                    'EXCELENTE' => 'badge-excelente',
                                    'FRAGMENTADO' => 'badge-fragmentado',
                                    default => 'badge-regular'
                                };
                                $coords = '-';
                                if (!empty($pieza['latitud']) && !empty($pieza['longitud'])) {
                                    $coords = htmlspecialchars($pieza['latitud']) . ', ' . htmlspecialchars($pieza['longitud']);
                                }
                            ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars((string)$pieza['id']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($pieza['nombre_tipo_objeto']) ?></strong>
                                    <?php if (!empty($pieza['descripcion'])): ?>
                                        <div class="form-help" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($pieza['descripcion']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($pieza['sitio_hallazgo']) ?></td>
                                <td><code><?= $coords ?></code></td>
                                <td><?= htmlspecialchars($pieza['fecha_hallazgo']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($estado) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="editar.php?id=<?= urlencode((string)$pieza['id']) ?>" class="btn btn-secondary btn-sm">
                                        Editar
                                    </a>
                                    <a href="eliminar.php?id=<?= urlencode((string)$pieza['id']) ?>" class="btn btn-danger btn-sm">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
