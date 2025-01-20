<?php
session_start();
session_destroy();

$current_page = basename($_SERVER['PHP_SELF']);

if ($current_page === 'history.php' || $current_page === 'PersonalizedList.php') {
    header('Location: welcome.php');
} else {
    $current_url = $_SERVER['HTTP_REFERER'] ?? 'welcome.php';
    header("Location: $current_url");
}
exit;
?>
