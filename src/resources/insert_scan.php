<?php
session_start();
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'safety_app';
$username_db = getenv('DB_USER') ?: 'root';
$password_db = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    $uid = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $product_link = $_POST['product_link'];
    $status = $_POST['status'];

    $date = date("Y-m-d");
    $time = date("H:i:s");

    $stmt = $pdo->prepare("INSERT INTO product_history (product_id, user_id, barcode, product_link, date, time, status) 
                               VALUES (:product_id, :user_id, :barcode, :product_link, :date, :time, :status)");

    $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $uid, PDO::PARAM_INT);
    $stmt->bindValue(":barcode", $barcode, PDO::PARAM_STR);
    $stmt->bindValue(":product_link", $product_link, PDO::PARAM_STR);
    $stmt->bindValue(":date", $date, PDO::PARAM_STR);
    $stmt->bindValue(":time", $time, PDO::PARAM_STR);
    $stmt->bindValue(":status", $status, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Scan successfully inserted"]);
        
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
}
?>