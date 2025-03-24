<?php
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header("Location: connexion.php");
        exit();
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
}
?> 