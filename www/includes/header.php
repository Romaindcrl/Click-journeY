<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click-journeY - Voyages sur mesure</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash']['type']; ?>">
            <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <header>
        <nav>
            <a href="index.php" class="logo">Click-journeY</a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="voyages.php">Voyages</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn btn-danger">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php" class="btn btn-primary">Connexion</a></li>
                    <li><a href="inscription.php" class="btn btn-warning">Inscription</a></li>
                <?php endif; ?>
                <li>
                    <div class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <label for="theme-toggle">
                            <span class="theme-icon">🌙</span>
                        </label>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'banned'): ?>
        <div class="alert alert-danger">
            Votre compte a été suspendu. Veuillez contacter l'administrateur pour plus d'informations.
        </div>
    <?php endif; ?>

    <main>
    <script>
    // Gestion du thème sombre/clair
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const themeIcon = document.querySelector('.theme-icon');

    // Charger le thème depuis localStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    themeToggle.checked = savedTheme === 'dark';
    themeIcon.textContent = savedTheme === 'dark' ? '☀️' : '🌙';

    themeToggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'dark' : 'light';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeIcon.textContent = newTheme === 'dark' ? '☀️' : '🌙';
    });

    // Gestion des flash messages
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessage = document.querySelector('.flash-message');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.opacity = '0';
                setTimeout(() => {
                    flashMessage.remove();
                }, 300);
            }, 3000);
        }
    });
    </script> 