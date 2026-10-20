<?php
declare(strict_types=1);
/** @var string $tituloPagina */
/** @var string $rutaBase */
$tituloPagina = $tituloPagina ?? 'EcoConexión';
$rutaBase = $rutaBase ?? '';
$cssHref = $rutaBase . 'css/estilos.css';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EcoConexión conecta residuos reciclables con quien los transforma en Bogotá.">
    <title><?= e($tituloPagina) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e($cssHref) ?>">
</head>
<body>
<header class="site-header">
    <div class="nav-inner">
        <a class="brand" href="<?= e($rutaBase) ?>index.php">
            <span class="brand-mark" aria-hidden="true"></span>
            <span class="brand-text">
                <strong>EcoConexión</strong>
                <small>Residuos → Recurso</small>
            </span>
        </a>
        <button type="button" class="nav-toggle" aria-label="Abrir menú" aria-expanded="false" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-nav>
            <a href="<?= e($rutaBase) ?>tablero.php">Tablero</a>
            <?php if (estaAutenticado()): ?>
                <a href="<?= e($rutaBase) ?>publicar.php">Publicar</a>
                <a href="<?= e($rutaBase) ?>mis_publicaciones.php">Mis publicaciones</a>
                <a href="<?= e($rutaBase) ?>auth/logout.php" class="nav-muted">Salir (<?= e($_SESSION['usuario_nombre']) ?>)</a>
            <?php else: ?>
                <a href="<?= e($rutaBase) ?>auth/login.php">Ingresar</a>
                <a href="<?= e($rutaBase) ?>auth/registro.php" class="nav-cta">Registrarse</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (!btn || !nav) return;
    btn.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.classList.toggle('is-open', open);
    });
});
</script>
<main class="site-main">