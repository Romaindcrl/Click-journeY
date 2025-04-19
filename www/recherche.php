<?php
require_once __DIR__ . '/includes/header.php';

// Récupérer le terme de recherche
$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

// Chargement des voyages
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$data = json_decode($voyagesJson, true);
$voyages = $data['voyages'] ?? [];

// Filtrer les voyages si un terme de recherche est défini
$filteredVoyages = [];
if (!empty($searchTerm)) {
    foreach ($voyages as $voyage) {
        // Recherche dans le nom, la description ou les activités
        if (
            stripos($voyage['nom'], $searchTerm) !== false ||
            stripos($voyage['description'], $searchTerm) !== false
        ) {
            $filteredVoyages[] = $voyage;
            continue;
        }
        
        // Recherche dans les activités
        foreach ($voyage['activites'] as $activite) {
            if (stripos($activite['nom'], $searchTerm) !== false) {
                $filteredVoyages[] = $voyage;
                break;
            }
        }
    }
} else {
    $filteredVoyages = $voyages;
}
?>

<div class="page-container">
    <h1 class="page-title">Recherche de voyages</h1>
    
    <div class="search-form">
        <form action="recherche.php" method="GET">
            <div class="search-input">
                <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Rechercher un voyage, une destination, une activité...">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($searchTerm)): ?>
        <div class="search-results-header">
            <h2>Résultats pour "<?php echo htmlspecialchars($searchTerm); ?>"</h2>
            <p><?php echo count($filteredVoyages); ?> voyage(s) trouvé(s)</p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($filteredVoyages)): ?>
        <div class="no-results">
            <p>Aucun voyage ne correspond à votre recherche.</p>
            <?php if (!empty($searchTerm)): ?>
                <p>Essayez avec d'autres mots-clés ou <a href="voyages.php">consultez tous nos voyages</a>.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sort-options">
            <label for="sort-select">Trier par:</label>
            <select id="sort-select">
                <option value="nom-asc">Nom (A-Z)</option>
                <option value="nom-desc">Nom (Z-A)</option>
                <option value="prix-asc">Prix (croissant)</option>
                <option value="prix-desc">Prix (décroissant)</option>
            </select>
        </div>
        
        <div class="voyages-grid" id="voyages-grid">
            <?php foreach ($filteredVoyages as $voyage): ?>
                <div class="voyage-card" 
                     data-nom="<?php echo htmlspecialchars($voyage['nom']); ?>"
                     data-prix="<?php echo $voyage['prix']; ?>">
                    <img src="<?php echo htmlspecialchars($voyage['image']); ?>" 
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
                            <a href="personnalisation.php?id=<?php echo $voyage['id']; ?>" class="btn btn-primary">Réserver ce voyage</a>
                        <?php else: ?>
                            <a href="connexion.php" class="btn btn-primary">Connectez-vous pour réserver</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-select');
    const voyagesGrid = document.getElementById('voyages-grid');
    
    if (sortSelect && voyagesGrid) {
        sortSelect.addEventListener('change', function() {
            sortVoyages(this.value);
        });
    }
    
    function sortVoyages(sortValue) {
        const voyages = Array.from(voyagesGrid.querySelectorAll('.voyage-card'));
        
        voyages.sort((a, b) => {
            switch (sortValue) {
                case 'nom-asc':
                    return a.dataset.nom.localeCompare(b.dataset.nom);
                case 'nom-desc':
                    return b.dataset.nom.localeCompare(a.dataset.nom);
                case 'prix-asc':
                    return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
                case 'prix-desc':
                    return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
                default:
                    return 0;
            }
        });
        
        // Vider la grille
        voyagesGrid.innerHTML = '';
        
        // Réinsérer les éléments triés
        voyages.forEach(voyage => voyagesGrid.appendChild(voyage));
    }
});
</script>

<style>
.search-form {
    margin-bottom: 2rem;
}

.search-input {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
}

.search-input input {
    flex: 1;
    padding: 0.8rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 8px 0 0 8px;
    font-size: 1rem;
}

.search-input button {
    border-radius: 0 8px 8px 0;
    padding: 0.8rem 1.5rem;
}

.search-results-header {
    margin-bottom: 2rem;
    text-align: center;
}

.search-results-header h2 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.sort-options {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-bottom: 1.5rem;
    gap: 0.5rem;
}

.sort-options select {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background-color: var(--card-bg);
    color: var(--text-color);
}

.sort-options select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.no-results {
    text-align: center;
    padding: 3rem;
    background-color: var(--background-color);
    border-radius: 10px;
    margin-top: 2rem;
}

.no-results a {
    color: var(--primary-color);
    text-decoration: underline;
}

/* Dark mode */
[data-theme="dark"] .search-input input {
    background-color: var(--background-color);
    color: var(--text-color);
}

[data-theme="dark"] .sort-options select {
    background-color: var(--background-color);
    color: var(--text-color);
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 