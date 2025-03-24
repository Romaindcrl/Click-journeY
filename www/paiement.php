<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

// Vérifier si une réservation est en cours
if (!isset($_SESSION['reservation'])) {
    header('Location: voyages.php');
    exit();
}

// Récupérer les informations de la réservation
$reservation = $_SESSION['reservation'];

// Récupérer les informations du voyage
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyages = json_decode($voyagesJson, true)['voyages'];

$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $reservation['voyage_id']) {
        $voyage = $v;
        break;
    }
}

if (!$voyage) {
    header('Location: voyages.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Générer un ID de transaction unique
    $transactionId = uniqid('TRANS_');
    
    // Créer la commande
    $commande = [
        'id' => $transactionId,
        'user_id' => $_SESSION['user']['id'],
        'voyage_id' => $voyage['id'],
        'date_reservation' => date('Y-m-d H:i:s'),
        'date_depart' => $reservation['date_depart'],
        'activites' => $reservation['activites'],
        'prix_total' => $reservation['prix_total'],
        'statut' => 'confirmé'
    ];
    
    // Charger les commandes existantes
    $commandesFile = __DIR__ . '/../data/commandes.json';
    $commandes = [];
    if (file_exists($commandesFile)) {
        $commandes = json_decode(file_get_contents($commandesFile), true);
    }
    if (!isset($commandes['commandes'])) {
        $commandes['commandes'] = [];
    }
    
    // Ajouter la nouvelle commande
    $commandes['commandes'][] = $commande;
    
    // Sauvegarder les commandes
    if (file_put_contents($commandesFile, json_encode($commandes, JSON_PRETTY_PRINT))) {
        // Supprimer la réservation de la session
        unset($_SESSION['reservation']);
        
        // Rediriger vers la page de confirmation
        header('Location: confirmation.php?status=success');
        exit();
    } else {
        header('Location: confirmation.php?status=error');
        exit();
    }
}
?>

<div class="page-container">
    <h1 class="page-title">Paiement</h1>

    <div class="payment-summary">
        <h3>Récapitulatif de votre réservation</h3>
        
        <div class="summary-item">
            <span>Voyage</span>
            <span><?php echo htmlspecialchars($voyage['nom']); ?></span>
        </div>
        
        <div class="summary-item">
            <span>Date de départ</span>
            <span><?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?></span>
        </div>
        
        <div class="summary-item">
            <span>Activités</span>
            <ul>
                <?php foreach ($reservation['activites'] as $activite): ?>
                    <li><?php echo htmlspecialchars($activite); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="summary-item total">
            <span>Total à payer</span>
            <span><?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> €</span>
        </div>
    </div>

    <form method="POST" class="payment-form">
        <div class="form-group">
            <label for="card_number">Numéro de carte</label>
            <input type="text" id="card_number" name="card_number" required 
                   pattern="[0-9]{16}" placeholder="1234 5678 9012 3456">
        </div>
        
        <div class="form-group">
            <label for="expiry">Date d'expiration</label>
            <input type="text" id="expiry" name="expiry" required 
                   pattern="[0-9]{2}/[0-9]{2}" placeholder="MM/YY">
        </div>
        
        <div class="form-group">
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required 
                   pattern="[0-9]{3}" placeholder="123">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Confirmer le paiement</button>
            <a href="personnalisation.php?id=<?php echo $voyage['id']; ?>" class="btn btn-secondary">Modifier la réservation</a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 