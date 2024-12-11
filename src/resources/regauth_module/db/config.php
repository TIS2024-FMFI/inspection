<?php
$host = 'localhost';
$dbname = 'safety_app'; // Имя базы данных
$username = 'root'; // Имя пользователя MySQL
$password = ''; // Пароль (по умолчанию пустой для XAMPP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

