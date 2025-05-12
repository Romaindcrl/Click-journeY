<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Traitement du formulaire de personnalisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si c'est une demande de suppression du panier
    if (isset($_POST['action']) && $_POST['action'] === 'vider_panier') {
        // Supprimer la réservation de la session
        unset($_SESSION['reservation']);
        
        // Message de confirmation
        $_SESSION['flash_message'] = 'Votre panier a été vidé.';
        $_SESSION['flash_type'] = 'success';
        
        // Rediriger vers la page des voyages
        header('Location: voyages.php');
        exit;
    }
    
    // Récupérer les données du formulaire
    $voyage_id = isset($_POST['voyage_id']) ? intval($_POST['voyage_id']) : 0;
    $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $nb_participants = isset($_POST['nb_participants']) ? intval($_POST['nb_participants']) : 1;
    
    if (empty($date_depart) || $voyage_id === 0) {
        $_SESSION['flash_message'] = 'Informations de réservation incomplètes.';
        $_SESSION['flash_type'] = 'error';
        header('Location: voyages.php');
        exit;
    }
    
    // Charger les données des voyages pour récupérer les activités
    $voyagesFile = __DIR__ . '/../data/voyages.json';
    if (!file_exists($voyagesFile)) {
        $voyagesFile = __DIR__ . '/data/voyages.json';
    }
    $voyagesJson = file_get_contents($voyagesFile);
    $voyagesData = json_decode($voyagesJson, true);
    $voyages = $voyagesData['voyages'] ?? [];
    
    // Rechercher le voyage
    $voyage = null;
    foreach ($voyages as $v) {
        if ($v['id'] == $voyage_id) {
            $voyage = $v;
            break;
        }
    }
    
    // Calcul du prix total initial en fonction du nombre de participants
    $prix_total = $voyage['prix'] * $nb_participants;
    
    // Récupérer le nombre de bénéficiaires par activité et recalculer le prix
    $activites = [];
    if (isset($_POST['activites_counts']) && is_array($_POST['activites_counts'])) {
        foreach ($_POST['activites_counts'] as $index => $count) {
            $i = intval($index);
            $q = intval($count);
            if ($q > 0 && isset($voyage['activites'][$i])) {
                $act = $voyage['activites'][$i];
                $activites[] = [
                    'nom' => $act['nom'],
                    'prix' => $act['prix'],
                    'count' => $q
                ];
                $prix_total += $act['prix'] * $q;
            }
        }
    }
    
    // Stocker la réservation en session
    $_SESSION['reservation'] = [
        'voyage_id' => $voyage_id,
        'voyage_nom' => $voyage['nom'],
        'voyage_image' => $voyage['image'],
        'date_depart' => $date_depart,
        'nb_participants' => $nb_participants,
        'activites' => $activites,
        'prix_total' => $prix_total
    ];
    
    // Rediriger en GET pour afficher le panier
    header('Location: panier.php');
    exit;
}

// Vérifier la présence d'une réservation en cours
if (!isset($_SESSION['reservation'])) {
    $_SESSION['flash_message'] = 'Aucun voyage dans le panier.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

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
$duree = $voyage['duree'] ?? 7;
$dateRetour = date('Y-m-d', strtotime($date_depart . ' + ' . $duree . ' days'));
$dateDepartFormatted = DateTime::createFromFormat('Y-m-d', $date_depart);
$dateRetourFormatted = DateTime::createFromFormat('Y-m-d', $dateRetour);

// Inclure le header
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title">Panier</h1>
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
                    <h4>Options sélectionnées</h4>
                    <ul>
                        <?php foreach ($activites as $activite): ?>
                        <li>
                            <span class="activity-name"><?php echo htmlspecialchars($activite['nom']); ?> (<?php echo $activite['count']; ?> voyageur<?php echo $activite['count'] > 1 ? 's' : ''; ?>)</span>
                            <span class="activity-price"><?php echo number_format($activite['prix'] * $activite['count'], 0, ',', ' '); ?> €</span>
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
                            $activitesTotal += $activite['prix'] * $activite['count'];
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
        <div class="cart-actions" style="margin-top: 1rem; text-align: center;">
            <div class="cart-buttons">
                <a href="paiement.php" class="btn btn-primary">Passer au paiement</a>
                <a href="voyages.php" class="btn btn-link">Ajouter un autre voyage</a>
                <form action="panier.php" method="post" style="display: inline-block; margin-top: 10px;">
                    <input type="hidden" name="action" value="vider_panier">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir vider votre panier ?');">
                        <i class="fas fa-trash"></i> Vider le panier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 