<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour laisser un avis'
    ]);
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

// Vérifier que toutes les données nécessaires sont présentes
if (!isset($data['voyage_id']) || !isset($data['rating'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Données incomplètes'
    ]);
    exit;
}

// Vérifier que la note est entre 1 et 5
if ($data['rating'] < 1 || $data['rating'] > 5) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'La note doit être entre 1 et 5'
    ]);
    exit;
}

// Vérifier que le commentaire n'est pas vide s'il est fourni
if (isset($data['comment']) && trim($data['comment']) === '') {
    $data['comment'] = null;
}

// Charger le fichier des avis
$avisFilePath = '../../data/avis.json';
$avis = [];

if (file_exists($avisFilePath)) {
    $avisContent = file_get_contents($avisFilePath);
    $avis = json_decode($avisContent, true);
    
    if (!is_array($avis)) {
        $avis = [];
    }
}

// Créer un nouvel avis
$nouvelAvis = [
    'id' => uniqid(),
    'voyage_id' => $data['voyage_id'],
    'user_id' => $_SESSION['user']['id'],
    'user_nom' => $_SESSION['user']['nom'],
    'user_prenom' => $_SESSION['user']['prenom'],
    'note' => (int)$data['rating'],
    'commentaire' => $data['comment'] ?? null,
    'date' => date('Y-m-d'),
    'statut' => 'publié'
];

// Ajouter l'avis à la liste
$avis[] = $nouvelAvis;

// Enregistrer dans le fichier
if (file_put_contents($avisFilePath, json_encode($avis, JSON_PRETTY_PRINT))) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Avis ajouté avec succès',
        'avis' => $nouvelAvis
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement de l\'avis'
    ]);
} 