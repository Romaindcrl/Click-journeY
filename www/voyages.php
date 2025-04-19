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
                        <img src="<?php echo htmlspecialchars($voyage['image']); ?>" 
                             alt="<?php echo htmlspecialchars($voyage['nom']); ?>" 
                             class="voyage-image">
                        
                        <div class="voyage-content">
                            <h2 class="voyage-title"><?php echo htmlspecialchars($voyage['nom']); ?></h2>
                            
                            <!-- Affichage des étoiles pour la note moyenne -->
                            <div class="rating">
                                <?php
                                $noteMoyenne = isset($notesMoyennes[$voyage['id']]) ? $notesMoyennes[$voyage['id']] : 0;
                                $nbAvis = isset($avisParVoyage[$voyage['id']]) ? count($avisParVoyage[$voyage['id']]) : 0;
                                
                                // Afficher les étoiles
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $noteMoyenne) {
                                        echo '<span class="star filled">★</span>';
                                    } elseif ($i - 0.5 <= $noteMoyenne) {
                                        echo '<span class="star half-filled">★</span>';
                                    } else {
                                        echo '<span class="star">☆</span>';
                                    }
                                }
                                
                                echo '<span class="avis-count">(' . $nbAvis . ' avis)</span>';
                                ?>
                            </div>
                            
                            <p class="voyage-description"><?php echo htmlspecialchars(substr($voyage['description'], 0, 100)); ?>...</p>
                            
                            <div class="voyage-details">
                                <p class="voyage-prix">À partir de <span><?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</span></p>
                                <p class="voyage-duree"><i class="fas fa-clock"></i> <?php echo isset($voyage['duree']) ? $voyage['duree'] : '7'; ?> jours</p>
                            </div>
                            
                            <div class="voyage-buttons">
                                <a href="voyage-details.php?id=<?php echo $voyage['id']; ?>" class="btn btn-secondary">Voir détails</a>
                                <a href="personnalisation.php?id=<?php echo $voyage['id']; ?>" class="btn btn-primary">RÉSERVER</a>
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
    gap: 20px;
    padding: 20px;
}

.voyage-card {
    background-color: var(--card-bg);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    height: 560px;
}

.voyage-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.voyage-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.voyage-content {
    padding: 1.5rem;
    padding-bottom: 4.5rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    position: relative;
}

.voyage-title {
    margin-top: 0;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
    font-size: 1.3rem;
}

.voyage-description {
    color: var(--text-color);
    margin-bottom: 1rem;
    line-height: 1.4;
    font-size: 0.95rem;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.voyage-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.voyage-prix {
    font-size: 1.3rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 0;
}

.voyage-prix span {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.voyage-duree {
    color: var(--text-color);
    font-size: 1rem;
    margin-top: 0;
    margin-bottom: 0;
}

.voyage-buttons {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    width: 90%;
    position: absolute;
    bottom: 1.75rem;
    left: 50%;
    transform: translateX(-50%);
}

.voyage-buttons .btn {
    padding: 0.7rem 0.5rem;
    font-size: 0.85rem;
    flex: 1;
    text-align: center;
    transition: all 0.3s ease;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.voyage-buttons .btn-secondary {
    background-color: #f8f9fa;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}

.voyage-buttons .btn-secondary:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.voyage-buttons .btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.voyage-buttons .btn-primary:hover {
    background-color: var(--primary-hover, #3251AC);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(65, 105, 225, 0.3);
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
    // Sélectionner tous les boutons "Voir détails"
    const detailsButtons = document.querySelectorAll('.voyage-buttons .btn-secondary');
    
    // Ajouter un écouteur d'événements pour chaque bouton
    detailsButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Empêcher le comportement par défaut
            e.preventDefault();
            
            // Obtenir l'URL
            const url = this.getAttribute('href');
            
            // Rediriger vers l'URL
            window.location.href = url;
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 