<?php
function checkBanned() {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'banni') {
        // Déconnexion de l'utilisateur
        session_destroy();
        session_start();
        
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Votre compte a été banni. Veuillez contacter l\'administrateur pour plus d\'informations.'
        ];
        
        header('Location: connexion.php');
        exit();
    }
}
?> 