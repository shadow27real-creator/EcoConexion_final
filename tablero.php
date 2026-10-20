<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

if (!estaAutenticado()) {
    header('Location: auth/login.php');
    exit();
}

$filtroMat = (int)($_GET['material'] ?? 0);

$categorias = $mysqli->query('SELECT id_material, nombre FROM materiales ORDER BY nombre ASC')->fetch_all(MYSQLI_ASSOC);

$sql = 'SELECT p.id_publicacion, p.titulo, p.cantidad, p.direccion, p.estado, p.fecha_publicacion, m.nombre AS material
        FROM publicaciones p
        JOIN materiales m ON p.id_material = m.id_material
        WHERE p.estado = "disponible"';

if ($filtroMat > 0) {
    $sql .= ' AND p.id_material = ?';
}
$sql .= ' ORDER BY p.fecha_publicacion DESC';

if ($filtroMat > 0) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $filtroMat);
    $stmt->execute();
    $publicaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $publicaciones = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$fotosMaterial = [
    'Cartón'   => 'img/reciclaje-carton.jpg',
    'Plástico' => 'img/reciclaje-plastico.jpg',
    'Vidrio'   => 'img/reciclaje-vidrio.jpg',
    'Metal'    => 'img/reciclaje-metal.jpg',
    'Papel'    => 'img/reciclaje-papel.jpg',
];
$claseBadge = [
    'Cartón'   => 'badge-carton',
    'Plástico' => 'badge-plastico',
    'Vidrio'   => 'badge-vidrio',
    'Metal'    => 'badge-metal',
    'Papel'    => 'badge-papel',
];

$tituloPagina = 'Tablero — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor">
    <div class="page-head anim-rise">
        <span class="sello">Tablero vivo</span>
        <h2>Materiales disponibles</h2>
        <p>Filtra por tipo y abre el detalle para contactar por WhatsApp al generador.</p>
    </div>

    <div class="filtros-bar anim-rise-2">
        <a href="tablero.php" class="<?= $filtroMat === 0 ? 'activo' : '' ?>">Todos</a>
        <?php foreach ($categorias as $cat): ?>
            <a href="tablero.php?material=<?= (int)$cat['id_material'] ?>"
               class="<?= $filtroMat === (int)$cat['id_material'] ? 'activo' : '' ?>">
                <?= e($cat['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($publicaciones) > 0): ?>
        <div class="grid-publicaciones anim-rise-3">
            <?php foreach ($publicaciones as $pub): ?>
                <?php
                $foto = $fotosMaterial[$pub['material']] ?? 'img/carton.jpg';
                $badge = $claseBadge[$pub['material']] ?? '';
                
                // Lógica del Temporizador de 6 horas
                $tiempoPub = strtotime($pub['fecha_publicacion']);
                $diferenciaHoras = (time() - $tiempoPub) / 3600;
                $esReciente = ($diferenciaHoras <= 6);
                ?>
                <article class="pub-card">
                    <div>
                        <div class="pub-foto">
                            <img src="<?= e($foto) ?>" alt="<?= e($pub['material']) ?> disponible">
                        </div>
                        <div class="pub-meta">
                            <span class="badge <?= e($badge) ?>"><?= e($pub['material']) ?></span>
                            <?php if ($esReciente): ?>
                                <span class="badge" style="background: #e4f2e8; color: var(--ok); border-color: var(--ok);">⏱️ < 6 horas</span>
                            <?php endif; ?>
                        </div>
                        <h3><?= e($pub['titulo']) ?></h3>
                        <p><strong>Cantidad:</strong> <?= e($pub['cantidad']) ?></p>
                        <p><strong>Zona:</strong> <?= e($pub['direccion']) ?></p>
                    </div>
                    <a href="detalle.php?id=<?= (int)$pub['id_publicacion'] ?>" class="btn btn-block">Ver detalle y contactar</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card empty-state">
            <p>No hay publicaciones disponibles en esta categoría ahora mismo.</p>
            <?php if (estaAutenticado()): ?>
                <a href="publicar.php" class="btn">Publicar material</a>
            <?php else: ?>
                <a href="auth/registro.php" class="btn">Crear cuenta y publicar</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>