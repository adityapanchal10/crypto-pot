<?php

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (!isset($_GET['level'])) {
        header('Location: dashboard.php');
    } else {
        $level = $_GET['level'];
        if ($level == 'low') {
            setcookie('fnz_cookie_val', 'high', time() + (86400 * 30), "/");
        } else if ($level == 'high') {
            setcookie('fnz_cookie_val', 'low', time() + (86400 * 30), "/");
        } else if ($level == 'max') {
            setcookie('fnz_cookie_val', 'no', time() + (86400 * 30), "/");
        }
    }
    header('Location: dashboard.php');
}
?>