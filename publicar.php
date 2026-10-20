<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

if (!estaAutenticado()) {
    header('Location: auth/login.php');
    exit();
}

$errores = [];
$valores = [
    'titulo' => '',
    'id_material' => 0,
    'cantidad' => '',
    'direccion' => '',
    'descripcion' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['titulo'] = trim($_POST['titulo'] ?? '');
    $valores['id_material'] = (int)($_POST['id_material'] ?? 0);
    $valores['cantidad'] = trim($_POST['cantidad'] ?? '');
    $valores['direccion'] = trim($_POST['direccion'] ?? '');
    $valores['descripcion'] = trim($_POST['descripcion'] ?? '');

    if ($valores['titulo'] === '') {
        $errores[] = 'El título de la publicación es obligatorio.';
    }
    if ($valores['id_material'] <= 0) {
        $errores[] = 'Seleccione una categoría de material.';
    }
    if ($valores['cantidad'] === '') {
        $errores[] = 'Especifique la cantidad disponible.';
    }
    if ($valores['direccion'] === '') {
        $errores[] = 'La dirección escrita es requerida para el retiro.';
    }

    if (count($errores) === 0) {
        $stmt = $mysqli->prepare(
            'INSERT INTO publicaciones (titulo, cantidad, direccion, descripcion, id_material, id_usuario)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssii',
            $valores['titulo'],
            $valores['cantidad'],
            $valores['direccion'],
            $valores['descripcion'],
            $valores['id_material'],
            $_SESSION['usuario_id']
        );
        $stmt->execute();

        header('Location: mis_publicaciones.php?ok=1');
        exit();
    }
}

$categorias = $mysqli->query('SELECT id_material, nombre FROM materiales ORDER BY nombre ASC')->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Publicar material — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor" style="max-width:640px;">
    <div class="page-head anim-rise">
        <span class="sello">Nueva oferta</span>
        <h2>Publicar material</h2>
        <p>Describe qué tienes. Los recicladores lo verán en el tablero y te contactarán por WhatsApp.</p>
    </div>

    <div class="card anim-rise-2">
        <?php foreach ($errores as $err): ?>
            <div class="alerta alerta-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form action="publicar.php" method="POST">
            <div class="campo">
                <label for="titulo">Título informativo</label>
                <input type="text" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>"
                       placeholder="Ej: Cartón corrugado y cajas varias" required maxlength="150">
            </div>
            <div class="campo">
                <label for="id_material">Categoría de material</label>
                <select id="id_material" name="id_material" required>
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id_material'] ?>"
                            <?= $valores['id_material'] === (int)$cat['id_material'] ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="cantidad">Cantidad estimada</label>
                <input type="text" id="cantidad" name="cantidad" value="<?= e($valores['cantidad']) ?>"
                       placeholder="Ej: 15 kg / 3 bolsas grandes" required maxlength="80">
            </div>
            <div class="campo">
                <label for="direccion">Dirección de recogida</label>
                <input type="text" id="direccion" name="direccion" value="<?= e($valores['direccion']) ?>"
                       placeholder="Ej: Calle 10 # 5-20, Barrio Centro, Bogotá" required maxlength="255">
                <p class="form-hint">Incluye barrio o zona para que el reciclador estime el recorrido.</p>
            </div>

            <!-- Área amarilla de referencia / mapa de ubicación -->
            <div class="auth-demo" style="margin-top: 1rem; border-color: var(--kraft);">
                <strong>🗺️ Referencia de Zona (Bogotá)</strong>
                <p style="margin-top: 0.3rem; font-size: 0.88rem;">
                    Asegúrate de indicar puntos de referencia claros. Esto simula el mapa de ubicación para guiar al reciclador de manera óptima en la zona.
                </p>
            </div>

            <div class="campo" style="margin-top: 1.15rem;">
                <label for="descripcion">Descripción adicional (opcional)</label>
                <textarea id="descripcion" name="descripcion" rows="3"
                          placeholder="Empaque, horario, instrucciones de portería…"><?= e($valores['descripcion']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-block btn-lg">Publicar oferta</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>