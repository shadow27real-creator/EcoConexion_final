<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

$materiales = $mysqli->query(
    'SELECT m.id_material, m.nombre, COUNT(p.id_publicacion) AS disponibles
     FROM materiales m
     LEFT JOIN publicaciones p ON p.id_material = m.id_material AND p.estado = "disponible"
     GROUP BY m.id_material, m.nombre
     ORDER BY m.nombre ASC'
)->fetch_all(MYSQLI_ASSOC);

$colores = [
    'Cartón'   => 'carton',
    'Plástico' => 'plastico',
    'Vidrio'   => 'vidrio',
    'Metal'    => 'metal',
    'Papel'    => 'papel',
];

$tituloPagina = 'EcoConexión — Residuos que se transforman';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="hero">
    <div class="hero-texto anim-rise">
        <span class="sello">Bogotá · Reciclaje comunitario</span>
        <h1 class="hero-brand">EcoConexión</h1>
        <p class="hero-lead">Conectamos residuos con quien los transforma. Publica cartón, plástico o vidrio; un reciclador de tu zona te escribe por WhatsApp y lo recoge.</p>
        <div class="hero-acciones">
            <a href="tablero.php" class="btn btn-lg">Ver material disponible</a>
            <a href="<?= estaAutenticado() ? 'publicar.php' : 'auth/registro.php' ?>" class="btn btn-linea btn-lg">Publicar mi material</a>
        </div>
    </div>
    <figure class="hero-foto anim-rise-2">
        <span class="hero-cinta" aria-hidden="true"></span>
        <img src="img/reciclaje-hero.jpg" alt="Punto de acopio comunitario con canecas de reciclaje">
        <figcaption>Lo que para unos es basura, para otros es materia prima.</figcaption>
    </figure>
</section>

<section class="bloque">
    <div class="bloque-titulo anim-rise">
        <h2>¿Qué material buscas?</h2>
        <p>Elige una etiqueta y abre el tablero filtrado por tipo.</p>
    </div>
    <div class="materiales">
        <?php foreach ($materiales as $mat): ?>
            <a href="tablero.php?material=<?= (int)$mat['id_material'] ?>" class="etiqueta mat-<?= e($colores[$mat['nombre']] ?? 'carton') ?>">
                <span class="etiqueta-nombre"><?= e($mat['nombre']) ?></span>
                <span class="etiqueta-num"><?= (int)$mat['disponibles'] ?></span>
                <span class="etiqueta-pie"><?= (int)$mat['disponibles'] === 1 ? 'publicación' : 'publicaciones' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="bloque">
    <div class="bloque-titulo">
        <h2>Material en la calle</h2>
        <p>Fotos reales del flujo: acopio, canecas y residuos listos para recoger.</p>
    </div>
    <div class="galeria">
        <figure><img src="img/reciclaje-carton.jpg" alt="Cartón apilado para reciclaje"><figcaption>Cartón</figcaption></figure>
        <figure><img src="img/reciclaje-plastico.jpg" alt="Botellas plásticas PET"><figcaption>Plástico</figcaption></figure>
        <figure><img src="img/reciclaje-vidrio.jpg" alt="Botellas de vidrio clasificadas"><figcaption>Vidrio</figcaption></figure>
        <figure><img src="img/reciclaje-metal.jpg" alt="Latas y metal para reciclar"><figcaption>Metal</figcaption></figure>
    </div>
</section>

<section class="bloque pasos">
    <div class="bloque-titulo">
        <h2>Así funciona</h2>
    </div>
    <ol>
        <li>
            <span class="paso-num">1</span>
            <h3>Publicas</h3>
            <p>Dices qué material tienes, cuánto y en qué dirección se puede recoger.</p>
        </li>
        <li>
            <span class="paso-num">2</span>
            <h3>Te encuentran</h3>
            <p>Los recicladores filtran el tablero por tipo de material y ven tu publicación.</p>
        </li>
        <li>
            <span class="paso-num">3</span>
            <h3>Se coordinan</h3>
            <p>Te escriben por WhatsApp, recogen el material y lo marcas como recogido.</p>
        </li>
    </ol>
</section>

<section class="bloque roles">
    <article class="rol">
        <img src="img/canecas.jpg" alt="Canecas de reciclaje de colores">
        <div class="rol-body">
            <span class="sello">Generador</span>
            <h3>Tienes material y no sabes a quién dárselo</h3>
            <p>Hogares, estudiantes y pequeños comercios. Publica en un minuto y deja de botar lo que se puede aprovechar.</p>
        </div>
    </article>
    <article class="rol">
        <img src="img/acopio.jpg" alt="Centro de acopio de material reciclable">
        <div class="rol-body">
            <span class="sello">Reciclador</span>
            <h3>Buscas material para recoger</h3>
            <p>Consulta qué hay disponible por tipo de material y contacta directamente a quien lo publicó.</p>
        </div>
    </article>
</section>

<?php require __DIR__ . '/includes/pie.php'; ?>
