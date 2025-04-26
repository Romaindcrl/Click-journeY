<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: connexion.php');
        exit();
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: profil.php');
        exit();
    }
} 