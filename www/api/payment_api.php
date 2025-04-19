<?php
session_start();
header('Content-Type: application/json');

// Simuler une latence d'API
usleep(rand(500000, 1500000)); // 0.5 à 1.5 secondes de délai

// Récupérer les données POST (JSON)
$input = json_decode(file_get_contents('php://input'), true);

// Vérifier que toutes les données nécessaires sont présentes
if (!isset($input['cardName']) || !isset($input['cardNumber']) || 
    !isset($input['expiryMonth']) || !isset($input['expiryYear']) || 
    !isset($input['cvv'])) {
        
    echo json_encode([
        'success' => false,
        'message' => 'Données de paiement incomplètes.'
    ]);
    exit;
}

// Validation des données
$cardName = trim($input['cardName']);
$cardNumber = trim(str_replace(' ', '', $input['cardNumber']));
$expiryMonth = trim($input['expiryMonth']);
$expiryYear = trim($input['expiryYear']);
$cvv = trim($input['cvv']);

// Validation du nom
if (strlen($cardName) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Le nom sur la carte est trop court.'
    ]);
    exit;
}

// Validation du numéro de carte
if (!preg_match('/^[0-9]{16}$/', $cardNumber)) {
    echo json_encode([
        'success' => false,
        'message' => 'Numéro de carte invalide. Assurez-vous qu\'il comporte 16 chiffres.'
    ]);
    exit;
}

// Validation de la date d'expiration
$currentYear = date('Y');
$currentMonth = date('n');

if ($expiryYear < $currentYear || 
    ($expiryYear == $currentYear && $expiryMonth < $currentMonth)) {
    echo json_encode([
        'success' => false,
        'message' => 'La date d\'expiration de la carte est dépassée.'
    ]);
    exit;
}

// Validation du CVV
if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
    echo json_encode([
        'success' => false,
        'message' => 'Le code de sécurité CVV est invalide.'
    ]);
    exit;
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour effectuer un paiement.'
    ]);
    exit;
}

// Vérifier qu'il y a une réservation en cours
if (!isset($_SESSION['customization'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Aucune réservation en cours.'
    ]);
    exit;
}

// Simuler une probabilité de 90% de réussite du paiement
$success = rand(1, 10) <= 9;

if ($success) {
    // Générer un ID de transaction unique
    $transactionId = uniqid('TRANS-', true);
    
    // Création d'un identifiant de commande unique
    $orderId = uniqid('CMD-', true);
    
    // Récupérer les données de la réservation
    $reservation = $_SESSION['customization'];
    $userId = $_SESSION['user_id'];
    
    // Charger les données des voyages
    $voyagesJson = file_get_contents('../data/voyages.json');
    $voyages = json_decode($voyagesJson, true);
    
    // Trouver le voyage sélectionné
    $selectedVoyage = null;
    foreach ($voyages as $voyage) {
        if ($voyage['id'] == $reservation['voyage_id']) {
            $selectedVoyage = $voyage;
            break;
        }
    }
    
    if (!$selectedVoyage) {
        echo json_encode([
            'success' => false,
            'message' => 'Voyage introuvable.'
        ]);
        exit;
    }
    
    // Créer une nouvelle commande
    $newOrder = [
        'id' => $orderId,
        'user_id' => $userId,
        'voyage_id' => $reservation['voyage_id'],
        'transaction_id' => $transactionId,
        'date_commande' => date('Y-m-d H:i:s'),
        'date_depart' => $reservation['date_depart'],
        'nb_participants' => $reservation['nb_participants'],
        'etapes' => $reservation['etapes'] ?? [],
        'prix_total' => $reservation['prix_total'],
        'statut' => 'Confirmé'
    ];
    
    // Charger et mettre à jour le fichier de commandes
    $commandesFile = '../data/commandes.json';
    $commandes = [];
    
    if (file_exists($commandesFile)) {
        $commandesJson = file_get_contents($commandesFile);
        $commandes = json_decode($commandesJson, true);
    }
    
    $commandes[] = $newOrder;
    file_put_contents($commandesFile, json_encode($commandes, JSON_PRETTY_PRINT));
    
    // Supprimer les données de réservation de la session
    unset($_SESSION['customization']);
    
    // Préparer la réponse
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Paiement traité avec succès.'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Paiement traité avec succès.',
        'transaction_id' => $transactionId,
        'redirect' => 'confirmation.php?order_id=' . $orderId
    ]);
} else {
    // Simuler différentes erreurs de paiement
    $errorMessages = [
        'La transaction a été refusée par votre banque. Veuillez contacter votre établissement bancaire.',
        'Fonds insuffisants sur votre compte. Veuillez utiliser une autre carte.',
        'Erreur de communication avec le serveur de paiement. Veuillez réessayer.',
        'Transaction suspectée de fraude. Veuillez contacter votre banque.',
        'Limite de paiement dépassée. Veuillez contacter votre banque ou utiliser une autre carte.'
    ];
    
    $randomError = $errorMessages[array_rand($errorMessages)];
    
    echo json_encode([
        'success' => false,
        'message' => $randomError
    ]);
}
?> 