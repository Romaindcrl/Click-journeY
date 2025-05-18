<head>
    <link rel="stylesheet" href="src/css/voyages.css">
    <link rel="stylesheet" href="src/css/form-validation.css">
    <link rel="stylesheet" href="src/css/voyages-pagination.css">
</head>
<?php
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="src/css/voyages.css">
<link rel="stylesheet" href="src/css/voyages-pagination.css">
<link rel="stylesheet" href="src/css/search.css">
<?php

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

// Faire le tri des voyages côté serveur
$sortValue = isset($_GET['sort']) ? $_GET['sort'] : 'nom-asc';
if (!empty($sortValue)) {
    switch ($sortValue) {
        case 'nom-asc':
            usort($filteredVoyages, fn($a, $b) => strcmp($a['nom'], $b['nom']));
            break;
        case 'nom-desc':
            usort($filteredVoyages, fn($a, $b) => strcmp($b['nom'], $a['nom']));
            break;
        case 'prix-asc':
            usort($filteredVoyages, fn($a, $b) => $a['prix'] <=> $b['prix']);
            break;
        case 'prix-desc':
            usort($filteredVoyages, fn($a, $b) => $b['prix'] <=> $a['prix']);
            break;
        case 'duree-asc':
            usort($filteredVoyages, fn($a, $b) => ($a['duree'] ?? 7) <=> ($b['duree'] ?? 7));
            break;
        case 'duree-desc':
            usort($filteredVoyages, fn($a, $b) => ($b['duree'] ?? 7) <=> ($a['duree'] ?? 7));
            break;
    }
}

// Pagination
$itemsPerPage = 9;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}
$totalVoyages = count($filteredVoyages);
$totalPages = ceil($totalVoyages / $itemsPerPage);
$offset = ($currentPage - 1) * $itemsPerPage;
$paginatedVoyages = array_slice($filteredVoyages, $offset, $itemsPerPage);
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
        <div class="no-voyages">
            <p>Aucun voyage ne correspond à votre recherche "<?php echo htmlspecialchars($searchTerm); ?>".</p>
            <p>Essayez d'autres mots-clés ou <a href="voyages.php">consultez tous nos voyages</a>.</p>
        </div>
    <?php else: ?>
        <form action="voyages.php" method="GET" class="sort-options" id="sort-form">
            <label for="sort-select">Trier par:</label>
            <select id="sort-select" name="sort" onchange="document.getElementById('sort-form').submit()">
                <option value="nom-asc" <?= $sortValue === 'nom-asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                <option value="nom-desc" <?= $sortValue === 'nom-desc' ? 'selected' : '' ?>>Nom (Z-A)</option>
                <option value="prix-asc" <?= $sortValue === 'prix-asc' ? 'selected' : '' ?>>Prix (croissant)</option>
                <option value="prix-desc" <?= $sortValue === 'prix-desc' ? 'selected' : '' ?>>Prix (décroissant)</option>
                <option value="duree-asc" <?= $sortValue === 'duree-asc' ? 'selected' : '' ?>>Durée (croissante)</option>
                <option value="duree-desc" <?= $sortValue === 'duree-desc' ? 'selected' : '' ?>>Durée (décroissante)</option>
            </select>
            <?php if (!empty($searchTerm)): ?>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <?php endif; ?>
        </form>

        <div class="voyages-grid" id="voyages-grid">
            <?php foreach ($paginatedVoyages as $voyage): ?>
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

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" class="page-link">&laquo; Précédent</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                        class="page-link <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" class="page-link">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>