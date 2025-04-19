<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Vérifier si le paramètre id est présent dans l'URL
$voyageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($voyageId === 0) {
    $_SESSION['flash_message'] = 'Aucun voyage sélectionné.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Récupérer les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';

if (!file_exists($voyagesFile)) {
    $voyagesFile = __DIR__ . '/data/voyages.json';
}

if (!file_exists($voyagesFile)) {
    $_SESSION['flash_message'] = 'Erreur: fichier de voyages introuvable.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Rechercher le voyage par ID
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyageId) { // Utiliser == au lieu de === pour comparer des chiffres qui peuvent être de types différents
        $voyage = $v;
        break;
    }
}

if (!$voyage) {
    $_SESSION['flash_message'] = 'Voyage non trouvé.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier et récupérer les données du formulaire
    $dateDepart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $nbParticipants = isset($_POST['nb_participants']) ? intval($_POST['nb_participants']) : 1;
    $prixTotal = isset($_POST['prix_total']) ? intval($_POST['prix_total']) : $voyage['prix'];
    
    // Débogage - Vérifier les valeurs soumises
    error_log("Formulaire soumis: date=" . $dateDepart . ", participants=" . $nbParticipants . ", prix=" . $prixTotal);
    
    if (empty($dateDepart)) {
        $_SESSION['flash_message'] = 'Veuillez sélectionner une date de départ.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $activites = [];
        
        // Récupérer les activités sélectionnées
        if (isset($voyage['activites']) && is_array($voyage['activites'])) {
            foreach ($voyage['activites'] as $index => $activite) {
                $activiteId = 'activite_' . $index;
                if (isset($_POST[$activiteId])) {
                    $activites[] = [
                        'nom' => $activite['nom'],
                        'prix' => $activite['prix']
                    ];
                }
            }
        }
        
        // Stocker les informations de réservation dans la session
        $_SESSION['reservation'] = [
            'voyage_id' => $voyageId,
            'voyage_nom' => $voyage['nom'],
            'voyage_image' => $voyage['image'],
            'date_depart' => $dateDepart,
            'nb_participants' => $nbParticipants,
            'activites' => $activites,
            'prix_total' => $prixTotal
        ];
        
        // Débogage - Vérifier la session avant redirection
        error_log("Session reservation créée. Redirection vers paiement.php");
        
        // Rediriger vers la page de paiement
        header('Location: paiement.php');
        exit;
    }
}

// Générer les options pour le nombre de participants
$participantsOptions = '';
for ($i = 1; $i <= 10; $i++) {
    $selected = ($i === 1) ? 'selected' : '';
    $participantsOptions .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
}

// Date minimale de départ (demain)
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<div class="page-container">
    <h1 class="page-title">Personnalisez votre voyage</h1>
    
    <?php if ($voyage): ?>
    <div class="personnalisation-container">
        <div class="voyage-details">
            <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['nom']) ?>" class="voyage-image">
            
            <h2 class="voyage-title"><?= htmlspecialchars($voyage['nom']) ?></h2>
            <p class="voyage-description"><?= htmlspecialchars($voyage['description']) ?></p>
            
            <form id="personnalisation-form" method="post" action="paiement.php">
                <input type="hidden" name="voyage_id" value="<?= $voyageId ?>">
                
                <div class="form-section">
                    <h3>Informations de base</h3>
                    
                    <div class="input-group">
                        <label for="date_depart">Date de départ:</label>
                        <input type="date" id="date_depart" name="date_depart" class="form-control" min="<?= $tomorrow ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="nb_participants">Nombre de participants:</label>
                        <select id="nb_participants" name="nb_participants" class="form-control">
                            <?= $participantsOptions ?>
                        </select>
                    </div>
                </div>
                
                <?php if (isset($voyage['activites']) && !empty($voyage['activites'])): ?>
                <div class="form-section">
                    <h3>Activités optionnelles</h3>
                    <p class="mb-3">Sélectionnez les activités que vous souhaitez ajouter à votre voyage :</p>
                    
                    <div class="activity-list">
                        <?php foreach ($voyage['activites'] as $index => $activite): ?>
                        <div class="activity-checkbox">
                            <input type="checkbox" id="activite_<?= $index ?>" name="activite_<?= $index ?>" data-price="<?= $activite['prix'] ?>">
                            <label for="activite_<?= $index ?>"><?= htmlspecialchars($activite['nom']) ?></label>
                            <div class="activity-price"><?= number_format($activite['prix'], 0, ',', ' ') ?> €</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            
                <div class="order-recap">
                    <h3 class="recap-title">Récapitulatif de votre voyage</h3>
                    
                    <div class="recap-content">
                        <div class="recap-item">
                            <span>Voyage</span>
                            <strong><?= htmlspecialchars($voyage['nom']) ?></strong>
                        </div>
                        
                        <div class="recap-item">
                            <span>Prix de base</span>
                            <strong id="price-per-person" data-base="<?= $voyage['prix'] ?>"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</strong>
                        </div>
                        
                        <div class="recap-item">
                            <span>Participants</span>
                            <strong><span id="nb-participants">1</span></strong>
                        </div>
                        
                        <!-- Affichage dynamique des activités sélectionnées -->
                        <div id="selected-activities-container" style="display: none; margin-top: 1rem;">
                            <h4>Activités sélectionnées</h4>
                            <div id="selected-activities-list">
                                <!-- Les activités sélectionnées seront affichées ici par JavaScript -->
                            </div>
                        </div>
                        
                        <div class="recap-separator"></div>
                        
                        <div class="recap-item total-price">
                            <span>Prix total</span>
                            <strong><span id="total-price"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</span></strong>
                        </div>
                    </div>
                    
                    <input type="hidden" id="prix_total_input" name="prix_total" value="<?= $voyage['prix'] ?>">
                    <button type="submit" class="btn btn-primary btn-reserver">Passer au paiement</button>
                    
                    <div class="text-center mt-3">
                        <a href="voyages.php" class="btn btn-link">Retour aux voyages</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-danger">
        Voyage non trouvé. <a href="voyages.php">Retour à la liste des voyages</a>
    </div>
    <?php endif; ?>
</div>

<style>
.personnalisation-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.voyage-details {
    background-color: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.voyage-image {
    width: 100%;
    height: auto;
    max-height: 400px;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    object-fit: cover;
}

.voyage-title {
    color: #4169E1;
    font-size: 1.8rem;
    margin-bottom: 1rem;
}

.voyage-description {
    color: #333;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: #4169E1;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
}

.input-group {
    margin-bottom: 1.5rem;
}

.input-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #444;
}

.form-control {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
}

/* Style spécifique pour le champ date */
input[type="date"] {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 2.5rem;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
    background-repeat: no-repeat;
    background-position: 98% center;
    background-size: 20px;
    position: relative;
}

/* Masquer les contrôles de calendrier des navigateurs */
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    color: transparent;
    background: transparent;
    z-index: 1;
    cursor: pointer;
}

.activity-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.8rem;
}

.activity-checkbox {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.activity-checkbox:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.activity-checkbox input {
    margin-right: 1rem;
    width: 1.2rem;
    height: 1.2rem;
}

.activity-checkbox label {
    flex-grow: 1;
    margin-bottom: 0;
    cursor: pointer;
}

.activity-price {
    font-weight: 600;
    color: #4169E1;
}

.order-recap {
    background-color: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
}

.recap-title {
    color: #4169E1;
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.recap-content {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.recap-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.recap-separator {
    height: 1px;
    background-color: #eee;
    margin: 0.5rem 0;
}

.total-price {
    font-size: 1.2rem;
    font-weight: 600;
    color: #4169E1;
}

.btn-reserver {
    width: 100%;
    padding: 1rem;
    margin-top: 1.5rem;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 1.1rem;
    background-color: #4169E1;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-reserver:hover {
    background-color: #3251AC;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(65, 105, 225, 0.3);
}

.btn-link {
    display: inline-block;
    margin-top: 1rem;
    color: #4169E1;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.text-center {
    text-align: center;
}

.mt-3 {
    margin-top: 1.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (min-width: 768px) {
    .personnalisation-container {
        grid-template-columns: 2fr 1fr;
    }
    
    .activity-list {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .order-recap {
        margin-top: 0;
    }
}

.activity-item {
    margin: 8px 0;
    padding: 8px 0;
    border-bottom: 1px dashed #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

#selected-activities-container h4 {
    font-size: 1.1rem;
    color: #4169E1;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.activity-checkbox.selected {
    background-color: rgba(65, 105, 225, 0.1);
    border: 1px solid #4169E1;
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nbParticipantsSelect = document.getElementById('nb_participants');
    const pricePerPerson = document.getElementById('price-per-person');
    const nbParticipantsDisplay = document.getElementById('nb-participants');
    const totalPriceDisplay = document.getElementById('total-price');
    const totalPriceInput = document.getElementById('prix_total_input');
    const activityCheckboxes = document.querySelectorAll('input[type="checkbox"][data-price]');
    const selectedActivitiesContainer = document.getElementById('selected-activities-container');
    const selectedActivitiesList = document.getElementById('selected-activities-list');
    
    if (!nbParticipantsSelect || !pricePerPerson || !nbParticipantsDisplay || !totalPriceDisplay || !totalPriceInput) {
        console.error('One or more elements not found');
        return;
    }
    
    const basePrice = parseInt(pricePerPerson.getAttribute('data-base'), 10);
    
    // Formatter les nombres au format français
    function formatPrice(price) {
        return price.toLocaleString('fr-FR') + ' €';
    }
    
    // Mettre à jour la liste des activités sélectionnées
    function updateSelectedActivities() {
        // Vider la liste
        selectedActivitiesList.innerHTML = '';
        
        // Compteur pour savoir si des activités ont été sélectionnées
        let activitiesSelected = false;
        const participants = parseInt(nbParticipantsSelect.value, 10);
        
        // Parcourir toutes les cases à cocher
        activityCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                activitiesSelected = true;
                const activityName = checkbox.nextElementSibling.textContent.trim();
                const activityPrice = parseInt(checkbox.getAttribute('data-price'), 10);
                const totalActivityPrice = activityPrice * participants;
                
                // Créer un élément pour l'activité
                const activityItem = document.createElement('div');
                activityItem.className = 'activity-item';
                activityItem.innerHTML = `
                    <div class="recap-item">
                        <span>${activityName}</span>
                        <strong>${formatPrice(totalActivityPrice)}</strong>
                    </div>
                `;
                
                // Ajouter l'élément à la liste
                selectedActivitiesList.appendChild(activityItem);
            }
        });
        
        // Afficher ou masquer le conteneur en fonction des sélections
        selectedActivitiesContainer.style.display = activitiesSelected ? 'block' : 'none';
    }
    
    // Mettre à jour le prix total
    function updateTotalPrice() {
        const participants = parseInt(nbParticipantsSelect.value, 10);
        
        // Mettre à jour l'affichage du nombre de participants
        nbParticipantsDisplay.textContent = participants;
        
        // Calculer le prix de base
        let total = basePrice * participants;
        
        // Ajouter le prix des activités
        activityCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const activityPrice = parseInt(checkbox.getAttribute('data-price'), 10);
                total += activityPrice * participants;
                
                // Ajouter une classe pour l'animation
                checkbox.closest('.activity-checkbox').classList.add('selected');
            } else {
                checkbox.closest('.activity-checkbox').classList.remove('selected');
            }
        });
        
        // Mettre à jour l'affichage du prix total
        totalPriceDisplay.textContent = formatPrice(total);
        
        // Mettre à jour le champ caché
        totalPriceInput.value = total;
        
        // Mettre à jour la liste des activités
        updateSelectedActivities();
    }
    
    // Ajouter les écouteurs d'événements
    nbParticipantsSelect.addEventListener('change', updateTotalPrice);
    
    activityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    // Initialiser les calculs
    updateTotalPrice();
    
    // Vérifier la soumission du formulaire
    const form = document.getElementById('personnalisation-form');
    form.addEventListener('submit', function(e) {
        // Vérifier que la date est sélectionnée
        const dateInput = document.getElementById('date_depart');
        if (!dateInput.value) {
            e.preventDefault();
            alert('Veuillez sélectionner une date de départ.');
            dateInput.focus();
        }
    });
});
</script> 