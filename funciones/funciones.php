<?php
declare(strict_types=1);

// Helper para prevenir ataques XSS al imprimir en HTML
function e(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Verificar si el usuario ha iniciado sesión
function estaAutenticado(): bool {
    return isset($_SESSION['usuario_id']);
}

// Normalizar número telefónico para enlace directo de WhatsApp
function limpiarTelefono(string $telefono): string {
    $limpio = preg_replace('/[^\d]/', '', $telefono);
    if (strlen($limpio) === 10 && strpos($limpio, '57') !== 0) {
        $limpio = '57' . $limpio;
    }
    return $limpio;
}