<?php
declare(strict_types=1);

$databasePath = getenv('CITAS_DB_PATH') ?: __DIR__ . DIRECTORY_SEPARATOR . 'citas_medicas.db';
$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec('CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
)');
$pdo->exec('CREATE TABLE IF NOT EXISTS citas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    paciente_nombre TEXT NOT NULL,
    especialidad TEXT NOT NULL,
    fecha TEXT NOT NULL,
    hora TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT "Registrada"
)');

$existingAdmin = $pdo->query("SELECT id FROM usuarios WHERE usuario = 'admin'")->fetch();
if (!$existingAdmin) {
    $statement = $pdo->prepare('INSERT INTO usuarios (usuario, password) VALUES (?, ?)');
    $statement->execute(['admin', password_hash('1234', PASSWORD_DEFAULT)]);
}

