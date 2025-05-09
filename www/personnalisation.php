<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();
require_once __DIR__ . '/includes/header.php';

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
            
            <form id="personnalisation-form" method="post" action="panier.php">
                <input type="hidden" name="voyage_id" value="<?= $voyageId ?>">
                
                <div class="form-section">
                    <h3>Informations de base</h3>
                    
                    <div class="input-group">
                        <label for="date_depart">Date de départ:</label>
                        <input type="date" id="date_depart" name="date_depart" class="form-control" min="<?= $tomorrow ?>" required aria-describedby="date-help">
                        <small id="date-help" class="form-text">Sélectionnez une date à partir de demain</small>
                    </div>
                    
                    <div class="input-group">
                        <label for="nb_participants">Nombre de participants:</label>
                        <select id="nb_participants" name="nb_participants" class="form-control" aria-describedby="participants-help">
                            <?= $participantsOptions ?>
                        </select>
                        <small id="participants-help" class="form-text">Le prix sera ajusté en fonction du nombre de participants</small>
                    </div>
                </div>
                
                <?php if (isset($voyage['activites']) && !empty($voyage['activites'])): ?>
                <div class="form-section">
                    <h3>Activités optionnelles</h3>
                    <p class="section-description">Sélectionnez les activités que vous souhaitez ajouter à votre voyage :</p>
                    
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