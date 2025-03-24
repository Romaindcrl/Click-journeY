<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <h1>Bienvenue sur <span class="brand-text">Gégé on the road</span></h1>
        <p class="hero-text">
            Partez à l'aventure à bord d'une voiture connectée et plongez au cœur 
            d'expériences hors du commun. Découvrez nos astuces de road trip, nos 
            rencontres passionnantes et la technologie embarquée qui rend chaque 
            voyage unique.
        </p>
        <img src="src/img/4l.jpg" alt="Voiture vintage" class="hero-image">
    </section>

    <!-- About Section -->
    <section class="about-section">
        <h2>Qui sommes-nous ?</h2>
        <p>
            Nous sommes une équipe de passionnés d'automobile et de nouvelles technologies, animés par 
            l'envie de partager nos découvertes sur la route. Avec <span class="brand-text">Gégé on the road</span>, 
            nous sillonnons les routes du monde entier pour dénicher des lieux insolites, rencontrer des gens 
            formidables et montrer comment la connectivité peut transformer l'expérience du voyage.
        </p>
    </section>

    <!-- Objectives Section -->
    <section class="objectives-section">
        <h2>Nos Objectifs</h2>
        <div class="objectives-grid">
            <div class="objective-card">
                <img src="src/img/voiture-connectee.png" alt="Voiture Connectée" class="objective-icon">
                <h3>Voiture Connectée</h3>
                <p>Explorez la haute technologie embarquée, apprenez à optimiser vos trajets grâce à la 
                data en temps réel et profitez d'un confort de conduite amélioré.</p>
            </div>
            <div class="objective-card">
                <img src="src/img/aventure.png" alt="Aventure sur la route" class="objective-icon">
                <h3>Aventure sur la route</h3>
                <p>Sortez des sentiers battus, découvrez des paysages hors norme, et vivez la liberté 
                d'un road trip mémorable en quête de moments authentiques.</p>
            </div>
            <div class="objective-card">
                <img src="src/img/communaute.png" alt="Communauté Engagée" class="objective-icon">
                <h3>Communauté Engagée</h3>
                <p>Rejoignez un réseau d'explorateurs et de passionnés de voyages afin de partager 
                vos expériences, vos bons plans et vos conseils sur la route.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2>Envie de rejoindre l'aventure ?</h2>
        <p>Inscrivez-vous dès maintenant pour suivre nos actualités, découvrir nos 
        prochains itinéraires et profiter des conseils de la communauté.</p>
        <div class="cta-buttons">
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="inscription.php" class="btn btn-primary">Rejoignez-nous</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<footer>
    <p>&copy; 2025 Gégé on the road. Tous droits réservés.</p>
    <p>Suivez nos aventures sur <a href="https://instagram.com" target="_blank">Instagram</a>.</p>
</footer>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 