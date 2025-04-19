<?php
require_once __DIR__ . '/includes/header.php';

// Charger les avis
$avisFile = __DIR__ . '/../data/avis.json';
$avis = [];

if (file_exists($avisFile)) {
    $avisContent = file_get_contents($avisFile);
    $avis = json_decode($avisContent, true);
}

// Charger les voyages pour obtenir les informations
$voyagesFile = __DIR__ . '/../data/voyages.json';
$voyages = [];

if (file_exists($voyagesFile)) {
    $voyagesContent = file_get_contents($voyagesFile);
    $voyagesData = json_decode($voyagesContent, true);
    
    if (isset($voyagesData['voyages']) && is_array($voyagesData['voyages'])) {
        foreach ($voyagesData['voyages'] as $voyage) {
            $voyages[$voyage['id']] = $voyage;
        }
    }
}

// Fonction pour calculer la note moyenne
function calculerNoteMoyenne($avis, $voyageId = null) {
    $notes = [];
    
    foreach ($avis as $unAvis) {
        if ($voyageId !== null && $unAvis['voyage_id'] != $voyageId) {
            continue;
        }
        
        if ($unAvis['statut'] === 'publié') {
            $notes[] = $unAvis['note'];
        }
    }
    
    if (empty($notes)) {
        return 0;
    }
    
    return round(array_sum($notes) / count($notes), 1);
}

// Regrouper les avis par voyage
$avisByVoyage = [];
foreach ($avis as $unAvis) {
    if ($unAvis['statut'] === 'publié') {
        $avisByVoyage[$unAvis['voyage_id']][] = $unAvis;
    }
}

// Trier les voyages par nombre d'avis (décroissant)
uasort($avisByVoyage, function($a, $b) {
    return count($b) - count($a);
});
?>

<div class="page-container">
    <h1 class="page-title">Avis des voyageurs</h1>
    
    <?php if (empty($avis)): ?>
        <div class="no-reviews">
            <p>Aucun avis n'a été publié pour le moment.</p>
            <div class="centered-button">
                <a href="voyages.php" class="btn btn-primary">Découvrir nos voyages</a>
            </div>
        </div>
    <?php else: ?>
        <div class="reviews-stats">
            <div class="global-rating">
                <h2>Note moyenne globale</h2>
                <div class="big-rating">
                    <span class="big-rating-value"><?php echo calculerNoteMoyenne($avis); ?></span>
                    <div class="star-display">
                        <?php
                        $moyenneGlobale = calculerNoteMoyenne($avis);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= floor($moyenneGlobale)) {
                                echo '<span class="star full">★</span>';
                            } elseif ($i - 0.5 <= $moyenneGlobale) {
                                echo '<span class="star half">★</span>';
                            } else {
                                echo '<span class="star empty">☆</span>';
                            }
                        }
                        ?>
                    </div>
                    <div class="reviews-count">
                        <span><?php echo count($avis); ?> avis au total</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="reviews-by-destination">
            <h2>Avis par destination</h2>
            
            <?php foreach ($avisByVoyage as $voyageId => $voyageAvis): ?>
                <?php if (isset($voyages[$voyageId])): ?>
                    <div class="destination-reviews">
                        <div class="destination-header">
                            <h3><?php echo htmlspecialchars($voyages[$voyageId]['nom'] ?? 'Voyage #' . $voyageId); ?></h3>
                            <div class="destination-rating">
                                <div class="star-display">
                                    <?php
                                    $moyenne = calculerNoteMoyenne($avis, $voyageId);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($moyenne)) {
                                            echo '<span class="star full">★</span>';
                                        } elseif ($i - 0.5 <= $moyenne) {
                                            echo '<span class="star half">★</span>';
                                        } else {
                                            echo '<span class="star empty">☆</span>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-value"><?php echo $moyenne; ?> (<?php echo count($voyageAvis); ?> avis)</span>
                            </div>
                        </div>
                        
                        <div class="reviews-list">
                            <?php foreach ($voyageAvis as $unAvis): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <span class="reviewer-name"><?php echo htmlspecialchars($unAvis['user_prenom'] . ' ' . substr($unAvis['user_nom'], 0, 1) . '.'); ?></span>
                                            <span class="review-date"><?php echo date('d/m/Y', strtotime($unAvis['date'])); ?></span>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo $i <= $unAvis['note'] ? 'full' : 'empty'; ?>">
                                                    <?php echo $i <= $unAvis['note'] ? '★' : '☆'; ?>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($unAvis['commentaire'])): ?>
                                        <div class="review-comment">
                                            <p><?php echo nl2br(htmlspecialchars($unAvis['commentaire'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="view-more">
                            <a href="voyage-details.php?id=<?php echo $voyageId; ?>" class="btn btn-outline">Voir ce voyage</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-title {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--primary-color);
}

.no-reviews {
    text-align: center;
    padding: 3rem;
    background-color: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.reviews-stats {
    display: flex;
    justify-content: center;
    margin-bottom: 3rem;
}

.global-rating {
    text-align: center;
    padding: 2rem;
    background-color: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.global-rating h2 {
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: var(--primary-color);
}

.big-rating {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.big-rating-value {
    font-size: 3rem;
    font-weight: bold;
    color: var(--primary-color);
}

.star-display {
    display: flex;
    gap: 0.3rem;
    margin: 0.5rem 0;
}

.star {
    font-size: 1.5rem;
}

.star.full {
    color: var(--star-color, gold);
}

.star.half {
    position: relative;
    color: var(--star-color, gold);
}

.star.empty {
    color: var(--star-empty-color, #ddd);
}

.reviews-count {
    font-size: 0.9rem;
    color: var(--text-light);
}

.reviews-by-destination {
    margin-top: 3rem;
}

.reviews-by-destination h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: var(--primary-color);
}

.destination-reviews {
    background-color: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
}

.destination-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.destination-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.destination-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rating-value {
    font-size: 0.9rem;
    color: var(--text-light);
}

.reviews-list {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.review-card {
    background-color: var(--background-color);
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.reviewer-info {
    display: flex;
    flex-direction: column;
}

.reviewer-name {
    font-weight: bold;
    color: var(--primary-color);
}

.review-date {
    font-size: 0.8rem;
    color: var(--text-light);
}

.review-comment {
    margin-top: 1rem;
    color: var(--text-color);
}

.review-comment p {
    margin: 0;
    line-height: 1.6;
}

.view-more {
    text-align: center;
    margin-top: 1.5rem;
}

@media (min-width: 768px) {
    .reviews-list {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .reviews-list {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Mode sombre */
[data-theme="dark"] .star.empty {
    color: #555;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 