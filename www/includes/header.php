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
    <link rel="stylesheet" href="src/css/reviews.css">
    <link rel="stylesheet" href="src/css/confirmation.css">
    <link rel="stylesheet" href="src/css/auth.css">
    <link rel="stylesheet" href="src/css/home.css">
    <link rel="stylesheet" href="src/css/voyages.css">
    <link rel="stylesheet" href="src/css/voyage-details.css">
    <link rel="stylesheet" href="src/css/search.css">
    <link rel="stylesheet" href="src/css/profile.css">
    <link rel="stylesheet" href="src/css/personnalisation.css">
    <link rel="stylesheet" href="src/css/paiement.css">
    <link rel="stylesheet" href="src/css/cards.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="src/images/logo.png" type="image/png">
    
    <style>
    /* Styles améliorés pour le header selon la charte graphique */
    :root {
        --rich-black: #041728;
        --lapis-lazuli: #2d5977;
        --air-blue: #65A4CA;
        --silver: #b6b6b6;
        --white: #FFFFFF;
        --tomato: #FE4A49;
        
        --background-color: #f8f9fa;
        --text-color: var(--rich-black);
    }
    
    body {
        background-color: var(--background-color);
        color: var(--text-color);
        transition: all 0.3s ease;
        margin: 0;
        padding: 0;
    }
    
    header {
        background-color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(4, 23, 40, 0.1);
        padding: 0.75rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        transition: all 0.3s ease;
    }
    
    nav {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1.5rem;
    }
    
    .logo-container {
        display: flex;
        align-items: center;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        transition: transform 0.3s ease;
    }
    
    .logo:hover {
        transform: translateY(-2px);
    }
    
    .logo-img {
        width: 36px;
        height: 36px;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(4, 23, 40, 0.15));
    }
    
    .logo-text {
        font-family: 'Grechen Fuemen', cursive;
        font-size: 1.75rem;
        color: #041728;
        font-weight: 600;
        position: relative;
    }
    
    .logo-text::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, #2d5977, #65A4CA);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.3s ease;
    }
    
    .logo:hover .logo-text::after {
        transform: scaleX(1);
        transform-origin: left;
    }
    
    .nav-links {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .nav-links li {
        position: relative;
    }
    
    .nav-links a {
        color: #041728;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.92rem;
        padding: 0.5rem 0.6rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
        display: inline-block;
        white-space: nowrap;
    }
    
    .nav-links a:hover {
        color: #2d5977;
        background-color: rgba(101, 164, 202, 0.1);
        transform: translateY(-2px);
    }
    
    .btn-inscription, .btn-connexion {
        background-color: #2d5977;
        color: white !important;
        border-radius: 50px !important;
        padding: 0.5rem 1.25rem !important;
        transition: all 0.3s ease;
        font-weight: 600 !important;
        box-shadow: 0 4px 6px rgba(45, 89, 119, 0.2);
    }
    
    .btn-inscription:hover, .btn-connexion:hover {
        background-color: #224760 !important;
        color: white !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 6px 10px rgba(45, 89, 119, 0.3) !important;
    }
    
    .btn-connexion {
        background-color: transparent !important;
        color: #2d5977 !important;
        border: 2px solid #2d5977 !important;
        box-shadow: none !important;
    }
    
    .btn-connexion:hover {
        background-color: #2d5977 !important;
        color: white !important;
    }
    
    /* Style pour le toggle de thème */
    .theme-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
        margin-left: 0.5rem;
    }
    
    .theme-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e0e0e0;
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    input:checked + .slider {
        background-color: #2d5977;
    }
    
    input:checked + .slider:before {
        transform: translateX(24px);
    }
    
    .slider:after {
        content: '☀️';
        position: absolute;
        left: 6px;
        top: 1px;
        font-size: 12px;
        display: block;
        transition: .4s;
        color: #041728;
    }
    
    input:checked + .slider:after {
        content: '🌙';
        left: 28px;
        color: white;
    }
    
    /* Mode sombre pour le header et global */
    [data-theme="dark"] {
        --rich-black: #041728;
        --lapis-lazuli: #2d5977;
        --air-blue: #65A4CA;
        --silver: #666;
        --white: #1a1a1a;
        --text-color: #e0e0e0;
        --background-color: #121212;
    }
    
    [data-theme="dark"] body {
        background-color: var(--background-color);
        color: var(--text-color);
    }
    
    [data-theme="dark"] header {
        background-color: #1a1a1a;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    
    [data-theme="dark"] .logo-text {
        color: #FFFFFF;
    }
    
    [data-theme="dark"] .nav-links a {
        color: #e0e0e0;
    }
    
    [data-theme="dark"] .nav-links a:hover {
        color: #65A4CA;
        background-color: rgba(101, 164, 202, 0.1);
    }
    
    [data-theme="dark"] .btn-connexion {
        background-color: transparent !important;
        color: #65A4CA !important;
        border: 2px solid #65A4CA !important;
    }
    
    [data-theme="dark"] .btn-connexion:hover {
        background-color: #65A4CA !important;
        color: #041728 !important;
    }
    
    [data-theme="dark"] .btn-inscription {
        background-color: #65A4CA !important;
        color: #041728 !important;
    }
    
    [data-theme="dark"] .btn-inscription:hover {
        background-color: #56a0ca !important;
    }
    
    /* Style pour les messages flash */
    .flash-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1100;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        display: flex;
        align-items: center;
        max-width: 350px;
        animation: slideIn 0.3s forwards;
        opacity: 0.95;
        transition: opacity 0.3s ease;
    }
    
    .flash-message:hover {
        opacity: 1;
    }
    
    .flash-message.success {
        background-color: #e1f5e9;
        color: #0d6832;
        border-left: 4px solid #0d6832;
    }
    
    .flash-message.error {
        background-color: #fee2e2;
        color: #ef4444;
        border-left: 4px solid #ef4444;
    }
    
    .flash-message.info {
        background-color: #e0f2fe;
        color: #2d5977;
        border-left: 4px solid #2d5977;
    }
    
    .flash-message.warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #856404;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 0.95; }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        nav {
            flex-direction: column;
            padding: 1rem;
        }
        
        .logo-container {
            margin-bottom: 1rem;
        }
        
        .nav-links {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .nav-links a {
            font-size: 0.9rem;
            padding: 0.4rem 0.6rem;
        }
        
        .nav-links li {
            margin: 0.2rem;
        }
        
        .flash-message {
            max-width: 90%;
            left: 5%;
            right: 5%;
        }
    }
    
    @media (min-width: 769px) and (max-width: 992px) {
        .nav-links {
            gap: 0.5rem;
        }
        
        .nav-links a {
            padding: 0.4rem 0.5rem;
            font-size: 0.9rem;
        }
    }
    </style>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Correction navigation -->
    <script>
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
                <li><a href="panier.php" <?php echo ($current_page === 'panier.php') ? 'style="font-weight: 600; color: #2d5977;"' : ''; ?>><i class="fas fa-shopping-cart"></i><?php if(isset($_SESSION['reservation'])): ?><span class="cart-count"> (1)</span><?php else: ?> Panier<?php endif; ?></a></li>
                
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
    </script>
</body>
</html> 