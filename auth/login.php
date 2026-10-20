<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../funciones/funciones.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare('SELECT id_usuario, nombre, contrasena_hash, tipo_usuario FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if ($usuario && password_verify($password, $usuario['contrasena_hash'])) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];
        header('Location: ../tablero.php');
        exit();
    }
    $error = 'Credenciales incorrectas. Verifique su correo y contraseña.';
}

$tituloPagina = 'Ingresar — EcoConexión';
$rutaBase = '../';
require __DIR__ . '/../includes/cabecera.php';
?>

<div class="auth-wrap anim-rise">
    <div class="page-head">
        <span class="sello">Acceso</span>
        <h2>Iniciar sesión</h2>
        <p>Entra para publicar material o gestionar tus ofertas.</p>
    </div>

    <div class="card">
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito"><?= e($_SESSION['mensaje_exito']) ?></div>
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-block btn-lg">Ingresar</button>
        </form>

        <div class="auth-demo">
            <strong>Demo presentación</strong><br>
            Generadora: <code>maria@ecoconexion.co</code><br>
            Reciclador: <code>carlos@ecoconexion.co</code><br>
            Clave: <code>EcoDemo2024!</code>
        </div>

        <p style="margin-top:1.1rem;text-align:center;">
            ¿No tienes cuenta? <a href="registro.php"><strong>Regístrate</strong></a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>
