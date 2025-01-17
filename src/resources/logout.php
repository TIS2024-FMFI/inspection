<?php
session_start();
session_destroy();

$current_page = basename($_SERVER['PHP_SELF']); // current page name

if ($current_page === 'history.php' || $current_page === 'PersonalizedList.php') {
    header('Location: index.php');
} else {
    $current_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $current_url");
}

// header('Location: index.php');
exit;
?>
