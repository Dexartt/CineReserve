<?php
$host = 'localhost';
$db   = 'cinema_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT * FROM users WHERE username='enes12'");
    $userObj = $stmt->fetch();
    var_dump($userObj);
    var_dump((bool)$userObj['is_admin']);
} catch (\PDOException $e) {
    echo $e->getMessage();
}
