<?php
require_once __DIR__ . '/includes/header.php';
// Utiliser la fonction check_auth optionnellement sans bloquer l'accès
// require_once __DIR__ . '/check_auth.php';
// L'utilisateur n'a pas besoin d'être connecté pour voir les voyages
// checkAuth();

// Charger les données des voyages
$voyagesFile = __DIR__ . '/../data/voyages.json';
$voyages = [];

if (file_exists($voyagesFile)) {
    $voyagesContent = file_get_contents($voyagesFile);
    $voyagesData = json_decode($voyagesContent, true);
    // Correction de l'accès aux données
    $voyages = $voyagesData['voyages'] ?? [];
}

// Initialiser les tableaux pour les avis et les notes moyennes
$avisParVoyage = [];
$notesMoyennes = [];

// Charger les avis s'ils existent
$avisFile = __DIR__ . '/../data/avis.json';
if (file_exists($avisFile)) {
    $avisContent = file_get_contents($avisFile);
    $avisData = json_decode($avisContent, true);
    
    if (isset($avisData['avis'])) {
        foreach ($avisData['avis'] as $avis) {
            if ($avis['statut'] === 'publié') {
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

// Afficher un message flash s'il existe
if (isset($_SESSION['flash'])) {
    echo '<div class="flash-message">' . $_SESSION['flash'] . '</div>';
    unset($_SESSION['flash']);
}
?>

<div class="page-container">
    <h1 class="page-title">Nos Voyages</h1>
    
    <div class="voyages-grid">
        <?php if (empty($voyages)): ?>
            <p class="no-voyages">Aucun voyage n'est disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($voyages as $voyage): ?>
                <?php if (isset($voyage['disponible']) ? $voyage['disponible'] : true): ?>
                    <div class="voyage-card">
                        <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['nom']) ?>" class="voyage-image">
                        <div class="voyage-content">
                            <h3 class="voyage-title"><?= htmlspecialchars($voyage['nom']) ?></h3>
                            <p class="voyage-description"><?= htmlspecialchars($voyage['description']) ?></p>
                            
                            <div class="voyage-info">
                                <div class="voyage-price">
                                    À partir de <?= number_format($voyage['prix'], 0, ',', ' ') ?> <span>€</span>
                                </div>
                                
                                <div class="voyage-duree">
                                    <i class="fas fa-clock"></i>
                                    <span><?= isset($voyage['duree']) ? $voyage['duree'] : 7 ?> jours</span>
                                </div>
                                
                                <div class="voyage-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <span class="voyage-rating-text">(<?= rand(5, 30) ?> avis)</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="voyage-footer">
                            <div class="voyage-buttons">
                                <a href="voyage-details.php?id=<?= $voyage['id'] ?>" class="btn-details">Voir détails</a>
                                <a href="personnalisation.php?id=<?= $voyage['id'] ?>" class="btn-reserve">Réserver</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.voyages-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    padding: 30px;
    margin-bottom: 50px;
}

.voyage-card {
    background-color: white;
    border-radius: 15px;
    overflow: visible;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: auto;
    min-height: 650px;
    position: relative;
    margin-bottom: 40px;
    padding-bottom: 80px;
}

.voyage-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
}

.voyage-image {
    width: 100%;
    height: 240px;
    object-fit: cover;
}

.voyage-content {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.voyage-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 12px;
    line-height: 1.3;
}

.voyage-description {
    font-size: 1rem;
    color: var(--text-color);
    margin-bottom: 20px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.voyage-footer {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-bottom: 15px;
    position: absolute;
    bottom: 10px;
    left: 20px;
    right: 20px;
}

.voyage-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.voyage-price span {
    font-size: 1.1rem;
    font-weight: 500;
}

.voyage-duree {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
    color: var(--text-light);
    margin-bottom: 5px;
}

.voyage-duree i {
    color: var(--primary-color);
}

.voyage-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.voyage-rating i {
    color: #FFD700;
    font-size: 1.1rem;
}

.voyage-rating-text {
    font-size: 0.9rem;
    color: var(--text-light);
}

.voyage-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 12px;
    z-index: 100;
    position: relative;
}

.btn-details, .btn-reserve {
    padding: 10px 12px;
    text-align: center;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    letter-spacing: 0.5px;
    display: block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.btn-details {
    background-color: var(--background-color);
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}

.btn-reserve {
    background-color: var(--primary-color);
    color: white;
    border: 1px solid var(--primary-color);
}

.btn-details:hover, .btn-reserve:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.rating {
    display: flex;
    align-items: center;
    margin: 0.5rem 0;
}

.star {
    color: #ddd;
    font-size: 1.2rem;
}

.star.filled {
    color: #ffb400;
}

.star.half-filled {
    color: #ffb400;
    position: relative;
}

.avis-count {
    margin-left: 0.5rem;
    font-size: 0.9rem;
    color: #777;
}

.voyage-info {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-bottom: 10px;
}

/* Media queries pour assurer la responsivité */
@media (max-width: 1200px) {
    .voyages-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .voyages-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .voyages-grid {
        grid-template-columns: 1fr;
    }
    
    .voyage-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Assurer que les liens fonctionnent correctement
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons
    const allButtons = document.querySelectorAll('.btn-details, .btn-reserve');
    
    // Ajouter un écouteur d'événements pour chaque bouton
    allButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Obtenir l'URL
            const url = this.getAttribute('href');
            
            // Rediriger vers l'URL
            window.location.href = url;
            
            // Ajouter un log pour déboguer
            console.log('Navigation vers: ' + url);
        });
    });
    
    // Fixer les problèmes de z-index et de clics
    const voyageCards = document.querySelectorAll('.voyage-card');
    voyageCards.forEach(card => {
        // S'assurer que la carte a un z-index normal
        card.style.zIndex = "1";
        
        // S'assurer que les boutons dans cette carte ont un z-index plus élevé
        const buttons = card.querySelectorAll('.btn-details, .btn-reserve');
        buttons.forEach(button => {
            button.style.zIndex = "5";
            button.style.position = "relative";
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 