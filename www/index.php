<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1>Découvrez des destinations uniques</h1>
        <p>Voyages sur mesure pour des expériences inoubliables</p>
        <a href="voyages.php" class="btn btn-primary btn-large">Explorer nos voyages</a>
    </div>
</div>

<section class="featured-destinations">
    <div class="container">
        <h2 class="section-title">Destinations populaires</h2>
        
        <div class="destination-slider">
            <div class="destination-card">
                <div class="destination-image">
                    <img src="https://images.unsplash.com/photo-1589394760151-b4c9895088e5?q=80&w=1974&auto=format&fit=crop" alt="Paris">
                </div>
                <div class="destination-info">
                    <h3>Paris</h3>
                    <p>La ville de l'amour et de la lumière</p>
                    <a href="voyages.php" class="btn btn-outline">Découvrir</a>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="https://images.unsplash.com/photo-1597910037310-7dd8ddb93e24?q=80&w=2070&auto=format&fit=crop" alt="Bali">
                </div>
                <div class="destination-info">
                    <h3>Bali</h3>
                    <p>Plages paradisiaques et temples spirituels</p>
                    <a href="voyages.php" class="btn btn-outline">Découvrir</a>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="https://images.unsplash.com/photo-1555992457-b8fefdd46da2?q=80&w=2064&auto=format&fit=crop" alt="New York">
                </div>
                <div class="destination-info">
                    <h3>New York</h3>
                    <p>La ville qui ne dort jamais</p>
                    <a href="voyages.php" class="btn btn-outline">Découvrir</a>
                </div>
            </div>
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

<style>
/* Styles de la page d'accueil */
.hero {
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1488085061387-422e29b40080?q=80&w=2031&auto=format&fit=crop');
    background-size: cover;
    background-position: center;
    height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
}

.hero-content {
    max-width: 800px;
    padding: 0 2rem;
}

.hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.hero p {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.2rem;
}

.section-title {
    text-align: center;
    margin: 3rem 0;
    font-size: 2.2rem;
    color: var(--primary-color);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

section {
    padding: 4rem 0;
}

/* Styles des cartes de destination */
.destination-slider {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
}

.destination-card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.destination-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.destination-image {
    height: 200px;
    overflow: hidden;
}

.destination-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.destination-card:hover .destination-image img {
    transform: scale(1.1);
}

.destination-info {
    padding: 1.5rem;
    background: white;
}

.destination-info h3 {
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
    color: var(--primary-color);
}

.destination-info p {
    margin-bottom: 1rem;
    color: var(--text-color);
}

/* Styles des caractéristiques */
.features {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
}

.feature {
    text-align: center;
    padding: 1.5rem;
    border-radius: 10px;
    background: white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.feature:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 1.5rem;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 5px 15px rgba(65, 105, 225, 0.3);
}

.feature h3 {
    margin-bottom: 1rem;
    color: var(--primary-color);
}

/* Styles des témoignages */
.testimonials {
    background: #f8f9fa;
}

.testimonial-slider {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.testimonial {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.testimonial-content {
    margin-bottom: 1rem;
    font-style: italic;
    position: relative;
}

.testimonial-content:before {
    content: """;
    font-size: 4rem;
    color: var(--primary-color);
    opacity: 0.2;
    position: absolute;
    top: -1.5rem;
    left: -0.5rem;
}

.testimonial-author {
    text-align: right;
}

/* Dark Mode */
[data-theme="dark"] .destination-info,
[data-theme="dark"] .feature,
[data-theme="dark"] .testimonial {
    background: #2d2d2d;
    color: #e1e1e1;
}

[data-theme="dark"] .testimonials {
    background: #1a1a1a;
}

[data-theme="dark"] .destination-info h3,
[data-theme="dark"] .feature h3 {
    color: #4169E1;
}

/* Responsive */
@media (max-width: 768px) {
    .hero h1 {
        font-size: 2.5rem;
    }
    
    .hero p {
        font-size: 1.2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 