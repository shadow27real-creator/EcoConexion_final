<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../funciones/funciones.php';

if (estaAutenticado()) {
    header('Location: ../tablero.php');
    exit();
}

$errores = [];
$valores = [
    'nombre' => '',
    'email' => '',
    'telefono' => '',
    'tipo_usuario' => 'reciclador',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['nombre'] = trim($_POST['nombre'] ?? '');
    $valores['email'] = trim($_POST['email'] ?? '');
    $valores['telefono'] = trim($_POST['telefono'] ?? '');
    $valores['tipo_usuario'] = trim($_POST['tipo_usuario'] ?? 'reciclador');
    $contrasena = $_POST['contrasena'] ?? '';
    $contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

    // Validaciones de formulario
    if ($valores['nombre'] === '') {
        $errores[] = 'El nombre completo es obligatorio.';
    }

    // VALIDACIÓN ESTRICTA DEL CORREO CON ARROBA (@)
    if ($valores['email'] === '') {
        $errores[] = 'El correo electrónico es obligatorio.';
    } elseif (strpos($valores['email'], '@') === false || !filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico debe incluir el símbolo "@" y tener un formato válido (ejemplo: usuario@dominio.com).';
    }

    if ($valores['telefono'] === '') {
        $errores[] = 'El número de teléfono es obligatorio.';
    }

    if (!in_array($valores['tipo_usuario'], ['generador', 'reciclador'], true)) {
        $valores['tipo_usuario'] = 'reciclador';
    }

    if (strlen($contrasena) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($contrasena !== $contrasena_confirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // Comprobar si el correo ya está registrado
    if (count($errores) === 0) {
        $stmt = $mysqli->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $valores['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = 'El correo electrónico ya se encuentra registrado.';
        }
    }

    // Guardar en la base de datos
    if (count($errores) === 0) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare(
            'INSERT INTO usuarios (nombre, email, contrasena_hash, telefono, tipo_usuario) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sssss',
            $valores['nombre'],
            $valores['email'],
            $hash,
            $valores['telefono'],
            $valores['tipo_usuario']
        );
        $stmt->execute();

        // Autologin tras el registro
        $_SESSION['usuario_id'] = (int)$stmt->insert_id;
        $_SESSION['usuario_nombre'] = $valores['nombre'];
        $_SESSION['usuario_email'] = $valores['email'];
        $_SESSION['tipo_usuario'] = strtolower($valores['tipo_usuario']);

        header('Location: ../tablero.php');
        exit();
    }
}

$tituloPagina = 'Registro — EcoConexión';
$rutaBase = '../';
require __DIR__ . '/../includes/cabecera.php';
?>

<div class="contenedor" style="max-width: 480px;">
    <div class="page-head anim-rise" style="text-align: center;">
        <span class="sello">Unirse</span>
        <h2>Crear Cuenta</h2>
    </div>

    <div class="card anim-rise-2">
        <?php foreach ($errores as $err): ?>
            <div class="alerta alerta-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form action="registro.php" method="POST">
            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($valores['nombre']) ?>" placeholder="Ej: Camilo Galindo" required>
            </div>

            <div class="campo">
                <label for="email">Correo electrónico (requiere @)</label>
                <input type="email" id="email" name="email" value="<?= e($valores['email']) ?>" placeholder="ejemplo@correo.com" required>
            </div>

            <div class="campo">
                <label for="telefono">Teléfono / WhatsApp</label>
                <input type="text" id="telefono" name="telefono" value="<?= e($valores['telefono']) ?>" placeholder="Ej: 3001234567" required>
            </div>

            <div class="campo">
                <label for="tipo_usuario">Tipo de Perfil</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="reciclador" <?= $valores['tipo_usuario'] === 'reciclador' ? 'selected' : '' ?>>Reciclador (Busco material)</option>
                    <option value="generador" <?= $valores['tipo_usuario'] === 'generador' ? 'selected' : '' ?>>Generador (Publico residuos)</option>
                </select>
            </div>

            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>

            <div class="campo">
                <label for="contrasena_confirm">Confirmar contraseña</label>
                <input type="password" id="contrasena_confirm" name="contrasena_confirm" placeholder="Repite tu contraseña" required minlength="6">
            </div>

            <button type="submit" class="btn btn-block btn-lg" style="margin-top: 1rem;">Registrarme</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--botella); font-weight: 700;">Ingresa aquí</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>