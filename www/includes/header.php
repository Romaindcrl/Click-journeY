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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash']['type']; ?>">
            <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <script>
            setTimeout(function() {
                document.querySelector('.flash-message').remove();
            }, 3000);
        </script>
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
                    <li><a href="logout.php" class="btn btn-danger">Déconnexion</a></li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php" class="btn btn-inscription">Inscription</a></li>
                <?php endif; ?>
                <li>
                    <div class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <label for="theme-toggle">
                            <i class="fas fa-sun"></i>
                            <i class="fas fa-moon"></i>
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
    // Gestion du thème
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    
    // Vérifier le thème stocké ou utiliser le thème clair par défaut
    const currentTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-theme', currentTheme);
    themeToggle.checked = currentTheme === 'dark';
    
    // Changer le thème lorsque l'utilisateur clique sur le toggle
    themeToggle.addEventListener('change', function() {
        const theme = this.checked ? 'dark' : 'light';
        htmlElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
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