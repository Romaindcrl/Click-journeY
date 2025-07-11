<?php
session_start();
require_once __DIR__ . '/api/getapikey.php';

// Récupération des données de retour
$transaction = $_GET['transaction'] ?? '';
$montant     = $_GET['montant'] ?? '';
$vendeur     = $_GET['vendeur'] ?? '';
$statut      = $_GET['status'] ?? '';
$control     = $_GET['control'] ?? '';
$user_id     = $_GET['user'] ?? null;

// Recalcul de la valeur de contrôle
$api_key = getAPIKey($vendeur);
$control_calcule = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $statut . '#');

// Vérification d'intégrité
if ($control !== $control_calcule) {
    echo "<h1>Erreur : Vérification de l'intégrité échouée</h1>";
    echo "<pre>";
    echo "CONTROL REÇU   : " . htmlspecialchars($control) . "\n";
    echo "CONTROL CALCULÉ: " . htmlspecialchars($control_calcule) . "\n\n";
    echo "Données utilisées :\n";
    echo "API KEY        : " . htmlspecialchars($api_key) . "\n";
    echo "Transaction    : " . htmlspecialchars($transaction) . "\n";
    echo "Montant        : " . htmlspecialchars($montant) . "\n";
    echo "Vendeur        : " . htmlspecialchars($vendeur) . "\n";
    echo "Statut         : " . htmlspecialchars($statut) . "\n";
    echo "</pre>";
    exit;
}

// Vérification du statut de paiement
if ($statut === 'accepted') {
    // Vérifier que le panier n'est pas vide et que l'utilisateur est présent
    if (!isset($_SESSION['reservations']) || empty($_SESSION['reservations']) || !$user_id) {
        echo "<h1>Erreur</h1><p>Session expirée ou identifiant utilisateur manquant.</p>";
        exit;
    }

    // Récupérer les réservations du panier
    $reservations = $_SESSION['reservations'];

    // Charger les données des voyages
    $voyagesFile = __DIR__ . '/../data/voyages.json';
    if (!file_exists($voyagesFile)) {
        echo "<h1>Erreur</h1><p>Fichier de voyages introuvable.</p>";
        exit;
    }

    $voyagesJson = file_get_contents($voyagesFile);
    $voyagesData = json_decode($voyagesJson, true);
    $voyages = $voyagesData['voyages'] ?? [];

    // Charger les commandes existantes
    $commandesFile = __DIR__ . '/../data/commandes.json';
    $commandes = [];
    if (file_exists($commandesFile)) {
        $commandesJson = file_get_contents($commandesFile);
        $commandesData = json_decode($commandesJson, true);
        $commandes = $commandesData['commandes'] ?? [];
    }

    $user_id = intval($user_id);

    // Créer une commande pour chaque réservation
    foreach ($reservations as $reservation) {
        // Trouver le voyage sélectionné
        $voyage = null;
        foreach ($voyages as $v) {
            if ($v['id'] == $reservation['voyage_id']) {
                $voyage = $v;
                break;
            }
        }
        $duree = $voyage['duree'] ?? 7;
        $dateRetour = date('Y-m-d', strtotime($reservation['date_depart'] . ' + ' . $duree . ' days'));

        $nouvelleCommande = [
            'id' => count($commandes) + 1,
            'transaction_id' => $transaction,
            'user_id' => $user_id,
            'voyage_id' => $reservation['voyage_id'],
            'date_commande' => date('Y-m-d H:i:s'),
            'date_depart' => $reservation['date_depart'],
            'date_retour' => $dateRetour,
            'nb_participants' => $reservation['nb_participants'],
            'prix_total' => $reservation['prix_total'],
            'options_choisies' => [
                'etape_1' => [
                    'activites' => $reservation['activites'] ?? []
                ]
            ],
            'statut' => 'confirmé'
        ];
        $commandes[] = $nouvelleCommande;
    }

    file_put_contents($commandesFile, json_encode(['commandes' => $commandes], JSON_PRETTY_PRINT));

    // Nettoyer la session de réservation
    unset($_SESSION['reservations']);
    $_SESSION['flash_message'] = 'Paiement validé avec succès !';
    $_SESSION['flash_type'] = 'success';

    // Rediriger vers la page de confirmation avec la transaction
    header('Location: confirmation.php?transaction=' . urlencode($transaction));
    exit;
} else {
    $_SESSION['flash_message'] = 'Paiement refusé. Veuillez réessayer.';
    $_SESSION['flash_type'] = 'error';
    header('Location: paiement.php');
    exit;
}
