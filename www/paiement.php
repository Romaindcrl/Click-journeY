<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

// Vérifier si le voyageId et la date de départ sont définis dans la session
if (!isset($_SESSION['reservation']) || !isset($_SESSION['reservation']['voyageId']) || !isset($_SESSION['reservation']['date_depart'])) {
    // Rediriger vers la page des voyages si aucune réservation n'est en cours
    header("Location: voyages.php");
    exit();
}

// Récupération des infos de réservation
$reservation = $_SESSION['reservation'];
$voyageId = $reservation['voyageId'];
$dateDepart = $reservation['date_depart'];
$activites = $reservation['activites'] ?? [];
$prixTotal = $reservation['prix_total'] ?? 0;

// Chargement des voyages
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];

// Récupération du voyage spécifique
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyageId) {
        $voyage = $v;
        break;
    }
}

// Si le voyage n'existe pas, rediriger vers la page des voyages
if (!$voyage) {
    header("Location: voyages.php");
    exit();
}

// Prix de base
$prixBase = $voyage['prix'];

// Traitement du formulaire de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = false;
    
    // Simuler une vérification de paiement (toujours accepté dans cette démo)
    // Génération d'un identifiant de transaction unique
    $transactionId = uniqid('TRANS_');
    
    // Si le paiement est réussi
    if (!$erreur) {
        // Création de la commande
        $command = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user']['id'],
            'voyage_id' => $voyageId,
            'date_reservation' => date('Y-m-d H:i:s'),
            'date_depart' => $dateDepart,
            'activites' => $activites,
            'prix_total' => $prixTotal,
            'status' => 'confirmé',
            'transaction_id' => $transactionId
        ];
        
        // Chargement des commandes existantes
        $commandsFile = __DIR__ . '/../data/commandes.json';
        if (file_exists($commandsFile)) {
            $commandsJson = file_get_contents($commandsFile);
            $commands = json_decode($commandsJson, true) ?: [];
        } else {
            $commands = [];
        }
        
        // Ajout de la nouvelle commande
        $commands[] = $command;
        
        // Sauvegarde du fichier de commandes
        file_put_contents($commandsFile, json_encode($commands, JSON_PRETTY_PRINT));
        
        // Suppression des infos de réservation de la session
        unset($_SESSION['reservation']);
        
        // Création d'un message flash pour indiquer le succès
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Votre paiement a été accepté. Votre voyage est confirmé!'
        ];
        
        // Redirection vers la page de confirmation
        header("Location: confirmation.php?status=success");
        exit();
    } else {
        // Création d'un message flash pour indiquer l'échec
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Une erreur est survenue lors du traitement de votre paiement. Veuillez réessayer.'
        ];
        
        // Redirection en cas d'échec
        header("Location: confirmation.php?status=error");
        exit();
    }
}
?>

<div class="page-container">
    <h1 class="page-title">Finaliser votre réservation</h1>
    
    <div class="payment-container">
        <div class="payment-summary">
            <div class="payment-header">
                <h3>Récapitulatif de votre commande</h3>
            </div>
            <div class="payment-image">
                <?php 
                // Utiliser des URLs externes pour s'assurer que les images s'affichent
                $imageUrl = "";
                if ($voyage['id'] == "1") {
                    $imageUrl = "https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=2070&auto=format&fit=crop";
                } else if ($voyage['id'] == "2") {
                    $imageUrl = "https://images.unsplash.com/photo-1516483638261-f4dbaf036963?q=80&w=2036&auto=format&fit=crop";
                } else if ($voyage['id'] == "3") {
                    $imageUrl = "https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?q=80&w=2070&auto=format&fit=crop";
                }
                ?>
                <img src="<?php echo $imageUrl; ?>" 
                     alt="<?php echo htmlspecialchars($voyage['nom']); ?>">
            </div>
            <div class="payment-content" style="padding: 1.5rem;">
                <div class="summary-item">
                    <strong>Voyage:</strong> 
                    <span><?php echo htmlspecialchars($voyage['nom']); ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Date de départ:</strong> 
                    <span><?php echo htmlspecialchars($dateDepart); ?></span>
                </div>
                
                <div class="summary-item">
                    <strong>Prix de base:</strong> 
                    <span><?php echo number_format($prixBase, 0, ',', ' '); ?> €</span>
                </div>
                
                <?php if (!empty($activites)): ?>
                <div class="summary-item">
                    <strong>Activités sélectionnées:</strong>
                    <ul>
                        <?php 
                        $prixActivites = 0;
                        foreach ($activites as $activiteId): 
                            foreach ($voyage['activites'] as $activite): 
                                if ($activite['id'] == $activiteId): 
                                    $prixActivites += $activite['prix'];
                        ?>
                                    <li>
                                        <?php echo htmlspecialchars($activite['nom']); ?> 
                                        (+<?php echo number_format($activite['prix'], 0, ',', ' '); ?> €)
                                    </li>
                        <?php 
                                endif; 
                            endforeach; 
                        endforeach; 
                        ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="summary-item total" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <strong>Total à payer:</strong> 
                    <span><?php echo number_format($prixTotal, 0, ',', ' '); ?> €</span>
                </div>
            </div>
        </div>
        
        <div class="payment-form-container">
            <div class="payment-header">
                <h3>Détails de paiement</h3>
            </div>
            <form class="payment-form" method="POST" action="">
                <div class="form-group">
                    <label for="cardholder">Nom du titulaire de la carte</label>
                    <input type="text" id="cardholder" name="cardholder" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label for="card_number">Numéro de carte</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                </div>
                
                <div class="card-details">
                    <div class="form-group">
                        <label for="expiry_date">Date d'expiration (MM/AA)</label>
                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/AA" maxlength="5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label for="montant">Montant à payer</label>
                    <input type="text" id="montant" name="montant" value="<?php echo number_format($prixTotal, 0, ',', ' '); ?> €" readonly>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary pay-button">Procéder au paiement</button>
                </div>
                
                <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                    Cette transaction est 100% sécurisée.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// Formatage automatique du numéro de carte
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formatted = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    
    e.target.value = formatted;
});

// Formatage de la date d'expiration
document.getElementById('expiry_date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formatted = '';
    
    if (value.length > 0) {
        formatted = value.substring(0, 2);
        if (value.length > 2) {
            formatted += '/' + value.substring(2, 4);
        }
    }
    
    e.target.value = formatted;
});

// Validation du CVV (seulement des chiffres)
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
});
</script>

<style>
.payment-container {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 2rem;
    margin: 2rem auto;
}

.payment-summary, .payment-form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.payment-header {
    background: var(--primary-color);
    color: white;
    padding: 1rem 1.5rem;
}

.payment-header h3 {
    margin: 0;
    font-size: 1.2rem;
}

.payment-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.payment-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.payment-content {
    padding: 1.5rem;
}

.summary-item {
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.summary-item ul {
    margin: 0.5rem 0 0;
    padding-left: 1.5rem;
}

.summary-item li {
    margin-bottom: 0.25rem;
}

.summary-item.total {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.payment-form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-actions {
    margin-top: 2rem;
}

.pay-button {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    font-weight: bold;
    text-transform: uppercase;
}

/* Dark mode */
[data-theme="dark"] .payment-summary, 
[data-theme="dark"] .payment-form-container {
    background: #2d2d2d;
    color: #e1e1e1;
}

[data-theme="dark"] .payment-header {
    background: #4169E1;
}

[data-theme="dark"] .form-group input {
    background: #1a1a1a;
    border-color: #404040;
    color: #e1e1e1;
}

[data-theme="dark"] .summary-item.total {
    color: #5d8aff;
    border-color: #404040;
}

/* Responsive */
@media (max-width: 768px) {
    .payment-container {
        grid-template-columns: 1fr;
    }
    
    .card-details {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 