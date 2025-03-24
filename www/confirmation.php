<?php
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['status'])) {
    header('Location: voyages.php');
    exit();
}

$status = $_GET['status'];
$success = $status === 'success';
?>

<div class="page-container">
    <?php if ($success): ?>
        <div class="confirmation-success">
            <h2>Paiement accepté</h2>
            <p>Votre réservation a été confirmée avec succès !</p>
        </div>
        
        <div class="confirmation-message">
            <p>Un email de confirmation vous sera envoyé prochainement avec tous les détails de votre voyage.</p>
            <p>Vous pouvez consulter vos réservations dans votre espace personnel.</p>
        </div>
        
        <div class="confirmation-actions">
            <a href="profil.php" class="btn btn-primary">Voir mes voyages</a>
            <a href="voyages.php" class="btn btn-secondary">Découvrir d'autres voyages</a>
        </div>
    <?php else: ?>
        <div class="confirmation-error">
            <h2>Paiement refusé</h2>
            <p>Une erreur est survenue lors du traitement de votre paiement.</p>
        </div>
        
        <div class="confirmation-message">
            <p>Veuillez réessayer ou contacter notre service client si le problème persiste.</p>
        </div>
        
        <div class="confirmation-actions">
            <a href="paiement.php" class="btn btn-primary">Réessayer le paiement</a>
            <a href="voyages.php" class="btn btn-secondary">Retour aux voyages</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 