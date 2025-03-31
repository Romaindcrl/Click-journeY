<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Lecture du fichier voyages.json
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];
?>

<div class="page-container">
    <h1 class="page-title">Nos Voyages</h1>
    
    <div class="voyages-grid">
        <?php if (empty($voyages)): ?>
            <p class="no-voyages">Aucun voyage n'est disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($voyages as $voyage): ?>
                <div class="voyage-card">
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
                                    <li>
                                        <?php echo htmlspecialchars($activite['nom']); ?> 
                                        <small>(+<?php echo number_format($activite['prix'], 0, ',', ' '); ?> €)</small>
                                    </li>
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
        <?php endif; ?>
    </div>
</div>

<style>
.voyages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.voyage-card {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    background: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.voyage-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.voyage-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.voyage-content {
    padding: 1.5rem;
}

.voyage-title {
    margin-top: 0;
    margin-bottom: 0.75rem;
    font-size: 1.5rem;
    color: var(--primary-color);
}

.voyage-description {
    margin-bottom: 1rem;
    color: var(--text-color);
}

.voyage-price {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.voyage-activities {
    margin-bottom: 1.5rem;
}

.voyage-activities h4 {
    margin-bottom: 0.5rem;
}

.voyage-activities ul {
    padding-left: 1.25rem;
}

.voyage-activities li {
    margin-bottom: 0.25rem;
}

.voyage-activities small {
    color: #666;
}

.no-voyages {
    grid-column: 1 / -1;
    text-align: center;
    padding: 2rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}

/* Dark mode styles */
[data-theme="dark"] .voyage-card {
    background: #2d2d2d;
}

[data-theme="dark"] .voyage-title {
    color: #4169E1;
}

[data-theme="dark"] .voyage-description,
[data-theme="dark"] .voyage-activities li {
    color: #e1e1e1;
}

[data-theme="dark"] .voyage-activities small {
    color: #aaa;
}

[data-theme="dark"] .no-voyages {
    background: rgba(255, 255, 255, 0.05);
    color: #e1e1e1;
}

@media (max-width: 768px) {
    .voyages-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 