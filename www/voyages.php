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