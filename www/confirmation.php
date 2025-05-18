<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Récupérer et valider l'ID de commande
$orderIdRaw = $_GET['id'] ?? null;
if ($orderIdRaw === null || trim((string)$orderIdRaw) === '') {
    $_SESSION['flash_message'] = 'Aucune commande spécifiée.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}
$orderId = intval($orderIdRaw);

// Charger les commandes
$commandesFile = __DIR__ . '/../data/commandes.json';
$commandesJson = file_get_contents($commandesFile);
$commandesData = json_decode($commandesJson, true);
$commandes = $commandesData['commandes'] ?? [];

// Trouver la commande
$commande = null;
foreach ($commandes as $cmd) {
    if (is_array($cmd) && isset($cmd['id']) && $cmd['id'] == $orderId) {
        $commande = $cmd;
        break;
    }
}

// Vérifier que la commande appartient à l'utilisateur
$sessionUserId = $_SESSION['user']['id'] ?? null;
if (!$commande || $commande['user_id'] != $sessionUserId) {
    $_SESSION['flash_message'] = 'Commande introuvable ou accès non autorisé.';
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Charger les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';
$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Trouver le voyage
$voyage = null;
foreach ($voyages as $v) {
    if (is_array($v) && isset($v['id']) && $v['id'] == $commande['voyage_id']) {
        $voyage = $v;
        break;
    }
}

// Dates
$dateCommande = new DateTime($commande['date_commande']);
$dateDepartFormatted = new DateTime($commande['date_depart']);
$dateRetour = clone $dateDepartFormatted;
$dateRetour->modify('+' . $voyage['duree'] . ' days');

// Récupérer les activités choisies
$activites = $commande['options_choisies']['etape_1']['activites'] ?? [];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <div class="confirmation-header">
        <h1 class="page-title">Confirmation de commande</h1>
        <div class="confirmation-success">
            <i class="fas fa-check-circle"></i>
            <p>Votre réservation a été confirmée avec succès!</p>
        </div>
    </div>

    <div class="alert alert-success">
        <p><strong>Numéro de transaction:</strong> <?= htmlspecialchars($commande['transaction_id']); ?></p>
        <p><strong>Date de commande:</strong> <?= $dateCommande->format('d/m/Y à H:i'); ?></p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Résumé de votre voyage</h3>
        </div>
        <div class="card-body">
            <div class="card-content">
                <div class="voyage-image-container">
                    <img src="<?= htmlspecialchars($voyage['image']); ?>" alt="<?= htmlspecialchars($voyage['nom']); ?>" class="img-fluid rounded">
                </div>
                <div class="voyage-details">
                    <h4 class="voyage-title"><?= htmlspecialchars($voyage['nom']); ?></h4>
                    <p class="voyage-description"><?= htmlspecialchars($voyage['description']); ?></p>

                    <div class="trip-details">
                        <div class="detail-item"><i class="fas fa-calendar-alt"></i><span>Départ: <?= $dateDepartFormatted->format('d/m/Y'); ?></span></div>
                        <div class="detail-item"><i class="fas fa-calendar-check"></i><span>Retour: <?= $dateRetour->format('d/m/Y'); ?></span></div>
                        <div class="detail-item"><i class="fas fa-users"></i><span>Participants: <?= $commande['nb_participants']; ?></span></div>
                        <div class="detail-item"><i class="fas fa-clock"></i><span>Durée: <?= $voyage['duree']; ?> jours</span></div>
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
                <?php if (empty($activites)): ?>
                    <p class="empty-message">Aucune activité supplémentaire sélectionnée.</p>
                <?php else: ?>
                    <ul class="list-group" aria-label="Options sélectionnées">
                        <?php foreach ($activites as $activite): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($activite['nom']); ?>
                                <span class="badge bg-primary rounded-pill"><?= number_format($activite['prix'] * $commande['nb_participants'], 2, ',', ' '); ?> €</span>
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
                    <p><strong>Prix de base:</strong> <?= number_format($voyage['prix'], 2, ',', ' '); ?> €</p>
                    <?php if (!empty($activites)): ?>
                        <p><strong>Options supplémentaires:</strong>
                            <?php
                            $totalOptions = 0;
                            foreach ($activites as $a) {
                                $totalOptions += $a['prix'] * $commande['nb_participants'];
                            }
                            echo number_format($totalOptions, 2, ',', ' ');
                            ?> €</p>
                    <?php endif; ?>
                    <p><strong>Nombre de participants:</strong> <?= $commande['nb_participants']; ?></p>
                </div>
                <div class="total-price-container">
                    <div class="total-price">
                        <h4>Total payé</h4>
                        <div class="price"><?= number_format($commande['prix_total'], 2, ',', ' '); ?> €</div>
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