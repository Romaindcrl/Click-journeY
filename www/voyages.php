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
    <h1 class="page-title">Nos Voyages</h1>

    <div class="search-form">
        <form action="voyages.php" method="GET">
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
                <option value="duree-asc">Durée (croissante)</option>
                <option value="duree-desc">Durée (décroissante)</option>
            </select>
        </div>

        <div class="voyages-grid" id="voyages-grid">
            <?php foreach ($filteredVoyages as $voyage): ?>
                <div class="voyage-card"
                    data-nom="<?php echo htmlspecialchars($voyage['nom']); ?>"
                    data-prix="<?php echo $voyage['prix']; ?>"
                    data-duree="<?php echo isset($voyage['duree']) ? $voyage['duree'] : 7; ?>">
                    <img src="<?php echo htmlspecialchars($voyage['image']); ?>"
                        alt="<?php echo htmlspecialchars($voyage['nom']); ?>"
                        class="voyage-image">

                    <div class="voyage-content">
                        <h3 class="voyage-title"><?php echo htmlspecialchars($voyage['nom']); ?></h3>
                        <p class="voyage-description"><?php echo htmlspecialchars($voyage['description']); ?></p>

                        <div class="voyage-info">
                            <div class="voyage-price">
                                À partir de <?= number_format($voyage['prix'], 0, ',', ' ') ?> <span>€</span>
                            </div>

                            <div class="voyage-duree">
                                <i class="fas fa-clock"></i>
                                <span><?= isset($voyage['duree']) ? $voyage['duree'] : 7 ?> jours</span>
                            </div>
                        </div>

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
                    </div>

                    <div class="voyage-footer">
                        <div class="voyage-buttons">
                            <a href="voyage-details.php?id=<?= $voyage['id'] ?>" class="btn-details">Voir détails</a>
                            <?php if (isset($_SESSION['user'])): ?>
                                <a href="personnalisation.php?id=<?php echo $voyage['id']; ?>" class="btn-reserve">Réserver</a>
                            <?php else: ?>
                                <a href="connexion.php" class="btn-reserve">Se connecter</a>
                            <?php endif; ?>
                        </div>
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
                    case 'duree-asc':
                        return parseInt(a.dataset.duree) - parseInt(b.dataset.duree);
                    case 'duree-desc':
                        return parseInt(b.dataset.duree) - parseInt(a.dataset.duree);
                    default:
                        return 0;
                }
            });

            // Vider la grille
            voyagesGrid.innerHTML = '';

            // Réinsérer les éléments triés
            voyages.forEach(voyage => voyagesGrid.appendChild(voyage));
        }

        // Assurer que les liens fonctionnent correctement
        const allButtons = document.querySelectorAll('.btn-details, .btn-reserve');

        // Ajouter un écouteur d'événements pour chaque bouton
        allButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Obtenir l'URL
                const url = this.getAttribute('href');

                // Rediriger vers l'URL
                window.location.href = url;

                // Ajouter un log pour déboguer
                console.log('Navigation vers: ' + url);
            });
        });
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>