<?php
/**
 * Vérifie si l'utilisateur connecté a le rôle d'administrateur
 * Si ce n'est pas le cas, redirige vers la page d'accueil
 */
function checkAdmin() {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        // Définir un message flash
        $_SESSION['flash_message'] = "Vous devez être connecté pour accéder à cette page.";
        $_SESSION['flash_type'] = 'error';
        
        // Rediriger vers la page de connexion
        header('Location: connexion.php');
        exit;
    }
    
    // Vérifier si l'utilisateur est administrateur
    if ($_SESSION['user']['role'] !== 'admin') {
        // Définir un message flash
        $_SESSION['flash_message'] = "Vous n'avez pas les droits suffisants pour accéder à cette page.";
        $_SESSION['flash_type'] = 'error';
        
        // Rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    }
    
    // L'utilisateur est connecté et est administrateur, continuer l'exécution
    return true;
} 