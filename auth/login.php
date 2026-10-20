<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../funciones/funciones.php';

// Si ya está autenticado, redirigir al tablero
if (estaAutenticado()) {
    header('Location: ../tablero.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($email !== '' && $contrasena !== '') {
        $stmt = $mysqli->prepare('SELECT id_usuario, nombre, email, contrasena_hash, tipo_usuario FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();

        if ($usuario && password_verify($contrasena, $usuario['contrasena_hash'])) {
            // Guardar variables indispensables de la sesión
            $_SESSION['usuario_id'] = (int)$usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = strtolower(trim($usuario['tipo_usuario']));

            header('Location: ../tablero.php');
            exit();
        } else {
            $error = 'Credenciales incorrectas. Verifique su correo y contraseña.';
        }
    } else {
        $error = 'Por favor ingrese su correo electrónico y contraseña.';
    }
}

$tituloPagina = 'Ingresar — EcoConexión';
$rutaBase = '../';
require __DIR__ . '/../includes/cabecera.php';
?>

<div class="contenedor" style="max-width: 440px;">
    <div class="page-head anim-rise" style="text-align: center;">
        <span class="sello">Acceso</span>
        <h2>Iniciar Sesión</h2>
    </div>

    <div class="card anim-rise-2">
        <?php if ($error !== ''): ?>
            <div class="alerta alerta-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="ejemplo@correo.com" required>
            </div>
            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-block btn-lg" style="margin-top: 1rem;">Ingresar</button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
            ¿No tienes cuenta? <a href="registro.php" style="color: var(--botella); font-weight: 700;">Regístrate aquí</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>