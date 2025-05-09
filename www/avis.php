<?php
require_once __DIR__ . '/includes/header.php';

// Charger les avis
$avisFile = __DIR__ . '/../data/avis.json';
$avis = [];

if (file_exists($avisFile)) {
    $avisContent = file_get_contents($avisFile);
    $avisData = json_decode($avisContent, true);
    // S'assurer que nous accédons au tableau 'avis' dans le JSON
    if (isset($avisData['avis']) && is_array($avisData['avis'])) {
        $avis = $avisData['avis'];
    }
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
        // Vérifier que toutes les clés nécessaires existent
        if (!isset($unAvis['voyage_id']) || !isset($unAvis['statut']) || !isset($unAvis['note'])) {
            continue;
        }
        
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
    // Vérifier que toutes les clés nécessaires existent
    if (!isset($unAvis['voyage_id']) || !isset($unAvis['statut'])) {
        continue;
    }
    
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

<!-- Structure pour le modal d'avis - s'affichera avec JavaScript -->
<div id="review-modal" class="review-modal">
    <div class="review-modal-content">
        <span class="close">&times;</span>
        <h2>Votre avis compte !</h2>
        
        <form id="review-form">
            <input type="hidden" id="voyage-id" name="voyage_id" value="">
            
            <div class="rating-container">
                <p>Votre note :</p>
                <div class="rating-stars">
                    <span class="rating-star" data-value="1">★</span>
                    <span class="rating-star" data-value="2">★</span>
                    <span class="rating-star" data-value="3">★</span>
                    <span class="rating-star" data-value="4">★</span>
                    <span class="rating-star" data-value="5">★</span>
                </div>
                <input type="hidden" id="rating-value" name="note" value="0">
            </div>
            
            <textarea id="review-comment" name="commentaire" placeholder="Partagez votre expérience de voyage..."></textarea>
            
            <button type="submit" id="submit-review">Soumettre mon avis</button>
            
            <div id="review-message"></div>
        </form>
    </div>
</div>

<style>
/* Style spécifique à la page d'avis */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: 'Poppins', sans-serif;
}

.page-title {
    font-family: 'Amarante', serif;
    color: var(--rich-black);
    font-size: 2.5rem;
    text-align: center;
    margin-bottom: 2.5rem;
    position: relative;
    padding-bottom: 0.75rem;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--lapis-lazuli), var(--air-blue));
    border-radius: 3px;
}

.btn-primary {
    background-color: var(--lapis-lazuli);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    display: inline-block;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.btn-primary:hover {
    background-color: #224760;
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(45, 89, 119, 0.2);
}

.centered-button {
    text-align: center;
    margin-top: 2rem;
}
</style>

<script>
// Script pour gérer le modal d'avis
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionneurs
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    const stars = document.querySelectorAll('.rating-stars .rating-star');
    const ratingInput = document.getElementById('rating-value');
    const reviewForm = document.getElementById('review-form');
    const reviewMessage = document.getElementById('review-message');
    
    // Ouvrir le modal (fonction à appeler depuis le bouton "Laisser un avis")
    window.openReviewModal = function(voyageId) {
        document.getElementById('voyage-id').value = voyageId;
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Empêcher le défilement
    };
    
    // Fermer le modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Réactiver le défilement
        // Réinitialiser le formulaire
        reviewForm.reset();
        stars.forEach(star => star.classList.remove('active'));
        ratingInput.value = 0;
        reviewMessage.style.display = 'none';
        reviewMessage.className = '';
    });
    
    // Gestion des étoiles
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingInput.value = value;
            
            // Mettre à jour l'affichage des étoiles
            stars.forEach(s => {
                if (s.getAttribute('data-value') <= value) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    // Soumission du formulaire (à compléter avec l'envoi AJAX si nécessaire)
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validation simple
        if (ratingInput.value === '0') {
            showMessage('Veuillez attribuer une note.', 'error');
            return;
        }
        
        // Simuler un succès (à remplacer par l'appel AJAX réel)
        showMessage('Votre avis a été enregistré. Merci pour votre contribution !', 'success');
        
        // Après 2 secondes, fermer le modal
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Réinitialiser
            reviewForm.reset();
            stars.forEach(star => star.classList.remove('active'));
            ratingInput.value = 0;
            reviewMessage.style.display = 'none';
        }, 2000);
    });
    
    // Afficher un message
    function showMessage(text, type) {
        reviewMessage.textContent = text;
        reviewMessage.className = type;
        reviewMessage.style.display = 'block';
    }
    
    // Fermer le modal si on clique en dehors
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeBtn.click();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 