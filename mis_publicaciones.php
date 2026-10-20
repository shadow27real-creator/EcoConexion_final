<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

if (!estaAutenticado()) {
    header('Location: auth/login.php');
    exit();
}

// Limpieza automática: Eliminar publicaciones del usuario actual marcadas como 'recogido' hace más de 6 horas
$stmtLimpieza = $mysqli->prepare(
    'DELETE FROM publicaciones 
     WHERE id_usuario = ? 
       AND estado = "recogido" 
       AND fecha_publicacion <= (NOW() - INTERVAL 6 HOUR)'
);
$stmtLimpieza->bind_param('i', $_SESSION['usuario_id']);
$stmtLimpieza->execute();

// Marcar como recogido manualmente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_recogido'])) {
    $id_pub = (int)($_POST['id_publicacion'] ?? 0);
    $stmt = $mysqli->prepare(
        'UPDATE publicaciones SET estado = "recogido" WHERE id_publicacion = ? AND id_usuario = ?'
    );
    $stmt->bind_param('ii', $id_pub, $_SESSION['usuario_id']);
    $stmt->execute();
    header('Location: mis_publicaciones.php?recogido=1');
    exit();
}

$stmt = $mysqli->prepare(
    'SELECT p.id_publicacion, p.titulo, p.cantidad, p.direccion, p.estado, p.fecha_publicacion, m.nombre AS material
     FROM publicaciones p
     JOIN materiales m ON p.id_material = m.id_material
     WHERE p.id_usuario = ?
     ORDER BY p.fecha_publicacion DESC'
);
$stmt->bind_param('i', $_SESSION['usuario_id']);
$stmt->execute();
$misPubs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

$tituloPagina = 'Mis publicaciones — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor">
    <div class="page-head anim-rise">
        <span class="sello">Tu panel</span>
        <h2>Mis publicaciones</h2>
        <p>Gestiona lo que ofreciste. Las ofertas marcadas como recogidas se eliminan automáticamente del sistema transcurridas 6 horas.</p>
    </div>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alerta alerta-exito anim-rise">Publicación creada. Ya aparece en el tablero.</div>
    <?php endif; ?>
    <?php if (isset($_GET['recogido'])): ?>
        <div class="alerta alerta-exito anim-rise">Material marcado como recogido. Se depurará automáticamente en 6 horas.</div>
    <?php endif; ?>

    <p style="margin-bottom:1.25rem;">
        <a href="publicar.php" class="btn">+ Publicar nuevo</a>
    </p>

    <?php if (count($misPubs) > 0): ?>
        <div class="grid-publicaciones anim-rise-2">
            <?php foreach ($misPubs as $pub): ?>
                <?php
                $foto = $fotosMaterial[$pub['material']] ?? 'img/carton.jpg';
                $badge = $claseBadge[$pub['material']] ?? '';
                $recogido = $pub['estado'] === 'recogido';
                ?>
                <article class="pub-card <?= $recogido ? 'recogido' : '' ?>">
                    <div>
                        <div class="pub-foto">
                            <img src="<?= e($foto) ?>" alt="<?= e($pub['material']) ?>">
                        </div>
                        <div class="pub-meta">
                            <span class="badge <?= e($badge) ?>"><?= e($pub['material']) ?></span>
                            <span class="badge <?= $recogido ? 'badge-recogido' : 'badge-estado' ?>">
                                <?= e(strtoupper($pub['estado'])) ?>
                            </span>
                        </div>
                        <h3 style="color:var(--tinta);"><?= e($pub['titulo']) ?></h3>
                        <p><strong>Cantidad:</strong> <?= e($pub['cantidad']) ?></p>
                        <p><strong>Dirección:</strong> <?= e($pub['direccion']) ?></p>
                    </div>
                    <div>
                        <?php if (!$recogido): ?>
                            <form action="mis_publicaciones.php" method="POST">
                                <input type="hidden" name="id_publicacion" value="<?= (int)$pub['id_publicacion'] ?>">
                                <button type="submit" name="marcar_recogido" class="btn btn-secundario btn-block">
                                    Marcar como recogido
                                </button>
                            </form>
                            <p style="margin-top:0.6rem;text-align:center;">
                                <a href="detalle.php?id=<?= (int)$pub['id_publicacion'] ?>">Ver ficha pública</a>
                            </p>
                        <?php else: ?>
                            <p style="color:var(--muted);font-size:0.9rem;text-align:center;">Material entregado (En cuenta regresiva de 6h para auto-eliminación)</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card empty-state">
            <p>Aún no has creado publicaciones de material.</p>
            <a href="publicar.php" class="btn">Publicar ahora</a>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>