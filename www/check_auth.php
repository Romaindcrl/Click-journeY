<?php
function checkAuth() {
    // Vérifier si la session est démarrée
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user'])) {
        // Stocker l'URL actuelle pour redirection après connexion
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Message pour informer l'utilisateur
        $_SESSION['flash_message'] = 'Veuillez vous connecter pour accéder à cette page.';
        $_SESSION['flash_type'] = 'info';
        
        header("Location: connexion.php");
        exit();
    }
    return true;
}

function checkAdmin() {
    // Vérifier si la session est démarrée
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
    return true;
}
?> 