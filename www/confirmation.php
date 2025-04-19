<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

// Vérifier si l'ID de commande est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Rediriger vers la page d'accueil si aucun ID n'est fourni
    setFlashMessage('error', 'Aucune commande spécifiée.');
    header('Location: index.php');
    exit;
}

$orderId = $_GET['id'];

// Charger les données des commandes
$commandesFile = 'data/commandes.json';
$commandes = [];

if (file_exists($commandesFile)) {
    $commandesData = file_get_contents($commandesFile);
    $commandes = json_decode($commandesData, true);
}

// Rechercher la commande spécifique
$commande = null;
foreach ($commandes as $cmd) {
    if ($cmd['id'] === $orderId) {
        $commande = $cmd;
        break;
    }
}

// Vérifier si la commande existe et appartient à l'utilisateur connecté
if (!$commande || $commande['user_id'] !== $_SESSION['user']['id']) {
    setFlashMessage('error', 'Commande introuvable ou accès non autorisé.');
    header('Location: index.php');
    exit;
}

// Charger les données du voyage
$voyagesFile = 'data/voyages.json';
$voyages = [];

if (file_exists($voyagesFile)) {
    $voyagesData = file_get_contents($voyagesFile);
    $voyages = json_decode($voyagesData, true);
}

// Rechercher le voyage associé à la commande
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] === $commande['voyage_id']) {
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

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="confirmation-header">
                <h1>Confirmation de commande</h1>
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
                    <div class="row">
                        <div class="col-md-4">
                            <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo $voyage['titre']; ?></h4>
                            <p class="text-muted"><?php echo $voyage['description']; ?></p>
                            
                            <div class="trip-details mt-3">
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
                            <p>Aucune étape supplémentaire sélectionnée.</p>
                        <?php else: ?>
                            <ul class="list-group">
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
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="total-price">
                                <h4>Total payé</h4>
                                <div class="price"><?php echo number_format($commande['prix_total'], 2, ',', ' '); ?> €</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="actions mt-4 mb-5">
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
    </div>
</div>

<style>
.confirmation-header {
    text-align: center;
    margin-bottom: 2rem;
}

.confirmation-success {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 1rem;
}

.confirmation-success i {
    font-size: 3rem;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.trip-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #f8f9fa;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.detail-item i {
    color: #007bff;
}

.total-price {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    text-align: center;
    border-left: 4px solid #007bff;
}

.total-price .price {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

@media print {
    .actions, header, footer, .theme-switch-wrapper {
        display: none !important;
    }
    
    body {
        padding: 0;
        margin: 0;
    }
    
    .container {
        width: 100%;
        max-width: 100%;
        padding: 0;
        margin: 0;
    }
    
    .card {
        border: 1px solid #ddd;
        margin-bottom: 15px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?> 