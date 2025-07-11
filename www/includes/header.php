<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Définir les informations de base du site
$siteTitle = "Click-journeY";
$siteDescription = "Votre compagnon de voyage";

// Récupérer le nom de la page actuelle
$current_page = basename($_SERVER['PHP_SELF']);

// Vérifier si l'utilisateur est connecté
$user_logged_in = isset($_SESSION['user']);
$is_admin = $user_logged_in && $_SESSION['user']['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle; ?> - <?php echo $siteDescription; ?></title>

    <!-- Polices Google Fonts selon la charte graphique -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Amarante&family=Grechen+Fuemen&display=swap" rel="stylesheet">

    <!-- Feuilles de style -->
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/header-specific.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="src/images/logo.png" type="image/png">

</head>

<body>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash']['type']; ?>">
            <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <script>
            setTimeout(function() {
                const flashMessage = document.querySelector('.flash-message');
                if (flashMessage) {
                    flashMessage.style.opacity = '0';
                    flashMessage.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        flashMessage.remove();
                    }, 300);
                }
            }, 5000);
        </script>
        <?php unset($_SESSION['flash']); ?>
    <?php elseif (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_type']; ?>">
            <?php echo $_SESSION['flash_message']; ?>
        </div>
        <script>
            setTimeout(function() {
                const flashMessage = document.querySelector('.flash-message');
                if (flashMessage) {
                    flashMessage.style.opacity = '0';
                    flashMessage.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        flashMessage.remove();
                    }, 300);
                }
            }, 5000);
        </script>
        <?php
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <header>
        <nav>
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <img src="src/images/logo.png" alt="Logo Click-journeY" class="logo-img">
                    <span class="logo-text">Click-journeY</span>
                </a>
            </div>

            <ul class="nav-links">
                <li><a href="index.php" <?php echo ($current_page === 'index.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>>Accueil</a></li>
                <li><a href="voyages.php" <?php echo ($current_page === 'voyages.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>>Voyages</a></li>
                <li><a href="avis.php" <?php echo ($current_page === 'avis.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>>Avis voyageurs</a></li>
                <li><a href="panier.php" <?php echo ($current_page === 'panier.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>><i class="fas fa-shopping-cart"></i>
                        <?php
                        $cartCount = isset($_SESSION['reservations']) && is_array($_SESSION['reservations']) ? count($_SESSION['reservations']) : 0;
                        if ($cartCount > 0): ?>
                            <span class="cart-count">(<?php echo $cartCount; ?>)</span>
                        <?php else: ?>
                            Panier
                        <?php endif; ?>
                    </a></li>

                <?php if ($user_logged_in): ?>
                    <li><a href="profil.php" <?php echo ($current_page === 'profil.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>>
                            <i class="fas fa-user-circle"></i> Profil
                        </a></li>
                    <li><a href="logout.php" class="btn-connexion">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a></li>
                <?php else: ?>
                    <li><a href="inscription.php" class="btn-inscription">
                            <i class="fas fa-user-plus"></i> Inscription
                        </a></li>
                    <li><a href="connexion.php" class="btn-connexion">
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </a></li>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <li><a href="admin.php" <?php echo ($current_page === 'admin.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>>
                            <i class="fas fa-cog"></i> Administration
                        </a></li>
                <?php endif; ?>

                <li>
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="slider round"></span>
                    </label>
                </li>
            </ul>
        </nav>
    </header>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'banned'): ?>
        <div class="alert alert-danger" style="max-width: 1200px; margin: 1rem auto; padding: 1rem; background-color: #fee2e2; color: #ef4444; border-radius: 8px; text-align: center; font-weight: 500;">
            <i class="fas fa-exclamation-triangle"></i> Votre compte a été suspendu. Veuillez contacter l'administrateur pour plus d'informations.
        </div>
    <?php endif; ?>

    <script src="src/js/dark-theme.js"></script>

</body>

</html>