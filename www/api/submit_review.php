<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour soumettre un avis']);
    exit;
}

// Vérifier que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON de la requête
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Vérifier que les données requises sont présentes
if (!isset($data['order_id']) || !isset($data['voyage_id']) || !isset($data['rating']) || !isset($data['comment'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    exit;
}

// Validation des données
$order_id = $data['order_id'];
$voyage_id = $data['voyage_id'];
$rating = intval($data['rating']);
$comment = trim($data['comment']);

// Vérifier que la note est entre 1 et 5
if ($rating < 1 || $rating > 5) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'La note doit être entre 1 et 5']);
    exit;
}

// Vérifier que le commentaire n'est pas vide
if (empty($comment)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
    exit;
}

// Chemin vers le fichier des avis
$reviews_file = __DIR__ . '/../../data/avis.json';

// Créer le fichier s'il n'existe pas
if (!file_exists($reviews_file)) {
    file_put_contents($reviews_file, json_encode([]));
}

// Lire les avis existants
$reviews = json_decode(file_get_contents($reviews_file), true);
if (!is_array($reviews)) {
    $reviews = [];
}

// Vérifier si l'utilisateur a déjà donné un avis pour cette commande
foreach ($reviews as $review) {
    if ($review['order_id'] === $order_id && $review['user_id'] === $_SESSION['user_id']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà donné un avis pour cette commande']);
        exit;
    }
}

// Préparer le nouvel avis
$new_review = [
    'id' => uniqid(),
    'user_id' => $_SESSION['user_id'],
    'order_id' => $order_id,
    'voyage_id' => $voyage_id,
    'rating' => $rating,
    'comment' => $comment,
    'date' => date('Y-m-d H:i:s')
];

// Ajouter l'avis
$reviews[] = $new_review;

// Enregistrer les avis
if (file_put_contents($reviews_file, json_encode($reviews, JSON_PRETTY_PRINT))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Avis enregistré avec succès']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'avis']);
}
?> 