<?php
require_once __DIR__ . '/includes/header.php';

// Lecture du fichier voyages.json
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];
?>

<div class="hero">
    <div class="hero-content">
        <h1>Aventures en 4L</h1>
        <p>Découvrez le monde au rythme de votre Renault 4L, une expérience authentique et inoubliable</p>
        <a href="voyages.php" class="btn btn-primary btn-large">Découvrir nos circuits</a>
    </div>
</div>

<section class="featured-destinations">
    <div class="container">
        <h2 class="section-title">Destinations populaires</h2>
        
        <div class="destination-slider">
            <?php foreach (array_slice($voyages, 0, 3) as $voyage): ?>
            <div class="destination-card">
                <div class="destination-image">
                    <img src="<?php echo htmlspecialchars($voyage['image']); ?>" 
                         alt="<?php echo htmlspecialchars($voyage['nom']); ?>" 
                         loading="lazy">
                </div>
                <div class="destination-info">
                    <h3><?php echo htmlspecialchars($voyage['nom']); ?></h3>
                    <p><?php echo htmlspecialchars($voyage['description']); ?></p>
                    <div class="destination-price">À partir de <?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</div>
                    <a href="voyages.php" class="btn btn-outline">Découvrir</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="why-choose-us">
    <div class="container">
        <h2 class="section-title">Pourquoi choisir Click-journeY ?</h2>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Destinations uniques</h3>
                <p>Des lieux soigneusement sélectionnés pour vous offrir des expériences extraordinaires.</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Sécurité garantie</h3>
                <p>Votre sécurité est notre priorité absolue pendant toute la durée de votre voyage.</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3>Prix compétitifs</h3>
                <p>Des voyages de qualité à des prix adaptés à tous les budgets.</p>
            </div>
            
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Support 24/7</h3>
                <p>Notre équipe est disponible à tout moment pour répondre à vos questions.</p>
            </div>
        </div>
    </div>
</section>

<section class="testimonials">
    <div class="container">
        <h2 class="section-title">Ce que disent nos clients</h2>
        
        <div class="testimonial-slider">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Une expérience inoubliable ! L'organisation était parfaite et les lieux visités magnifiques."</p>
                </div>
                <div class="testimonial-author">
                    <p><strong>Marie Dupont</strong>, Paris</p>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Le meilleur voyage de ma vie. Je recommande Click-journeY à tous mes amis !"</p>
                </div>
                <div class="testimonial-author">
                    <p><strong>Jean Martin</strong>, Lyon</p>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Un service client exceptionnel et des destinations qui sortent des sentiers battus."</p>
                </div>
                <div class="testimonial-author">
                    <p><strong>Sophie Leclerc</strong>, Marseille</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 