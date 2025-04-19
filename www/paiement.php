<?php
// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Traitement des redirections et logique métier AVANT require_once header.php
// car header.php génère du HTML

// Vérifier si l'utilisateur est connecté (sans nécessiter de require check_auth.php pour l'instant)
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['flash_message'] = 'Veuillez vous connecter pour accéder à cette page.';
    $_SESSION['flash_type'] = 'info';
    header("Location: connexion.php");
    exit();
}

// Vérifier si le formulaire a été soumis directement à cette page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['card_number'])) {
    // Récupérer les données du formulaire de personnalisation
    $voyageId = isset($_POST['voyage_id']) ? intval($_POST['voyage_id']) : 0;
    $dateDepart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $nbParticipants = isset($_POST['nb_participants']) ? intval($_POST['nb_participants']) : 1;
    $prixTotal = isset($_POST['prix_total']) ? intval($_POST['prix_total']) : 0;
    
    if (empty($dateDepart) || $voyageId === 0) {
        $_SESSION['flash_message'] = 'Informations de réservation incomplètes.';
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
    
    // Rechercher le voyage
    $voyage = null;
    foreach ($voyages as $v) {
        if ($v['id'] == $voyageId) {
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
    
    // Traiter les activités sélectionnées
    $activites = [];
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
    
    // Calculer le prix total si pas fourni
    if ($prixTotal === 0) {
        $prixTotal = $voyage['prix'] * $nbParticipants;
        foreach ($activites as $activite) {
            $prixTotal += $activite['prix'] * $nbParticipants;
        }
    }
    
    // Créer la réservation dans la session
    $_SESSION['reservation'] = [
        'voyage_id' => $voyageId,
        'voyage_nom' => $voyage['nom'],
        'voyage_image' => $voyage['image'],
        'date_depart' => $dateDepart,
        'nb_participants' => $nbParticipants,
        'activites' => $activites,
        'prix_total' => $prixTotal
    ];
    
    error_log("Nouvelle réservation créée depuis le formulaire: " . print_r($_SESSION['reservation'], true));
} 
// Traitement du formulaire de paiement
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_number'])) {
    // Simulation d'un paiement (90% de réussite)
    $paymentSuccess = (mt_rand(1, 10) <= 9);
    
    if ($paymentSuccess) {
        // Générer un identifiant de transaction unique
        $transactionId = 'TR-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
        
        // Créer une nouvelle commande
        $commandesFile = __DIR__ . '/../data/commandes.json';
        if (!file_exists($commandesFile)) {
            $commandesFile = __DIR__ . '/data/commandes.json';
        }
        
        $commandes = [];
        
        if (file_exists($commandesFile)) {
            $commandesJson = file_get_contents($commandesFile);
            $commandesData = json_decode($commandesJson, true);
            $commandes = $commandesData['commandes'] ?? [];
        }
        
        // Récupérer les données de réservation
        $reservation = $_SESSION['reservation'];
        
        // Calculer la date de retour basée sur la durée du voyage
        $voyagesFile = __DIR__ . '/../data/voyages.json';
        if (!file_exists($voyagesFile)) {
            $voyagesFile = __DIR__ . '/data/voyages.json';
        }
        
        $voyagesJson = file_get_contents($voyagesFile);
        $voyagesData = json_decode($voyagesJson, true);
        $voyages = $voyagesData['voyages'] ?? [];
        
        $voyage = null;
        foreach ($voyages as $v) {
            if ($v['id'] == $reservation['voyage_id']) {
                $voyage = $v;
                break;
            }
        }
        
        $duree = $voyage ? ($voyage['duree'] ?? 7) : 7; // Durée par défaut si non spécifiée
        $dateRetour = date('Y-m-d', strtotime($reservation['date_depart'] . ' + ' . $duree . ' days'));
        
        // Créer la structure des activités choisies pour la commande
        $optionsChoisies = [];
        if (!empty($reservation['activites'])) {
            $optionsChoisies['etape_1'] = [
                'activites' => $reservation['activites']
            ];
        }
        
        // Créer la nouvelle commande
        $nouvelleCommande = [
            'id' => count($commandes) + 1,
            'transaction_id' => $transactionId,
            'user_id' => $_SESSION['user']['id'],
            'voyage_id' => $reservation['voyage_id'],
            'date_commande' => date('Y-m-d'),
            'date_depart' => $reservation['date_depart'],
            'date_retour' => $dateRetour,
            'nb_participants' => $reservation['nb_participants'],
            'prix_total' => $reservation['prix_total'],
            'options_choisies' => $optionsChoisies,
            'statut' => 'confirmé'
        ];
        
        // Ajouter la commande à la liste
        $commandes[] = $nouvelleCommande;
        
        // Enregistrer les commandes
        $commandesData = ['commandes' => $commandes];
        file_put_contents($commandesFile, json_encode($commandesData, JSON_PRETTY_PRINT));
        
        // Vider la réservation en cours
        unset($_SESSION['reservation']);
        
        // Définir un message de succès
        $_SESSION['flash_message'] = 'Paiement réussi ! Votre voyage est confirmé.';
        $_SESSION['flash_type'] = 'success';
        
        // Rediriger vers la page de confirmation
        header('Location: confirmation.php?id=' . $nouvelleCommande['id']);
        exit;
    } else {
        // Paiement échoué
        $_SESSION['flash_message'] = 'Le paiement a échoué. Veuillez réessayer ou contacter le service client.';
        $_SESSION['flash_type'] = 'error';
        
        // Rediriger vers la même page pour réessayer
        header('Location: paiement.php');
        exit;
    }
}

// Vérifier si une réservation est en cours
if (!isset($_SESSION['reservation'])) {
    // Ajouter un message d'erreur
    $_SESSION['flash_message'] = 'Aucune réservation trouvée. Veuillez choisir un voyage.';
    $_SESSION['flash_type'] = 'error';
    
    // Rediriger vers la page des voyages
    header('Location: voyages.php');
    exit;
}

// À ce stade, toutes les redirections sont terminées et nous pouvons inclure le header
require_once __DIR__ . '/includes/header.php';

// Récupérer les données de réservation
$reservation = $_SESSION['reservation'];
$voyage_id = $reservation['voyage_id'];
$date_depart = $reservation['date_depart'];
$nb_participants = $reservation['nb_participants'];
$activites = $reservation['activites'] ?? [];
$prix_total = $reservation['prix_total'];

// Charger les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';
if (!file_exists($voyagesFile)) {
    $voyagesFile = __DIR__ . '/data/voyages.json';
}

$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Rechercher le voyage correspondant
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyage_id) {
        $voyage = $v;
        break;
    }
}

// Calculer la date de retour prévue
$duree = $voyage['duree'] ?? 7; // Durée par défaut si non spécifiée
$dateRetour = date('Y-m-d', strtotime($date_depart . ' + ' . $duree . ' days'));

// Formater les dates pour l'affichage
$dateDepartFormatted = DateTime::createFromFormat('Y-m-d', $date_depart);
$dateRetourFormatted = DateTime::createFromFormat('Y-m-d', $dateRetour);
?>

<div class="page-container">
    <h1 class="page-title">Paiement</h1>
    
    <div class="payment-container">
        <div class="payment-summary">
            <div class="card-header">
                <h2>Récapitulatif de votre réservation</h2>
            </div>
            <div class="card-body">
                <div class="trip-summary">
                    <div class="trip-image">
                        <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['nom']); ?>">
                    </div>
                    <div class="trip-details">
                        <h3><?php echo htmlspecialchars($voyage['nom']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($voyage['description'], 0, 100)); ?>...</p>
                        
                        <div class="trip-info">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Départ: <?php echo $dateDepartFormatted->format('d/m/Y'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar-check"></i>
                                <span>Retour: <?php echo $dateRetourFormatted->format('d/m/Y'); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Participants: <?php echo $nb_participants; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($activites)): ?>
                <div class="selected-activities">
                    <h4>Activités sélectionnées</h4>
                    <ul>
                        <?php foreach ($activites as $activite): ?>
                        <li>
                            <span class="activity-name"><?php echo htmlspecialchars($activite['nom']); ?></span>
                            <span class="activity-price"><?php echo number_format($activite['prix'] * $nb_participants, 0, ',', ' '); ?> €</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="price-summary">
                    <div class="price-item">
                        <span>Prix du voyage (<?php echo $nb_participants; ?> personne<?php echo $nb_participants > 1 ? 's' : ''; ?>)</span>
                        <span><?php echo number_format($voyage['prix'] * $nb_participants, 0, ',', ' '); ?> €</span>
                    </div>
                    
                    <?php if (!empty($activites)): ?>
                    <div class="price-item">
                        <span>Options additionnelles</span>
                        <?php
                        $activitesTotal = 0;
                        foreach ($activites as $activite) {
                            $activitesTotal += $activite['prix'] * $nb_participants;
                        }
                        ?>
                        <span><?php echo number_format($activitesTotal, 0, ',', ' '); ?> €</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="price-item total">
                        <span>Total</span>
                        <span><?php echo number_format($prix_total, 0, ',', ' '); ?> €</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="payment-form">
            <form method="post" action="">
                <h2>Informations de paiement</h2>
                
                <div class="form-row">
                    <label class="form-label" for="card_holder">Nom sur la carte</label>
                    <input type="text" id="card_holder" name="card_holder" class="form-control" required autocomplete="off">
                </div>
                
                <div class="form-row">
                    <label class="form-label" for="card_number">Numéro de carte</label>
                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="XXXX XXXX XXXX XXXX" required autocomplete="off">
                </div>
                
                <div class="form-row-inline">
                    <div class="form-row">
                        <label class="form-label" for="expiry_month">Date d'expiration</label>
                        <div class="expiry-inputs">
                            <select id="expiry_month" name="expiry_month" class="form-control" required>
                                <option value="">Mois</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="expiry_year" name="expiry_year" class="form-control" required>
                                <option value="">Année</option>
                                <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <label class="form-label" for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" class="form-control" placeholder="XXX" required autocomplete="off">
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">J'accepte les <a href="conditions.php" target="_blank">conditions générales de vente</a></label>
                </div>
                
                <button type="submit" class="btn-payment">Payer <?php echo number_format($prix_total, 0, ',', ' '); ?> €</button>
            </form>
        </div>
    </div>
</div>

<style>
.payment-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.payment-summary,
.payment-form {
    background-color: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.card-header {
    background-color: var(--primary-color);
    color: white;
    padding: 1.25rem;
}

.card-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.trip-summary {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.trip-image {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.trip-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.trip-details h3 {
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 0.75rem;
}

.trip-info {
    margin-top: 1rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.info-item i {
    color: var(--primary-color);
}

.selected-activities {
    margin: 1.5rem 0;
}

.selected-activities h4 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.selected-activities ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.selected-activities li {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.selected-activities li:last-child {
    border-bottom: none;
}

.price-summary {
    background-color: var(--background-color);
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1.5rem;
}

.price-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.price-item.total {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary-color);
    border-top: 1px solid var(--border-color);
    padding-top: 0.75rem;
    margin-top: 0.75rem;
}

/* Formulaire de paiement */
.payment-form {
    padding: 1.5rem;
    max-width: 500px;
    margin: 0 auto;
}

.payment-form h2 {
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-row {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    text-align: left;
}

.form-control {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    text-align: center;
    box-sizing: border-box;
}

.form-row-inline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-check {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    justify-content: center;
}

.form-check input {
    margin-right: 0.5rem;
}

.form-check a {
    color: var(--primary-color);
    text-decoration: none;
}

.form-check a:hover {
    text-decoration: underline;
}

.btn-payment {
    width: 100%;
    padding: 1rem;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-payment:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (min-width: 768px) {
    .payment-container {
        grid-template-columns: 1fr 1fr;
    }
    
    .trip-summary {
        flex-direction: row;
    }
    
    .trip-image {
        flex: 0 0 40%;
    }
    
    .trip-details {
        flex: 1;
    }
}

.expiry-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

/* Styles pour centrer les sélecteurs de date */
select.form-control {
    text-align: center;
    text-align-last: center;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6" viewBox="0 0 12 6"><path d="M0 0l6 6 6-6z" fill="%23666"/></svg>');
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 12px 6px;
    padding-right: 1.75rem;
}
</style>

<script src="src/js/form-validation.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 