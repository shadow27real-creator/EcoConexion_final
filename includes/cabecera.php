<?php
declare(strict_types=1);

/** @var string $tituloPagina */
$tituloPagina = $tituloPagina ?? 'EcoConexión';

if (!function_exists('e')) {
    require_once __DIR__ . '/../funciones/funciones.php';
}

// Detectar si estamos en subcarpeta para ajustar la ruta base de recursos
$esSubcarpeta = (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/auth/') !== false);
$rutaBase = $esSubcarpeta ? '../' : '';

// Normalizar el rol para evitar fallos por mayúsculas o espacios
$tipoUsuario = strtolower(trim($_SESSION['tipo_usuario'] ?? ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>

    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Hoja de estilos con control de caché dinámico -->
    <link rel="stylesheet" href="<?= $rutaBase ?>css/estilos.css?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
    <div class="nav-inner">
        <a class="brand" href="<?= $rutaBase ?>index.php" style="display: flex; align-items: center; gap: 0.75rem;">
            <img src="<?= $rutaBase ?>img/logo_pagina.png" alt="EcoConexión Logo" style="height: 42px; width: auto; object-fit: contain;">
            <span class="brand-text">
                <strong>EcoConexión</strong>
                <small>Residuos → Recurso</small>
            </span>
        </a>
        <button type="button" class="nav-toggle" aria-label="Abrir menú" aria-expanded="false" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-nav>
            <!-- Tablero visible para todos los roles -->
            <a href="<?= $rutaBase ?>tablero.php">Tablero</a>

            <?php if (estaAutenticado()): ?>
                <!-- Pestañas para Generador y Administrador -->
                <?php if ($tipoUsuario === 'generador' || $tipoUsuario === 'administrador'): ?>
                    <a href="<?= $rutaBase ?>publicar.php">Publicar</a>
                    <a href="<?= $rutaBase ?>mis_publicaciones.php">Mis publicaciones</a>
                <?php endif; ?>

                <!-- Pestaña exclusiva para Administrador -->
                <?php if ($tipoUsuario === 'administrador'): ?>
                    <a href="<?= $rutaBase ?>admin.php" style="color: var(--kraft); font-weight: 700;">Panel Admin</a>
                <?php endif; ?>

                <a href="<?= $rutaBase ?>auth/logout.php" class="nav-muted">Salir (<?= e($_SESSION['usuario_nombre'] ?? 'Usuario') ?>)</a>
            <?php else: ?>
                <a href="<?= $rutaBase ?>auth/login.php">Ingresar</a>
                <a href="<?= $rutaBase ?>auth/registro.php" class="nav-cta">Registrarse</a>
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