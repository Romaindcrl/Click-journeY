<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

if (!isset($_GET['voyageId'])) {
    header('Location: voyages.php');
    exit();
}

$voyageId = $_GET['voyageId'];

// Lecture des données du voyage
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyages = json_decode($voyagesJson, true)['voyages'];

$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyageId) {
        $voyage = $v;
        break;
    }
}

if (!$voyage) {
    header('Location: voyages.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_depart = $_POST['date_depart'] ?? '';
    $activites = $_POST['activites'] ?? [];
    
    if ($date_depart && !empty($activites)) {
        $_SESSION['reservation'] = [
            'voyage_id' => $voyageId,
            'date_depart' => $date_depart,
            'activites' => $activites,
            'prix_total' => $voyage['prix']
        ];
        
        header('Location: paiement.php');
        exit();
    }
}
?>

<div class="page-container">
    <h1 class="page-title">Personnalisez votre voyage</h1>
    
    <div class="voyage-summary">
        <div class="voyage-image">
            <img src="src/img/<?php echo htmlspecialchars($voyage['image']); ?>" 
                 alt="<?php echo htmlspecialchars($voyage['nom']); ?>">
        </div>
        <div class="voyage-details">
            <h2><?php echo htmlspecialchars($voyage['nom']); ?></h2>
            <p><?php echo htmlspecialchars($voyage['description']); ?></p>
            <div class="voyage-price"><?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</div>
        </div>
    </div>

    <form method="POST" class="customization-form">
        <div class="form-group">
            <label for="date_depart">Date de départ</label>
            <input type="date" id="date_depart" name="date_depart" required 
                   min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-group">
            <label>Activités incluses</label>
            <div class="activities-grid">
                <?php foreach ($voyage['activites'] as $activite): ?>
                    <div class="activity-checkbox">
                        <input type="checkbox" id="activite_<?php echo htmlspecialchars($activite); ?>" 
                               name="activites[]" value="<?php echo htmlspecialchars($activite); ?>" 
                               checked>
                        <label for="activite_<?php echo htmlspecialchars($activite); ?>">
                            <?php echo htmlspecialchars($activite); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Continuer vers le paiement</button>
            <a href="voyages.php" class="btn btn-secondary">Retour aux voyages</a>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 