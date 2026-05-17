<?php
$host = '127.0.0.1';
$port = 3306;
$db   = 'santa_lucia';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "users_count: " . $count . "\n";
    $row = $pdo->query('SELECT id, name, email FROM users LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "first_user: " . $row['email'] . " (" . $row['name'] . ")\n";
    } else {
        echo "no users found\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
