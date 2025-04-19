<?php
// Ce fichier gère l'enregistrement des évaluations des voyages

// Vérification que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Méthode non autorisée');
}

// Vérification que toutes les données nécessaires sont présentes
if (!isset($_POST['commande_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Données manquantes');
}

// Récupération des données
$commandeId = $_POST['commande_id'];
$rating = (int)$_POST['rating'];
$comment = trim($_POST['comment']);

// Validation
if ($rating < 1 || $rating > 5) {
    header('HTTP/1.1 400 Bad Request');
    exit('Note invalide (doit être entre 1 et 5)');
}

if (strlen($comment) < 5) {
    header('HTTP/1.1 400 Bad Request');
    exit('Le commentaire doit contenir au moins 5 caractères');
}

// Chargement des commandes existantes
$commandesFile = __DIR__ . '/../data/commandes.json';
if (!file_exists($commandesFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur interne du serveur');
}

$commandesJson = file_get_contents($commandesFile);
$commandes = json_decode($commandesJson, true);

if (!is_array($commandes)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur interne du serveur');
}

// Recherche de la commande
$commandeTrouvee = false;
foreach ($commandes as &$commande) {
    if ($commande['id'] === $commandeId) {
        // Ajout de l'évaluation à la commande
        $commande['evaluation'] = [
            'note' => $rating,
            'commentaire' => $comment,
            'date' => date('Y-m-d H:i:s')
        ];
        $commandeTrouvee = true;
        break;
    }
}

if (!$commandeTrouvee) {
    header('HTTP/1.1 404 Not Found');
    exit('Commande non trouvée');
}

// Enregistrement des modifications
if (file_put_contents($commandesFile, json_encode($commandes, JSON_PRETTY_PRINT)) === false) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur lors de l\'enregistrement de l\'évaluation');
}

// Mise à jour des évaluations globales pour chaque voyage
// Chargement des voyages
$voyagesFile = __DIR__ . '/../data/voyages.json';
if (!file_exists($voyagesFile)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur interne du serveur');
}

$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);

if (!is_array($voyagesData) || !isset($voyagesData['voyages'])) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Format de données voyages invalide');
}

// Trouver le voyage associé à cette commande
foreach ($commandes as $cmd) {
    if ($cmd['id'] === $commandeId) {
        $voyageId = $cmd['voyage_id'];
        
        // Calculer les nouvelles moyennes d'évaluation pour ce voyage
        $totalRatings = 0;
        $sumRatings = 0;
        $voyageComments = [];
        
        foreach ($commandes as $c) {
            if (isset($c['voyage_id']) && $c['voyage_id'] === $voyageId && isset($c['evaluation'])) {
                $totalRatings++;
                $sumRatings += $c['evaluation']['note'];
                
                // Ajouter le commentaire à la liste (maximum 5 commentaires)
                if (count($voyageComments) < 5) {
                    $voyageComments[] = [
                        'note' => $c['evaluation']['note'],
                        'commentaire' => $c['evaluation']['commentaire'],
                        'date' => $c['evaluation']['date']
                    ];
                }
            }
        }
        
        // Mettre à jour les évaluations du voyage
        foreach ($voyagesData['voyages'] as &$voyage) {
            if ($voyage['id'] === $voyageId) {
                $voyage['evaluations'] = [
                    'moyenne' => $totalRatings > 0 ? round($sumRatings / $totalRatings, 1) : 0,
                    'total' => $totalRatings,
                    'commentaires' => $voyageComments
                ];
                break;
            }
        }
        
        break;
    }
}

// Enregistrer les modifications des voyages
if (file_put_contents($voyagesFile, json_encode($voyagesData, JSON_PRETTY_PRINT)) === false) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Erreur lors de la mise à jour des évaluations de voyage');
}

// Réponse réussie
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Évaluation enregistrée avec succès']);
exit; 