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
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/reviews.css">
    <link rel="stylesheet" href="src/css/confirmation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="src/images/logo.png" type="image/png">
    <!-- Ajout d'une solution globale pour corriger les problèmes de navigation -->
    <script>
    // Méthode traditionnelle de correction des liens (compatibilité maximale)
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction principale pour corriger les liens
        function fixAllNavigation() {
            // Trouver tous les liens dans le document
            var links = document.getElementsByTagName('a');
            
            // Parcourir chaque lien et appliquer le correctif
            for (var i = 0; i < links.length; i++) {
                var link = links[i];
                var href = link.getAttribute('href');
                
                // Ne traiter que les liens internes et valides
                if (href && !href.startsWith('http') && !href.startsWith('#')) {
                    // Créer une fonction de clic personnalisée pour chaque lien
                    link.onclick = (function(url) {
                        return function(e) {
                            e.preventDefault();
                            // Déboguer le lien utilisé
                            console.log("Navigation vers: " + url);
                            // Utiliser setTimeout pour éviter tout problème
                            setTimeout(function() {
                                window.location.href = url;
                            }, 10);
                            return false;
                        };
                    })(href);
                }
            }
        }
        
        // Exécuter immédiatement
        fixAllNavigation();
        
        // Réexécuter après un court délai
        setTimeout(fixAllNavigation, 200);
        
        // Et à nouveau après le chargement complet
        window.onload = function() {
            setTimeout(fixAllNavigation, 500);
        };
    });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <?php elseif (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_type']; ?>">
            <?php echo $_SESSION['flash_message']; ?>
        </div>
        <script>
            setTimeout(function() {
                document.querySelector('.flash-message').remove();
            }, 3000);
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
                <li><a href="index.php">Accueil</a></li>
                <li><a href="voyages.php">Voyages</a></li>
                <li><a href="avis.php">Avis voyageurs</a></li>
                
                <?php if ($user_logged_in): ?>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="deconnexion.php" class="btn-connexion">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="inscription.php" class="btn-inscription">Inscription</a></li>
                    <li><a href="connexion.php" class="btn-connexion">Connexion</a></li>
                <?php endif; ?>
                
                <?php if ($is_admin): ?>
                    <li><a href="admin.php">Administration</a></li>
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
        <div class="alert alert-danger">
            Votre compte a été suspendu. Veuillez contacter l'administrateur pour plus d'informations.
        </div>
    <?php endif; ?>

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
    <script src="src/js/script.js"></script>
    <script src="src/js/form-validation.js"></script>
    <script src="src/js/rating.js"></script>
    <script src="src/js/theme-switcher.js"></script>
</body>
</html> 