<?php
declare(strict_types=1);

require_once __DIR__ . '/db_config.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(): void
{
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        $error = 'La sesión del formulario caducó. Actualice la página e inténtelo de nuevo.';
    } elseif (isset($_POST['logout'])) {
        $_SESSION = [];
        session_regenerate_id(true);
        redirect();
    } elseif (isset($_POST['login'])) {
        $username = trim((string) ($_POST['user'] ?? ''));
        $password = (string) ($_POST['pass'] ?? '');
        $statement = $pdo->prepare('SELECT id, usuario, password FROM usuarios WHERE usuario = ?');
        $statement->execute([$username]);
        $user = $statement->fetch();
        $valid = $user && (password_verify($password, $user['password']) ||
            hash_equals($user['password'], $password));
        if ($valid) {
            if (!password_verify($password, $user['password'])) {
                $update = $pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            redirect();
        }
        $error = 'Usuario o contraseña incorrectos.';
    } elseif (isset($_POST['agendar'])) {
        if (empty($_SESSION['usuario'])) {
            http_response_code(403);
            $error = 'Inicie sesión para registrar una cita.';
        } else {
            $patient = trim((string) ($_POST['paciente'] ?? ''));
            $specialty = (string) ($_POST['especialidad'] ?? '');
            $date = (string) ($_POST['fecha'] ?? '');
            $time = (string) ($_POST['hora'] ?? '');
            $allowed = ['Medicina General', 'Pediatría', 'Odontología'];
            $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            $validDate = $parsedDate && $parsedDate->format('Y-m-d') === $date;
            $validTime = preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
            if ($patient === '' || strlen($patient) > 240 || !in_array($specialty, $allowed, true) || !$validDate || !$validTime) {
                $error = 'Revise el nombre, la especialidad, la fecha y la hora.';
            } else {
                $statement = $pdo->prepare('INSERT INTO citas (paciente_nombre, especialidad, fecha, hora) VALUES (?, ?, ?, ?)');
                $statement->execute([$patient, $specialty, $date, $time]);
                $_SESSION['message'] = 'Cita registrada correctamente.';
                redirect();
            }
        }
    }
}

$appointments = [];
if (!empty($_SESSION['usuario'])) {
    $appointments = $pdo->query('SELECT id, paciente_nombre, especialidad, fecha, hora, estado FROM citas ORDER BY id DESC')->fetchAll();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Citas médicas</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="container">
    <header class="topbar">
        <div><p class="eyebrow">Sistema local</p><h1>Citas médicas</h1></div>
        <?php if (!empty($_SESSION['usuario'])): ?>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                <button class="secondary" type="submit" name="logout">Cerrar sesión</button>
            </form>
        <?php endif; ?>
    </header>

    <?php if ($error): ?><p class="alert error" role="alert"><?= escape($error) ?></p><?php endif; ?>
    <?php if ($message): ?><p class="alert success" role="status"><?= escape($message) ?></p><?php endif; ?>

    <?php if (empty($_SESSION['usuario'])): ?>
        <section class="card narrow" aria-labelledby="login-title">
            <h2 id="login-title">Iniciar sesión</h2>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                <label for="user">Usuario</label>
                <input id="user" name="user" autocomplete="username" required>
                <label for="pass">Contraseña</label>
                <input id="pass" type="password" name="pass" autocomplete="current-password" required>
                <button type="submit" name="login">Ingresar</button>
            </form>
        </section>
    <?php else: ?>
        <section class="card" aria-labelledby="appointment-title">
            <h2 id="appointment-title">Registrar cita</h2>
            <form method="post">
                <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                <label for="paciente">Nombre del paciente</label>
                <input id="paciente" name="paciente" maxlength="120" required>
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" name="especialidad" required>
                    <option>Medicina General</option><option>Pediatría</option><option>Odontología</option>
                </select>
                <div class="fields">
                    <div><label for="fecha">Fecha</label><input id="fecha" type="date" name="fecha" required></div>
                    <div><label for="hora">Hora</label><input id="hora" type="time" name="hora" required></div>
                </div>
                <button type="submit" name="agendar">Guardar cita</button>
            </form>
        </section>
        <section class="card" aria-labelledby="list-title">
            <h2 id="list-title">Citas registradas</h2>
            <?php if (!$appointments): ?><p>Aún no hay citas registradas.</p><?php else: ?>
                <div class="table-scroll"><table><thead><tr><th>ID</th><th>Paciente</th><th>Especialidad</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead><tbody>
                <?php foreach ($appointments as $appointment): ?><tr>
                    <td><?= (int) $appointment['id'] ?></td>
                    <td><?= escape($appointment['paciente_nombre']) ?></td>
                    <td><?= escape($appointment['especialidad']) ?></td>
                    <td><?= escape($appointment['fecha']) ?></td>
                    <td><?= escape($appointment['hora']) ?></td>
                    <td><?= escape($appointment['estado']) ?></td>
                </tr><?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>

