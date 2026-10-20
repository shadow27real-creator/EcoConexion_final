<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/conexion/conexion.php';
require_once __DIR__ . '/funciones/funciones.php';

// Verificar que esté autenticado y sea administrador (tipo_usuario = 'administrador')
if (!estaAutenticado() || ($_SESSION['tipo_usuario'] ?? '') !== 'administrador') {
    header('Location: index.php');
    exit();
}

// Lógica para eliminar publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_pub'])) {
    $id_pub = (int)($_POST['id_publicacion'] ?? 0);
    $stmt = $mysqli->prepare('DELETE FROM publicaciones WHERE id_publicacion = ?');
    $stmt->bind_param('i', $id_pub);
    $stmt->execute();
    header('Location: admin.php?msg=pub_eliminada');
    exit();
}

// Lógica para eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usr'])) {
    $id_usr = (int)($_SESSION['usuario_id'] ?? 0);
    $target_usr = (int)($_POST['id_usuario'] ?? 0);
    // Evitar que el admin se elimine a sí mismo
    if ($target_usr !== $id_usr) {
        $stmt = $mysqli->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $stmt->bind_param('i', $target_usr);
        $stmt->execute();
    }
    header('Location: admin.php?msg=usr_eliminado');
    exit();
}

// Obtener todas las publicaciones
$publicaciones = $mysqli->query(
    'SELECT p.id_publicacion, p.titulo, p.estado, p.fecha_publicacion, u.nombre AS generador, m.nombre AS material
     FROM publicaciones p
     JOIN usuarios u ON p.id_usuario = u.id_usuario
     JOIN materiales m ON p.id_material = m.id_material
     ORDER BY p.fecha_publicacion DESC'
)->fetch_all(MYSQLI_ASSOC);

// Obtener todos los usuarios
$usuarios = $mysqli->query(
    'SELECT id_usuario, nombre, email, telefono, tipo_usuario, creado_en FROM usuarios ORDER BY creado_en DESC'
)->fetch_all(MYSQLI_ASSOC);

$tituloPagina = 'Panel de Administración — EcoConexión';
$rutaBase = '';
require __DIR__ . '/includes/cabecera.php';
?>

<div class="contenedor" style="max-width: 1000px;">
    <div class="page-head anim-rise">
        <span class="sello">Control Global</span>
        <h2>Panel de Administrador</h2>
        <p>Supervisa los registros de la plataforma y gestiona la moderación de publicaciones y usuarios.</p>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alerta alerta-exito anim-rise">Acción de administración ejecutada correctamente.</div>
    <?php endif; ?>

    <div class="card anim-rise-2" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem; color: var(--botella);">Publicaciones del Sistema (<?= count($publicaciones) ?>)</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tinta); text-align: left;">
                        <th style="padding: 0.5rem;">Título</th>
                        <th style="padding: 0.5rem;">Material</th>
                        <th style="padding: 0.5rem;">Generador</th>
                        <th style="padding: 0.5rem;">Estado</th>
                        <th style="padding: 0.5rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($publicaciones as $pub): ?>
                        <tr style="border-bottom: 1px dashed var(--linea);">
                            <td style="padding: 0.6rem;"><?= e($pub['titulo']) ?></td>
                            <td style="padding: 0.6rem;"><?= e($pub['material']) ?></td>
                            <td style="padding: 0.6rem;"><?= e($pub['generador']) ?></td>
                            <td style="padding: 0.6rem;"><?= e(ucfirst($pub['estado'])) ?></td>
                            <td style="padding: 0.6rem;">
                                <form action="admin.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta publicación?');" style="display:inline;">
                                    <input type="hidden" name="id_publicacion" value="<?= (int)$pub['id_publicacion'] ?>">
                                    <button type="submit" name="eliminar_pub" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: var(--error); border-color: var(--error);">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card anim-rise-3">
        <h3 style="margin-bottom: 1rem; color: var(--botella);">Usuarios Registrados (<?= count($usuarios) ?>)</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--tinta); text-align: left;">
                        <th style="padding: 0.5rem;">Nombre</th>
                        <th style="padding: 0.5rem;">Correo</th>
                        <th style="padding: 0.5rem;">Tipo</th>
                        <th style="padding: 0.5rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usr): ?>
                        <tr style="border-bottom: 1px dashed var(--linea);">
                            <td style="padding: 0.6rem;"><?= e($usr['nombre']) ?></td>
                            <td style="padding: 0.6rem;"><?= e($usr['email']) ?></td>
                            <td style="padding: 0.6rem;"><?= e(ucfirst($usr['tipo_usuario'])) ?></td>
                            <td style="padding: 0.6rem;">
                                <?php if ((int)$usr['id_usuario'] !== (int)$_SESSION['usuario_id']): ?>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario y sus datos asociados?');" style="display:inline;">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$usr['id_usuario'] ?>">
                                        <button type="submit" name="eliminar_usr" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: var(--error); border-color: var(--error);">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--muted);">(Tú)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>