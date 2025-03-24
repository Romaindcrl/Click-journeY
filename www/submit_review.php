<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commande_id = $_POST['commande_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    if (!$commande_id || !$rating || !$comment) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Tous les champs sont obligatoires.'
        ];
        header('Location: profil.php');
        exit();
    }

    // Lecture des commandes pour vérifier que la commande appartient bien à l'utilisateur
    $commandes = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
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
        $avis = json_decode(file_get_contents($avis_file), true);
    }

    // Création du nouvel avis
    $nouvel_avis = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'voyage_id' => $commande['voyage_id'],
        'commande_id' => $commande_id,
        'note' => (int)$rating,
        'commentaire' => htmlspecialchars($comment),
        'date' => date('Y-m-d H:i:s')
    ];

    // Ajout de l'avis
    $avis[] = $nouvel_avis;

    // Sauvegarde des avis
    file_put_contents($avis_file, json_encode($avis, JSON_PRETTY_PRINT));

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