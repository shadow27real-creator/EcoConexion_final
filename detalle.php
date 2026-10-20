<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

$id_pub = (int)($_GET['id'] ?? 0);

$stmt = $mysqli->prepare(
    'SELECT p.id_publicacion, p.titulo, p.cantidad, p.direccion, p.descripcion, p.estado, p.fecha_publicacion,
            m.nombre AS material, u.nombre AS generador, u.telefono
     FROM publicaciones p
     JOIN materiales m ON p.id_material = m.id_material
     JOIN usuarios u ON p.id_usuario = u.id_usuario
     WHERE p.id_publicacion = ?'
);
$stmt->bind_param('i', $id_pub);
$stmt->execute();
$pub = $stmt->get_result()->fetch_assoc();

if (!$pub) {
    http_response_code(404);
    exit('Publicación no encontrada.');
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

$foto = $fotosMaterial[$pub['material']] ?? 'img/carton.jpg';
$badge = $claseBadge[$pub['material']] ?? '';
$telLimpio = limpiarTelefono($pub['telefono']);
$mensajeWs = urlencode(
    "Hola {$pub['generador']}, vi tu publicación «{$pub['titulo']}» en EcoConexión y quiero coordinar la recogida del material ({$pub['material']}, {$pub['cantidad']}). ¿Cuándo te queda bien?"
);
$urlWhatsapp = 'https://api.whatsapp.com/send?phone=' . $telLimpio . '&text=' . $mensajeWs;
$fecha = date('d/m/Y H:i', strtotime($pub['fecha_publicacion']));

$tituloPagina = $pub['titulo'] . ' — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor">
    <p style="margin-bottom:1rem;"><a href="tablero.php" style="font-weight:600;">← Volver al tablero</a></p>

    <div class="detalle-layout anim-rise">
        <figure class="detalle-foto">
            <img src="<?= e($foto) ?>" alt="<?= e($pub['material']) ?> — <?= e($pub['titulo']) ?>">
        </figure>

        <div class="detalle-panel">
            <span class="badge <?= e($badge) ?>"><?= e($pub['material']) ?></span>
            <?php if ($pub['estado'] === 'disponible'): ?>
                <span class="badge badge-estado">Disponible</span>
            <?php else: ?>
                <span class="badge badge-recogido">Recogido</span>
            <?php endif; ?>

            <h2><?= e($pub['titulo']) ?></h2>

            <ul class="detalle-lista">
                <li><strong>Publicado por</strong> <?= e($pub['generador']) ?></li>
                <li><strong>Cantidad</strong> <?= e($pub['cantidad']) ?></li>
                <li><strong>Dirección de recogida</strong> <?= e($pub['direccion']) ?></li>
                <li><strong>Fecha</strong> <?= e($fecha) ?></li>
                <li>
                    <strong>Detalles</strong>
                    <?= !empty($pub['descripcion']) ? nl2br(e($pub['descripcion'])) : 'Sin especificaciones adicionales.' ?>
                </li>
            </ul>

            <?php if ($pub['estado'] === 'disponible'): ?>
                <div class="ws-box">
                    <p>Abre WhatsApp con un mensaje listo para coordinar la recogida con <?= e($pub['generador']) ?>.</p>
                    <a href="<?= e($urlWhatsapp) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ws btn-block btn-lg">
                        Contactar por WhatsApp
                    </a>
                </div>
            <?php else: ?>
                <div class="alerta alerta-error" style="text-align:center;margin-top:1rem;">
                    Este material ya fue marcado como recogido.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
