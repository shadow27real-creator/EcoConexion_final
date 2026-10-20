<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../funciones/funciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_usuario = trim($_POST['tipo_usuario'] ?? 'generador');

    if (!in_array($tipo_usuario, ['generador', 'reciclador'], true)) {
        $tipo_usuario = 'generador';
    }
    if (mb_strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    }
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($telefono === '') {
        $errores[] = 'El teléfono de contacto (WhatsApp) es obligatorio.';
    }

    if (count($errores) === 0) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $mysqli->prepare(
                'INSERT INTO usuarios (nombre, email, contrasena_hash, telefono, tipo_usuario) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('sssss', $nombre, $email, $hash, $telefono, $tipo_usuario);
            $stmt->execute();
            $_SESSION['mensaje_exito'] = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            header('Location: login.php');
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $errores[] = 'Este correo electrónico ya se encuentra registrado.';
            } else {
                $errores[] = 'Error al registrar el usuario en la base de datos.';
            }
        }
    }
}

$tituloPagina = 'Registro — EcoConexión';
$rutaBase = '../';
require __DIR__ . '/../includes/cabecera.php';
?>

<div class="auth-wrap anim-rise" style="max-width:520px;">
    <div class="page-head">
        <span class="sello">Únete</span>
        <h2>Crear cuenta</h2>
        <p>Genera o recoge material. El teléfono se usa para el enlace de WhatsApp.</p>
    </div>

    <div class="card">
        <?php foreach ($errores as $err): ?>
            <div class="alerta alerta-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form action="registro.php" method="POST">
            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="<?= e($_POST['nombre'] ?? '') ?>" required>
            </div>
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="campo">
                <label for="telefono">Teléfono (WhatsApp)</label>
                <input type="text" id="telefono" name="telefono" value="<?= e($_POST['telefono'] ?? '') ?>"
                       placeholder="Ej: 3001112233" required>
                <p class="form-hint">Solo dígitos colombianos; se antepone 57 automáticamente.</p>
            </div>
            <div class="campo">
                <label for="tipo_usuario">Tipo de usuario</label>
                <select id="tipo_usuario" name="tipo_usuario">
                    <option value="generador" <?= (($_POST['tipo_usuario'] ?? '') === 'generador') ? 'selected' : '' ?>>
                        Generador — tengo material para entregar
                    </option>
                    <option value="reciclador" <?= (($_POST['tipo_usuario'] ?? '') === 'reciclador') ? 'selected' : '' ?>>
                        Reciclador — busco material para recolectar
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-block btn-lg">Registrarme</button>
        </form>

        <p style="margin-top:1.1rem;text-align:center;">
            ¿Ya tienes cuenta? <a href="login.php"><strong>Inicia sesión</strong></a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>
