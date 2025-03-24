<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Lecture du fichier voyages.json
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyages = json_decode($voyagesJson, true)['voyages'];
?>

<div class="page-container">
    <h1 class="page-title">Nos Voyages</h1>
    
    <div class="voyages-grid">
        <?php foreach ($voyages as $voyage): ?>
            <div class="voyage-card">
                <img src="src/img/<?php echo htmlspecialchars($voyage['image']); ?>" 
                     alt="<?php echo htmlspecialchars($voyage['nom']); ?>" 
                     class="voyage-image">
                
                <div class="voyage-content">
                    <h2 class="voyage-title"><?php echo htmlspecialchars($voyage['nom']); ?></h2>
                    <p class="voyage-description"><?php echo htmlspecialchars($voyage['description']); ?></p>
                    <div class="voyage-price"><?php echo number_format($voyage['prix'], 0, ',', ' '); ?> €</div>
                    
                    <div class="voyage-activities">
                        <h4>Activités incluses :</h4>
                        <ul>
                            <?php foreach ($voyage['activites'] as $activite): ?>
                                <li><?php echo htmlspecialchars($activite); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="personnalisation.php?voyageId=<?php echo $voyage['id']; ?>" class="btn btn-primary">Réserver ce voyage</a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn btn-primary">Connectez-vous pour réserver</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 