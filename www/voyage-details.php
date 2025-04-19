<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/header.php';

// Vérifier si l'ID du voyage est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = 'Voyage non trouvé';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

$voyage_id = intval($_GET['id']);
$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
$user_logged_in = isset($_SESSION['user']);

// Charger les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';
// Utiliser un chemin alternatif si le premier ne fonctionne pas
if (!file_exists($voyagesFile)) {
    $voyagesFile = __DIR__ . '/data/voyages.json';
}

$voyages = [];

if (file_exists($voyagesFile)) {
    $voyagesContent = file_get_contents($voyagesFile);
    $voyagesData = json_decode($voyagesContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // En cas d'erreur de décodage JSON, enregistrer l'erreur
        error_log('Erreur JSON dans voyage-details.php: ' . json_last_error_msg());
    }
    $voyages = $voyagesData['voyages'] ?? [];
}

// Trouver le voyage spécifié
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyage_id) {
        $voyage = $v;
        break;
    }
}

if (!$voyage) {
    $_SESSION['flash_message'] = 'Voyage non trouvé';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Récupérer les images supplémentaires pour la galerie
// Au lieu de créer des dossiers qui pourraient causer des erreurs de permission,
// nous allons simplement utiliser des images qui existent déjà
$baseImagePath = 'src/images';
$defaultImage = $voyage['image']; // Image par défaut si les autres n'existent pas

// Vérifier si le dossier d'images 4L existe
$imageDirectory = __DIR__ . '/src/images/4L';
$has4LImages = file_exists($imageDirectory);

// Définir des images qui existent certainement
$galerieImages = [
    $defaultImage // L'image principale (qui existe certainement)
];

// Ajouter d'autres images seulement si elles existent
if ($has4LImages) {
    $additionalImages = [
        'src/images/4L/destination1.jpg',
        'src/images/4L/destination2.jpg',
        'src/images/4L/destination3.jpg'
    ];
    
    foreach ($additionalImages as $img) {
        if (file_exists(__DIR__ . '/' . $img)) {
            $galerieImages[] = $img;
        } else {
            // Utiliser l'image par défaut si l'image n'existe pas
            $galerieImages[] = $defaultImage;
        }
    }
} else {
    // Si le dossier 4L n'existe pas, ajouter l'image par défaut plusieurs fois
    for ($i = 0; $i < 3; $i++) {
        $galerieImages[] = $defaultImage;
    }
}

// Charger les avis pour ce voyage
$avisFile = __DIR__ . '/../data/avis.json';
// Utiliser un chemin alternatif si le premier ne fonctionne pas
if (!file_exists($avisFile)) {
    $avisFile = __DIR__ . '/data/avis.json';
}
$avis = [];

if (file_exists($avisFile)) {
    $avisContent = file_get_contents($avisFile);
    $avisData = json_decode($avisContent, true);
    
    if (isset($avisData['avis'])) {
        $avis = $avisData['avis'];
    }
}

// Filtrer les avis pour ce voyage
$voyage_avis = array_filter($avis, function($a) use ($voyage_id) {
    return $a['voyage_id'] == $voyage_id && $a['statut'] === 'publié';
});

// Calculer la note moyenne
$total_notes = 0;
$nombre_avis = count($voyage_avis);
foreach ($voyage_avis as $a) {
    $total_notes += $a['note'];
}
$note_moyenne = $nombre_avis > 0 ? round($total_notes / $nombre_avis, 1) : 0;

// Vérifier si l'utilisateur a déjà laissé un avis
$user_a_deja_note = false;
$avis_utilisateur = null;
if ($user_logged_in) {
    foreach ($voyage_avis as $a) {
        if ($a['user_id'] == $user_id) {
            $user_a_deja_note = true;
            $avis_utilisateur = $a;
            break;
        }
    }
}

// Vérifier si l'utilisateur a effectué ce voyage (a une commande terminée pour ce voyage)
$user_peut_noter = false;
if ($user_logged_in && !$user_a_deja_note) {
    $commandesFile = __DIR__ . '/../data/commandes.json';
    if (!file_exists($commandesFile)) {
        $commandesFile = __DIR__ . '/data/commandes.json';
    }
    
    $commandes = [];
    
    if (file_exists($commandesFile)) {
        $commandesContent = file_get_contents($commandesFile);
        $commandesData = json_decode($commandesContent, true);
        
        if (isset($commandesData['commandes'])) {
            $commandes = $commandesData['commandes'];
        }
    }
    
    foreach ($commandes as $commande) {
        if ($commande['user_id'] == $user_id && $commande['voyage_id'] == $voyage_id) {
            // Vérifier si la date de retour existe et est dans le passé
            if (isset($commande['date_retour']) && !empty($commande['date_retour'])) {
                if (strtotime($commande['date_retour']) < time()) {
                    $user_peut_noter = true;
                    break;
                }
            }
        }
    }
}

// Traiter la soumission d'un avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_avis']) && $user_logged_in && $user_peut_noter) {
    $note = isset($_POST['note']) ? intval($_POST['note']) : 0;
    $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
    
    if ($note >= 1 && $note <= 5 && !empty($commentaire)) {
        // Récupérer les informations de l'utilisateur
        $users = [];
        $usersFile = __DIR__ . '/../data/users.json';
        
        if (file_exists($usersFile)) {
            $usersContent = file_get_contents($usersFile);
            $usersData = json_decode($usersContent, true);
            
            if (isset($usersData['users'])) {
                $users = $usersData['users'];
            }
        }
        
        $user_prenom = $_SESSION['user']['prenom'] ?? 'Utilisateur';
        $user_nom = $_SESSION['user']['nom'] ?? '';
        
        // Créer un nouvel avis
        $nouvel_avis = [
            'id' => 'avis' . time() . rand(100, 999),
            'voyage_id' => $voyage_id,
            'user_id' => $user_id,
            'user_prenom' => $user_prenom,
            'user_nom' => $user_nom,
            'note' => $note,
            'commentaire' => $commentaire,
            'date' => date('Y-m-d H:i:s'),
            'statut' => 'publié'
        ];
        
        // Ajouter l'avis à la liste
        $avisData['avis'][] = $nouvel_avis;
        
        // Sauvegarder les avis mis à jour
        file_put_contents($avisFile, json_encode($avisData, JSON_PRETTY_PRINT));
        
        // Mettre à jour les variables de la page
        $voyage_avis[] = $nouvel_avis;
        $user_a_deja_note = true;
        $user_peut_noter = false;
        $nombre_avis++;
        $total_notes += $note;
        $note_moyenne = round($total_notes / $nombre_avis, 1);
        
        $_SESSION['flash_message'] = 'Votre avis a été publié avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: voyage-details.php?id=' . $voyage_id);
        exit;
    } else {
        $_SESSION['flash_message'] = 'Veuillez fournir une note valide (1-5) et un commentaire.';
        $_SESSION['flash_type'] = 'error';
    }
}

// Récupérer la durée du voyage
$duree = $voyage['duree'] ?? 7; // Durée par défaut si non spécifiée

// Vérifier si l'utilisateur a réservé ce voyage
$userHasBooked = false;
$commandesFile = __DIR__ . '/../data/commandes.json';
if (!file_exists($commandesFile)) {
    $commandesFile = __DIR__ . '/data/commandes.json';
}

$commandes = [];
if ($user_logged_in && file_exists($commandesFile)) {
    $commandesContent = file_get_contents($commandesFile);
    $commandesData = json_decode($commandesContent, true);
    $commandes = $commandesData['commandes'] ?? [];
    
    foreach ($commandes as $commande) {
        if ($commande['user_id'] == $user_id && $commande['voyage_id'] == $voyage_id && $commande['statut'] === 'confirmé') {
            $userHasBooked = true;
            break;
        }
    }
}

// Fonction pour formatter les dates
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}
?>

<div class="page-container">
    <h1 class="page-title"><?= htmlspecialchars($voyage['nom']) ?></h1>
    
    <div class="voyage-detail-container">
        <div class="voyage-detail-main">
            <!-- Image principale améliorée -->
            <div class="voyage-image-container">
                <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['nom']) ?>" class="voyage-detail-image">
            </div>
            
            <!-- Galerie de photos -->
            <div class="voyage-section">
                <h2 class="section-title">Galerie photos</h2>
                <div class="voyage-gallery">
                    <?php 
                    // N'afficher que les 4 premières images au maximum pour éviter une galerie trop grande
                    $maxImages = min(count($galerieImages), 4);
                    for ($i = 0; $i < $maxImages; $i++): 
                        $image = $galerieImages[$i];
                    ?>
                    <div class="gallery-item">
                        <img src="<?= htmlspecialchars($image) ?>" alt="Photo <?= htmlspecialchars($voyage['nom']) ?> <?= $i+1 ?>" class="gallery-image">
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Description améliorée -->
            <div class="voyage-section">
                <h2 class="section-title">Description</h2>
                <div class="description-content">
                    <p class="voyage-description"><?= nl2br(htmlspecialchars($voyage['description'])) ?></p>
                    
                    <div class="description-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Destination</h4>
                                <p><?= htmlspecialchars($voyage['nom']) ?></p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <h4>Durée</h4>
                                <p><?= htmlspecialchars($duree) ?> jours</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Groupe</h4>
                                <p>De 1 à 10 personnes</p>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-star"></i>
                            <div>
                                <h4>Note moyenne</h4>
                                <p><?= $note_moyenne ?>/5 (<?= $nombre_avis ?> avis)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Programme du voyage -->
            <div class="voyage-section">
                <h2 class="section-title">Programme du voyage</h2>
                <div class="itinerary">
                    <div class="itinerary-day">
                        <div class="day-number">Jour 1</div>
                        <div class="day-content">
                            <h3>Arrivée et découverte</h3>
                            <p>Arrivée à destination, installation à l'hôtel et première découverte des environs. Dîner de bienvenue et présentation du programme.</p>
                        </div>
                    </div>
                    <div class="itinerary-day">
                        <div class="day-number">Jour 2-<?= $duree-2 ?></div>
                        <div class="day-content">
                            <h3>Exploration et aventures</h3>
                            <p>Découverte des sites incontournables, activités en fonction des options choisies, immersion dans la culture locale.</p>
                        </div>
                    </div>
                    <div class="itinerary-day">
                        <div class="day-number">Jour <?= $duree-1 ?></div>
                        <div class="day-content">
                            <h3>Temps libre et détente</h3>
                            <p>Journée libre pour explorer selon vos envies, faire du shopping ou simplement vous détendre. Dîner d'au revoir en soirée.</p>
                        </div>
                    </div>
                    <div class="itinerary-day">
                        <div class="day-number">Jour <?= $duree ?></div>
                        <div class="day-content">
                            <h3>Retour</h3>
                            <p>Petit-déjeuner à l'hôtel, derniers moments sur place et retour.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Détails du voyage avec call-to-action -->
            <div class="voyage-section">
                <h2 class="section-title">Détails du voyage</h2>
                <div class="voyage-details-grid">
                    <div class="detail-card">
                        <div class="detail-icon"><i class="fas fa-bed"></i></div>
                        <h3>Hébergement</h3>
                        <p>Hôtels 3 à 4 étoiles sélectionnés pour leur confort et leur emplacement.</p>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon"><i class="fas fa-utensils"></i></div>
                        <h3>Repas</h3>
                        <p>Petits déjeuners inclus. Options demi-pension ou pension complète disponibles.</p>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon"><i class="fas fa-plane"></i></div>
                        <h3>Transport</h3>
                        <p>Vols non inclus. Transferts locaux et déplacements sur place compris.</p>
                    </div>
                    <div class="detail-card">
                        <div class="detail-icon"><i class="fas fa-map-marked-alt"></i></div>
                        <h3>Visites</h3>
                        <p>Guide francophone et entrées aux sites mentionnés dans le programme inclus.</p>
                    </div>
                </div>
                
                <div class="voyage-details-price">
                    <div class="price-tag">
                        <div class="price-label">Prix par personne</div>
                        <div class="price-value"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</div>
                    </div>
                    <div class="detail-actions">
                        <a href="personnalisation.php?id=<?= $voyage_id ?>" class="btn btn-primary btn-large">RÉSERVER MAINTENANT</a>
                    </div>
                </div>
            </div>
            
            <!-- Avis voyageurs -->
            <div class="voyage-section">
                <h2 class="section-title">Avis voyageurs</h2>
                
                <?php if ($nombre_avis > 0): ?>
                <div class="rating-summary">
                    <div class="rating-average">
                        <div class="average-rating"><?= $note_moyenne ?><span>/5</span></div>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $note_moyenne): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $note_moyenne): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-count"><?= $nombre_avis ?> avis</div>
                    </div>
                </div>
                
                <div class="reviews-list">
                    <?php foreach ($voyage_avis as $avis): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-name"><?= htmlspecialchars($avis['user_prenom']) ?> <?= substr(htmlspecialchars($avis['user_nom']), 0, 1) ?>.</div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa<?= $i <= $avis['note'] ? 's' : 'r' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="review-date"><?= formatDate($avis['date']) ?></div>
                        </div>
                        <div class="review-content">
                            <p><?= nl2br(htmlspecialchars($avis['commentaire'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-reviews">
                    <p>Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                </div>
                <?php endif; ?>
                
                <?php if ($user_logged_in && $user_peut_noter): ?>
                <div class="leave-review">
                    <h3>Laissez votre avis</h3>
                    <form action="" method="post" class="review-form">
                        <div class="form-group">
                            <label>Votre note</label>
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="note" value="<?= $i ?>" />
                                <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="commentaire">Votre commentaire</label>
                            <textarea id="commentaire" name="commentaire" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_avis" class="btn btn-primary">Publier mon avis</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar avec formulaire de réservation rapide -->
        <div class="voyage-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3>Réserver ce voyage</h3>
                </div>
                <div class="sidebar-content">
                    <div class="sidebar-price">
                        <span>À partir de</span>
                        <div class="price"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</div>
                        <span>par personne</span>
                    </div>
                    
                    <div class="sidebar-info">
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Durée: <?= $duree ?> jours</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <span>Groupe: 1-10 personnes</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-language"></i>
                            <span>Guide francophone</span>
                        </div>
                    </div>
                    
                    <a href="personnalisation.php?id=<?= $voyage_id ?>" class="btn btn-primary btn-block">PERSONNALISER CE VOYAGE</a>
                    
                    <div class="sidebar-contact">
                        <p>Des questions ? Contactez-nous</p>
                        <div class="contact-info">
                            <i class="fas fa-phone"></i>
                            <span>01 23 45 67 89</span>
                        </div>
                        <div class="contact-info">
                            <i class="fas fa-envelope"></i>
                            <span>contact@click-journey.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles de base pour la page détails */
.voyage-detail-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

/* Image principale */
.voyage-image-container {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.voyage-detail-image {
    width: 100%;
    height: auto;
    display: block;
}

/* Sections */
.voyage-section {
    background-color: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.section-title {
    color: var(--primary-color);
    font-size: 1.6rem;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f0f0f0;
}

/* Description */
.description-content {
    color: #333;
}

.voyage-description {
    line-height: 1.8;
    font-size: 1.05rem;
    margin-bottom: 2rem;
}

.description-highlights {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.highlight-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.highlight-item i {
    font-size: 2rem;
    color: var(--primary-color);
}

.highlight-item h4 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    color: #555;
}

.highlight-item p {
    margin: 0;
    font-weight: 600;
    color: #333;
}

/* Galerie */
.voyage-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.gallery-item {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
}

.gallery-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

/* Programme */
.itinerary {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.itinerary-day {
    display: flex;
    gap: 1.5rem;
    position: relative;
}

.itinerary-day:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 25px;
    top: 50px;
    height: calc(100% + 1.5rem);
    width: 2px;
    background-color: #e0e0e0;
}

.day-number {
    flex: 0 0 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    font-weight: 600;
    z-index: 1;
}

.day-content {
    flex: 1;
}

.day-content h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
    font-size: 1.25rem;
}

.day-content p {
    margin: 0;
    line-height: 1.6;
    color: #555;
}

/* Détails du voyage */
.voyage-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.detail-card {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.detail-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.detail-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.detail-card h3 {
    margin-top: 0;
    margin-bottom: 0.75rem;
    color: #333;
    font-size: 1.25rem;
}

.detail-card p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.voyage-details-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f5f5f5;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.price-tag {
    display: flex;
    flex-direction: column;
}

.price-label {
    font-size: 1rem;
    color: #666;
}

.price-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

.detail-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Avis */
.rating-summary {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.rating-average {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.average-rating {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.average-rating span {
    font-size: 1.5rem;
    color: #999;
}

.rating-stars {
    display: flex;
    color: #ffc107;
    font-size: 1.5rem;
}

.rating-count {
    color: #999;
    font-size: 0.9rem;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-card {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}

.reviewer-name {
    font-weight: 600;
    color: #333;
}

.review-rating {
    color: #ffc107;
}

.review-date {
    color: #999;
    font-size: 0.85rem;
}

.review-content p {
    margin: 0;
    line-height: 1.6;
    color: #555;
}

.no-reviews {
    text-align: center;
    padding: 2rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    color: #666;
}

.leave-review {
    margin-top: 2rem;
    border-top: 1px solid #eee;
    padding-top: 2rem;
}

.leave-review h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.review-form .form-group {
    margin-bottom: 1.5rem;
}

.review-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.review-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    resize: vertical;
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: all 0.2s ease;
    margin: 0 0.2rem;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #ffc107;
}

/* Sidebar */
.voyage-sidebar {
    position: sticky;
    top: 2rem;
}

.sidebar-card {
    background-color: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    max-width: 300px;
    margin: 0 auto;
}

.sidebar-header {
    background-color: var(--primary-color);
    color: white;
    padding: 1.2rem;
    text-align: center;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 1.3rem;
}

.sidebar-content {
    padding: 1.5rem;
}

.sidebar-price {
    text-align: center;
    margin-bottom: 1.5rem;
}

.sidebar-price span {
    color: #666;
}

.sidebar-price .price {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1.2;
}

.sidebar-info {
    margin-bottom: 1.5rem;
    text-align: center;
}

.sidebar-info .info-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #555;
}

.sidebar-info .info-item i {
    color: var(--primary-color);
    font-size: 1.25rem;
    width: 20px;
    text-align: center;
}

.btn-block {
    display: block;
    width: 80%;
    margin: 0 auto;
    text-align: center;
    padding: 0.8rem 1rem;
    font-size: 1rem;
    border-radius: 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.sidebar-contact {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
    text-align: center;
}

.sidebar-contact p {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #666;
}

.contact-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.contact-info i {
    color: var(--primary-color);
}

/* Responsive */
@media (min-width: 768px) {
    .voyage-detail-container {
        grid-template-columns: 3fr 1fr;
    }
}
</style>

<script>
// Script pour s'assurer que les boutons de réservation fonctionnent correctement
document.addEventListener('DOMContentLoaded', function() {
    // Activer les boutons de réservation
    const bookingButtons = document.querySelectorAll('a[href^="personnalisation.php"]');
    bookingButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            console.log('Redirection vers:', url);
            window.location.href = url;
        });
    });

    // Debug
    console.log('Nombre de boutons de réservation trouvés:', bookingButtons.length);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>