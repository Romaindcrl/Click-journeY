<?php
require_once __DIR__ . '/check_auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

// Récupérer et valider l'ID de commande depuis la requête
$orderIdRaw = $_GET['id'] ?? null;
if ($orderIdRaw === null || trim((string)$orderIdRaw) === '') {
    // Rediriger vers la page d'accueil si aucun ID valide n'est fourni
    $_SESSION['flash_message'] = 'Aucune commande spécifiée.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}
$orderId = intval($orderIdRaw);

// Charger les données des commandes (depuis le dossier data racine)
$commandesFile = __DIR__ . '/../data/commandes.json';
$commandesJson = file_get_contents($commandesFile);
$commandesData = json_decode($commandesJson, true);
$commandes = $commandesData['commandes'] ?? [];

// Rechercher la commande spécifique
$commande = null;
foreach ($commandes as $cmd) {
    if (!is_array($cmd) || !isset($cmd['id'])) {
        continue;
    }
    if ($cmd['id'] == $orderId) {
        $commande = $cmd;
        break;
    }
}

// Vérifier si la commande existe et appartient à l'utilisateur connecté
$sessionUserId = $_SESSION['user']['id'] ?? null;
if (!$commande || $commande['user_id'] != $sessionUserId) {
    $_SESSION['flash_message'] = 'Commande introuvable ou accès non autorisé.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Charger les données du voyage (depuis le dossier data racine)
$voyagesFile = __DIR__ . '/../data/voyages.json';
$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Rechercher le voyage associé à la commande
$voyage = null;
foreach ($voyages as $v) {
    if (!is_array($v) || !isset($v['id'])) {
        continue;
    }
    if ($v['id'] == $commande['voyage_id']) {
        $voyage = $v;
        break;
    }
}

// Formater la date pour l'affichage
$dateCommande = new DateTime($commande['date_commande']);
$dateDepartFormatted = new DateTime($commande['date_depart']);

// Calculer la date de retour (date de départ + durée du voyage)
$dateRetour = clone $dateDepartFormatted;
$dateRetour->modify('+' . $voyage['duree'] . ' days');
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="page-container">
    <div class="confirmation-header">
        <h1 class="page-title">Confirmation de commande</h1>
        <div class="confirmation-success">
            <i class="fas fa-check-circle"></i>
            <p>Votre réservation a été confirmée avec succès!</p>
        </div>
    </div>
    
    <div class="alert alert-success">
        <p><strong>Numéro de transaction:</strong> <?php echo $commande['transaction_id']; ?></p>
        <p><strong>Date de commande:</strong> <?php echo $dateCommande->format('d/m/Y à H:i'); ?></p>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3>Résumé de votre voyage</h3>
        </div>
        <div class="card-body">
            <div class="card-content">
                <div class="voyage-image-container">
                    <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['nom']); ?>" class="img-fluid rounded">
                </div>
                <div class="voyage-details">
                    <h4 class="voyage-title"><?php echo htmlspecialchars($voyage['nom']); ?></h4>
                    <p class="voyage-description"><?php echo $voyage['description']; ?></p>
                    
                    <div class="trip-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Départ: <?php echo $dateDepartFormatted->format('d/m/Y'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>Retour: <?php echo $dateRetour->format('d/m/Y'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>Participants: <?php echo $commande['nb_participants']; ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>Durée: <?php echo $voyage['duree']; ?> jours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3>Options sélectionnées</h3>
        </div>
        <div class="card-body">
            <div class="selected-options">
                <?php if (empty($commande['etapes'])): ?>
                    <p class="empty-message">Aucune étape supplémentaire sélectionnée.</p>
                <?php else: ?>
                    <ul class="list-group" aria-label="Options sélectionnées">
                        <?php foreach ($commande['etapes'] as $etape): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $etape['titre']; ?>
                                <span class="badge bg-primary rounded-pill"><?php echo number_format($etape['prix'], 2, ',', ' '); ?> €</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3>Détails du paiement</h3>
        </div>
        <div class="card-body">
            <div class="payment-details">
                <div class="payment-info">
                    <p><strong>Prix de base:</strong> <?php echo number_format($voyage['prix'], 2, ',', ' '); ?> €</p>
                    <?php if (!empty($commande['etapes'])): ?>
                        <p><strong>Options supplémentaires:</strong>
                        <?php 
                            $optionsTotal = 0;
                            foreach ($commande['etapes'] as $etape) {
                                $optionsTotal += $etape['prix'];
                            }
                            echo number_format($optionsTotal, 2, ',', ' '); 
                        ?> €</p>
                    <?php endif; ?>
                    <p><strong>Nombre de participants:</strong> <?php echo $commande['nb_participants']; ?></p>
                </div>
                <div class="total-price-container">
                    <div class="total-price">
                        <h4>Total payé</h4>
                        <div class="price"><?php echo number_format($commande['prix_total'], 2, ',', ' '); ?> €</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="actions">
        <a href="profil.php" class="btn btn-primary">
            <i class="fas fa-user"></i> Voir mon profil
        </a>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="fas fa-home"></i> Retour à l'accueil
        </a>
        <a href="#" class="btn btn-outline-secondary" onclick="window.print();">
            <i class="fas fa-print"></i> Imprimer
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?> 