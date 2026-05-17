<?php
// Lista las tablas en la base de datos configurada (santa_lucia)
$host = '127.0.0.1';
$port = 3306;
$db   = 'santa_lucia';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = :db');
    $stmt->execute([':db' => $db]);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$tables) {
        echo "NO_TABLES\n";
        exit(0);
    }

    foreach ($tables as $t) {
        echo $t . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
