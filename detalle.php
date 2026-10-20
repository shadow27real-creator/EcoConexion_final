<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

if (!estaAutenticado()) {
    header('Location: auth/login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: tablero.php');
    exit();
}

$stmt = $mysqli->prepare(
    'SELECT p.id_publicacion, p.titulo, p.cantidad, p.direccion, p.descripcion, p.estado, p.fecha_publicacion,
            u.nombre AS generador, u.telefono, m.nombre AS material
     FROM publicaciones p
     JOIN usuarios u ON p.id_usuario = u.id_usuario
     JOIN materiales m ON p.id_material = m.id_material
     WHERE p.id_publicacion = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$publicacion = $stmt->get_result()->fetch_assoc();

if (!$publicacion) {
    header('Location: tablero.php');
    exit();
}

$fotosMaterial = [
    'Cartón'   => 'img/reciclaje-carton.jpg',
    'Plástico' => 'img/reciclaje-plastico.jpg',
    'Vidrio'   => 'img/reciclaje-vidrio.jpg',
    'Metal'    => 'img/reciclaje-metal.jpg',
    'Papel'    => 'img/reciclaje-papel.jpg',
];
$foto = $fotosMaterial[$publicacion['material']] ?? 'img/carton.jpg';

$telefonoLimpio = preg_replace('/[^0-9]/', '', $publicacion['telefono'] ?? '');
if (strlen($telefonoLimpio) === 10) {
    $telefonoLimpio = '57' . $telefonoLimpio;
}

$mensajeWa = 'Hola ' . $publicacion['generador'] . ', vi tu publicación en EcoConexión sobre "' . $publicacion['titulo'] . '" (' . $publicacion['cantidad'] . ') y estoy interesado en coordinar la recogida en ' . $publicacion['direccion'] . '.';
$urlWhatsapp = 'https://wa.me/' . $telefonoLimpio . '?text=' . rawurlencode($mensajeWa);

// --- LIMPIEZA Y NORMALIZACIÓN ROBUSTA DE DIRECCIONES ---
// Maneja abreviaturas (cl, cll, cra, kr, av, etc.), espacios y símbolos irregulares
$direccionLimpia = trim($publicacion['direccion']);

$abreviaturas = [
    '/\bcll\.?\b/i' => 'Calle',
    '/\bcl\.?\b/i'  => 'Calle',
    '/\bcra\.?\b/i' => 'Carrera',
    '/\bkr\.?\b/i'  => 'Carrera',
    '/\bkr\b/i'     => 'Carrera',
    '/\bav\.?\b/i'  => 'Avenida',
    '/\bdg\.?\b/i'  => 'Diagonal',
    '/\btv\.?\b/i'  => 'Transversal',
    '/\btr\.?\b/i'  => 'Transversal',
    '/\bcl\b/i'     => 'Calle'
];

foreach ($abreviaturas as $patron => $reemplazo) {
    $direccionLimpia = preg_replace($patron, $reemplazo, $direccionLimpia);
}

// Asegurar espaciado correcto en numerales y guiones
$direccionLimpia = preg_replace('/([a-zA-Z0-9])#([a-zA-Z0-9])/', '$1 # $2', $direccionLimpia);
$direccionLimpia = preg_replace('/([a-zA-Z0-9])-([a-zA-Z0-9])/', '$1 - $2', $direccionLimpia);

$direccionBusqueda = $direccionLimpia . ', Bogota, Colombia';
$dirMapa = urlencode($direccionBusqueda);

$tituloPagina = $publicacion['titulo'] . ' — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor" style="max-width: 900px;">
    <p style="margin-bottom: 1.5rem;">
        <a href="tablero.php" style="color: var(--botella); font-weight: 600;">← Volver al tablero</a>
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; align-items: start;">
        <div>
            <div class="card" style="padding: 0; overflow: hidden; border-radius: 8px;">
                <img src="<?= e($foto) ?>" alt="<?= e($publicacion['material']) ?>" style="width: 100%; height: auto; display: block;">
            </div>

            <!-- Mapa Interactivo de Google Maps con Zoom Cercano (z=16) y Limpieza de Direcciones -->
            <div style="margin-top: 1.5rem; background: #fffbe6; border: 2px dashed var(--kraft); padding: 1rem; border-radius: 8px;">
                <strong style="color: var(--tinta); display: block; margin-bottom: 0.5rem;">🗺️ Ubicación en Google Maps</strong>
                <div style="width: 100%; height: 220px; border-radius: 6px; overflow: hidden; border: 1px solid var(--linea);">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        loading="lazy" 
                        allowfullscreen 
                        src="https://maps.google.com/maps?q=<?= $dirMapa ?>&t=&z=16&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.82rem; color: var(--muted); margin-bottom: 0;">
                    Punto de entrega: <strong><?= e($publicacion['direccion']) ?></strong>
                </p>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <span class="badge badge-carton"><?= e($publicacion['material']) ?></span>
                <span class="badge" style="background: var(--paja); color: var(--tinta);"><?= e(strtoupper($publicacion['estado'])) ?></span>
            </div>

            <h2 style="font-size: 1.8rem; margin-bottom: 1rem; color: var(--botella);"><?= e($publicacion['titulo']) ?></h2>

            <div style="margin-bottom: 1rem; border-bottom: 1px dashed var(--linea); padding-bottom: 0.8rem;">
                <small style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700;">Publicado por</small>
                <p style="margin: 0.2rem 0 0; font-weight: 600;"><?= e($publicacion['generador']) ?></p>
            </div>

            <div style="margin-bottom: 1rem; border-bottom: 1px dashed var(--linea); padding-bottom: 0.8rem;">
                <small style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700;">Cantidad</small>
                <p style="margin: 0.2rem 0 0; font-weight: 600;"><?= e($publicacion['cantidad']) ?></p>
            </div>

            <div style="margin-bottom: 1rem; border-bottom: 1px dashed var(--linea); padding-bottom: 0.8rem;">
                <small style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700;">Dirección de recogida</small>
                <p style="margin: 0.2rem 0 0; font-weight: 600;"><?= e($publicacion['direccion']) ?></p>
            </div>

            <div style="margin-bottom: 1rem; border-bottom: 1px dashed var(--linea); padding-bottom: 0.8rem;">
                <small style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700;">Fecha</small>
                <p style="margin: 0.2rem 0 0; font-weight: 600;"><?= date('d/m/Y H:i', strtotime($publicacion['fecha_publicacion'])) ?></p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <small style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); font-weight: 700;">Detalles</small>
                <p style="margin: 0.2rem 0 0;"><?= !empty($publicacion['descripcion']) ? e($publicacion['descripcion']) : 'Sin especificaciones adicionales.' ?></p>
            </div>

            <?php if ($publicacion['estado'] === 'disponible'): ?>
                <div style="background: #e4f2e8; border: 1px dashed var(--ok); padding: 1rem; border-radius: 6px; text-align: center;">
                    <p style="font-size: 0.85rem; margin-bottom: 0.8rem; color: var(--botella);">Abre WhatsApp con un mensaje listo para coordinar la recogida con <?= e($publicacion['generador']) ?>.</p>
                    <a href="<?= e($urlWhatsapp) ?>" target="_blank" class="btn btn-block" style="background: #25d366; border-color: #25d366;">Contactar por WhatsApp</a>
                </div>
            <?php else: ?>
                <div class="alerta alerta-error">Este material ya fue recogido o no se encuentra disponible.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>