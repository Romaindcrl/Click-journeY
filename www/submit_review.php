<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et caster les données du formulaire
    $commande_id = isset($_POST['commande_id']) ? intval($_POST['commande_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    if ($commande_id <= 0 || $rating < 1 || empty($comment)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Tous les champs sont obligatoires.'
        ];
        header('Location: profil.php');
        exit();
    }

    // Lecture des commandes pour vérifier que la commande appartient bien à l'utilisateur
    $commandesData = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
    $commandes = $commandesData['commandes'] ?? [];

    $commande = null;
    foreach ($commandes as $c) {
        if ($c['id'] === $commande_id && $c['user_id'] === $_SESSION['user']['id']) {
            $commande = $c;
            break;
        }
    }

    if (!$commande) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Commande introuvable.'
        ];
        header('Location: profil.php');
        exit();
    }

    // Lecture des avis existants
    $avis_file = __DIR__ . '/../data/avis.json';
    $avis = [];
    if (file_exists($avis_file)) {
        $avisData = json_decode(file_get_contents($avis_file), true);
        $avis = $avisData['avis'] ?? [];
    }

    // Création du nouvel avis
    $user = $_SESSION['user'];
    $nouvel_avis = [
        'id' => uniqid(),
        'user_id' => $user['id'],
        'user_prenom' => $user['prenom'],
        'user_nom' => $user['nom'],
        'voyage_id' => $commande['voyage_id'],
        'order_id' => $commande_id,
        'note' => (int)$rating,
        'commentaire' => htmlspecialchars($comment),
        'date' => date('Y-m-d H:i:s'),
        'statut' => 'publié'
    ];

    // Ajout de l'avis
    $avis[] = $nouvel_avis;

    // Sauvegarde des avis avec clé racine 'avis'
    file_put_contents($avis_file, json_encode(['avis' => $avis], JSON_PRETTY_PRINT));

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Votre avis a été enregistré avec succès.'
    ];
    header('Location: profil.php');
    exit();
} else {
    header('Location: profil.php');
    exit();
} 