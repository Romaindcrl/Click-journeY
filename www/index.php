<?php
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="src/css/home.css">
<link rel="stylesheet" href="src/css/index-specific.css">
<?php

// Lecture du fichier voyages.json
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];

// Charger les avis s'ils existent
$avisFile = __DIR__ . '/../data/avis.json';
$avisParVoyage = [];
$notesMoyennes = [];

if (file_exists($avisFile)) {
    $avisContent = file_get_contents($avisFile);
    $avisData = json_decode($avisContent, true);

    if (isset($avisData['avis'])) {
        foreach ($avisData['avis'] as $avis) {
            if (isset($avis['statut']) && $avis['statut'] === 'publié') {
                $voyageId = $avis['voyage_id'];

                if (!isset($avisParVoyage[$voyageId])) {
                    $avisParVoyage[$voyageId] = [];
                    $notesMoyennes[$voyageId] = ['total' => 0, 'count' => 0];
                }

                $avisParVoyage[$voyageId][] = $avis;
                $notesMoyennes[$voyageId]['total'] += $avis['note'];
                $notesMoyennes[$voyageId]['count']++;
            }
        }

        // Calculer les moyennes
        foreach ($notesMoyennes as $voyageId => $data) {
            if ($data['count'] > 0) {
                $notesMoyennes[$voyageId] = round($data['total'] / $data['count'], 1);
            } else {
                $notesMoyennes[$voyageId] = 0;
            }
        }
    }
}
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
                        <p><?php echo htmlspecialchars(substr($voyage['description'], 0, 150)); ?>...</p>
                        <div class="destination-price">À partir de <?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</div>

                        <div class="destination-rating">
                            <?php
                            // Récupérer la note moyenne pour ce voyage
                            $moyenne = isset($notesMoyennes[$voyage['id']]) ? $notesMoyennes[$voyage['id']] : 0;
                            $avisCount = isset($avisParVoyage[$voyage['id']]) ? count($avisParVoyage[$voyage['id']]) : 0;

                            // Afficher les étoiles basées sur la note moyenne
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= floor($moyenne)) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i - 0.5 <= $moyenne) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            if ($avisCount > 0) {
                                echo '<span class="rating-count">(' . $avisCount . ' avis)</span>';
                            }
                            ?>
                        </div>

                        <a href="voyage-details.php?id=<?php echo $voyage['id']; ?>" class="btn btn-outline">Découvrir</a>
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
            <?php if (!empty($avisData['avis'])):
                $testimonials = array_slice($avisData['avis'], 0, 3);
                foreach ($testimonials as $avis):
                    if (isset($avis['statut']) && $avis['statut'] === 'publié'): ?>
                        <div class="testimonial">
                            <div class="testimonial-content">
                                <?php
                                // D'abord, décoder les entités HTML
                                $commentaireDecode = html_entity_decode($avis['commentaire'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // Prendre le sous-ensemble désiré
                                $commentaireSubstr = substr($commentaireDecode, 0, 150);
                                // Ensuite, appliquer htmlspecialchars pour un affichage sécurisé
                                $commentaireSecurise = htmlspecialchars($commentaireSubstr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                ?>
                                <p>"<?php echo $commentaireSecurise; ?>..."</p>
                            </div>
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $avis['note'] ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="testimonial-author">
                                <p><strong><?php echo htmlspecialchars($avis['user_prenom'] . ' ' . substr($avis['user_nom'], 0, 1)); ?>.</strong>,
                                    <?php echo date('d/m/Y', strtotime($avis['date'])); ?></p>
                            </div>
                        </div>
                <?php endif;
                endforeach;
            else: ?>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Une expérience inoubliable ! L'organisation était parfaite et les lieux visités magnifiques."</p>
                    </div>
                    <div class="testimonial-author">
                        <p><strong>Marie D.</strong>, Paris</p>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Le meilleur voyage de ma vie. Je recommande Click-journeY à tous mes amis !"</p>
                    </div>
                    <div class="testimonial-author">
                        <p><strong>Jean M.</strong>, Lyon</p>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"Un service client exceptionnel et des destinations qui sortent des sentiers battus."</p>
                    </div>
                    <div class="testimonial-author">
                        <p><strong>Sophie L.</strong>, Marseille</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>