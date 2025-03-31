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
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];

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

// Calculer le prix total initial (prix de base)
$prixTotal = $voyage['prix'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_depart = $_POST['date_depart'] ?? '';
    $activites = $_POST['activites'] ?? [];
    
    // Calculer le prix des activités sélectionnées
    $prixActivites = 0;
    if (!empty($activites)) {
        foreach ($activites as $activiteId) {
            foreach ($voyage['activites'] as $activite) {
                if ($activite['id'] == $activiteId) {
                    $prixActivites += $activite['prix'];
                    break;
                }
            }
        }
    }
    
    // Prix total = prix de base + prix des activités
    $prixTotal = $voyage['prix'] + $prixActivites;
    
    // Stocker les informations de réservation dans la session
    $_SESSION['reservation'] = [
        'voyageId' => $voyageId,
        'date_depart' => $date_depart,
        'activites' => $activites,
        'prix_total' => $prixTotal
    ];
    
    // Rediriger vers la page de paiement
    header('Location: paiement.php');
    exit();
}
?>

<div class="page-container">
    <h1 class="page-title">Personnalisez votre voyage</h1>
    
    <div class="voyage-summary">
        <div class="voyage-image">
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
        <div class="voyage-details">
            <h2><?php echo htmlspecialchars($voyage['nom']); ?></h2>
            <p><?php echo htmlspecialchars($voyage['description']); ?></p>
            <div class="voyage-price">Prix de base : <?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</div>
        </div>
    </div>

    <form method="POST" class="customization-form">
        <div class="form-group">
            <label for="date_depart">Date de départ</label>
            <input type="date" id="date_depart" name="date_depart" required 
                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
        </div>

        <div class="form-group">
            <label>Activités disponibles</label>
            <div class="activities-grid">
                <?php foreach ($voyage['activites'] as $activite): ?>
                    <div class="activity-checkbox">
                        <input type="checkbox" id="activite_<?php echo htmlspecialchars($activite['id']); ?>" 
                               name="activites[]" value="<?php echo htmlspecialchars($activite['id']); ?>"
                               class="custom-checkbox">
                        <label for="activite_<?php echo htmlspecialchars($activite['id']); ?>">
                            <?php echo htmlspecialchars($activite['nom']); ?> 
                            <span class="activity-price">(+<?php echo number_format($activite['prix'], 0, ',', ' '); ?> €)</span>
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

<style>
.voyage-summary {
    display: flex;
    gap: 2rem;
    margin-bottom: 2rem;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.voyage-image {
    flex: 0 0 300px;
}

.voyage-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.voyage-details {
    flex: 1;
    padding: 1.5rem;
}

.voyage-details h2 {
    margin-top: 0;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.voyage-price {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-top: 1rem;
}

.customization-form {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    margin-top: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: var(--text-color);
}

.form-group input[type="date"] {
    width: 100%;
    max-width: 300px;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.activities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.activity-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.custom-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.activity-price {
    color: var(--primary-color);
    font-weight: bold;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Dark Mode */
[data-theme="dark"] .voyage-summary,
[data-theme="dark"] .customization-form {
    background: #2d2d2d;
    color: #e1e1e1;
}

[data-theme="dark"] .voyage-details h2,
[data-theme="dark"] .voyage-price {
    color: #4169E1;
}

[data-theme="dark"] .form-group label {
    color: #e1e1e1;
}

[data-theme="dark"] .form-group input[type="date"] {
    background: #1a1a1a;
    border-color: #404040;
    color: #e1e1e1;
}

[data-theme="dark"] .activity-price {
    color: #5d8aff;
}

/* Responsive */
@media (max-width: 768px) {
    .voyage-summary {
        flex-direction: column;
    }
    
    .voyage-image {
        flex: 0 0 200px;
    }
    
    .activities-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 