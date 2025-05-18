<?php
require_once __DIR__ . '/check_auth.php';
checkAuth();
require_once __DIR__ . '/includes/header.php';

// Détection du mode modification
$editing = false;
$editIndex = null;
$initialDate = '';
$initialNbParticipants = 1;
if (isset($_GET['index']) && isset($_SESSION['reservations'][$_GET['index']])) {
    $editing = true;
    $editIndex = intval($_GET['index']);
    $reservationSession = $_SESSION['reservations'][$editIndex];
    $initialDate = $reservationSession['date_depart'];
    $initialNbParticipants = $reservationSession['nb_participants'];
}

// Vérifier si le paramètre id est présent dans l'URL
$voyageId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($voyageId === 0) {
    $_SESSION['flash_message'] = 'Aucun voyage sélectionné.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Récupérer les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';

if (!file_exists($voyagesFile)) {
    $voyagesFile = __DIR__ . '/data/voyages.json';
}

if (!file_exists($voyagesFile)) {
    $_SESSION['flash_message'] = 'Erreur: fichier de voyages introuvable.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Rechercher le voyage par ID
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] == $voyageId) { // Utiliser == au lieu de === pour comparer des chiffres qui peuvent être de types différents
        $voyage = $v;
        break;
    }
}

if (!$voyage) {
    $_SESSION['flash_message'] = 'Voyage non trouvé.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Préparation des comptes d'activités initiales en modification (par jour et activité)
if ($editing) {
    $duree = $voyage['duree'] ?? 7;
    $numActs = count($voyage['activites']);
    $nestedCounts = [];
    for ($dayIdx = 0; $dayIdx < $duree; $dayIdx++) {
        $nestedCounts[$dayIdx] = array_fill(0, $numActs, 0);
    }
    foreach ($reservationSession['activites'] as $rAct) {
        $dayIdx = intval($rAct['day']) - 1;
        foreach ($voyage['activites'] as $i => $act) {
            if ($act['nom'] === $rAct['nom']) {
                $nestedCounts[$dayIdx][$i] = intval($rAct['count']);
                break;
            }
        }
    }
} else {
    $nestedCounts = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier et récupérer les données du formulaire
    $dateDepart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $nbParticipants = isset($_POST['nb_participants']) ? intval($_POST['nb_participants']) : 1;
    // Calcul du prix total initial en fonction du nombre de participants
    $prixTotal = $voyage['prix'] * $nbParticipants;

    // Débogage - Vérifier les valeurs soumises
    error_log("Formulaire soumis: date=" . $dateDepart . ", participants=" . $nbParticipants . ", prix=" . $prixTotal);

    if (empty($dateDepart)) {
        $_SESSION['flash_message'] = 'Veuillez sélectionner une date de départ.';
        $_SESSION['flash_type'] = 'error';
    } else {
        $activites = [];
        // Récupérer le nombre de bénéficiaires par activité et recalculer le prix pour chaque jour
        if (isset($_POST['activites_counts']) && is_array($_POST['activites_counts'])) {
            foreach ($_POST['activites_counts'] as $dayIdx => $countsForDay) {
                if (is_array($countsForDay)) {
                    foreach ($countsForDay as $index => $count) {
                        $i = intval($index);
                        $q = intval($count);
                        if ($q > 0 && isset($voyage['activites'][$i])) {
                            $act = $voyage['activites'][$i];
                            $activites[] = [
                                'nom' => $act['nom'],
                                'prix' => $act['prix'],
                                'count' => $q,
                                'day' => intval($dayIdx) + 1
                            ];
                            $prixTotal += $act['prix'] * $q;
                        }
                    }
                }
            }
        }

        // Stocker les informations de réservation dans la session
        $_SESSION['reservation'] = [
            'voyage_id' => $voyageId,
            'voyage_nom' => $voyage['nom'],
            'voyage_image' => $voyage['image'],
            'date_depart' => $dateDepart,
            'nb_participants' => $nbParticipants,
            'activites' => $activites,
            'prix_total' => $prixTotal
        ];

        // Débogage - Vérifier la session avant redirection
        error_log("Session reservation créée. Redirection vers paiement.php");

        // Rediriger vers la page de paiement
        header('Location: paiement.php');
        exit;
    }
}

// Générer les options pour le nombre de participants
$participantsOptions = '';
for ($i = 1; $i <= 10; $i++) {
    if ($editing) {
        $selected = ($i === $initialNbParticipants) ? 'selected' : '';
    } else {
        $selected = ($i === 1) ? 'selected' : '';
    }
    $participantsOptions .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
}

// Date minimale de départ (demain)
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<div class="page-container">
    <h1 class="page-title"><?= $editing ? 'Modifier votre voyage' : 'Personnalisez votre voyage' ?></h1>

    <?php if ($voyage): ?>
        <div class="personnalisation-container">
            <div class="voyage-details">
                <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['nom']) ?>" class="voyage-image">

                <h2 class="voyage-title"><?= htmlspecialchars($voyage['nom']) ?></h2>
                <p class="voyage-description"><?= htmlspecialchars($voyage['description']) ?></p>

                <form id="personnalisation-form" method="post" action="panier.php">
                    <?php if ($editing): ?>
                        <input type="hidden" name="action" value="modifier_reservation">
                        <input type="hidden" name="index" value="<?= $editIndex ?>">
                    <?php endif; ?>
                    <input type="hidden" name="voyage_id" value="<?= $voyageId ?>">

                    <div class="form-section">
                        <h3>Informations de base</h3>

                        <div class="input-group">
                            <label for="date_depart">Date de départ:</label>
                            <input type="date" id="date_depart" name="date_depart" class="form-control" min="<?= $tomorrow ?>" value="<?= htmlspecialchars($initialDate) ?>" required aria-describedby="date-help">
                            <small id="date-help" class="form-text">Sélectionnez une date à partir de demain</small>
                        </div>

                        <div class="input-group">
                            <label for="nb_participants">Nombre de participants:</label>
                            <select id="nb_participants" name="nb_participants" class="form-control" aria-describedby="participants-help">
                                <?= $participantsOptions ?>
                            </select>
                            <small id="participants-help" class="form-text">Le prix sera ajusté en fonction du nombre de participants</small>
                        </div>
                    </div>

                    <?php if (isset($voyage['activites']) && !empty($voyage['activites'])): ?>
                        <?php $duree = $voyage['duree'] ?? 7; ?>
                        <div class="form-section">
                            <h3>Options supplémentaires</h3>
                            <p class="section-description">Pour chaque jour, indiquez combien de voyageurs souhaitent bénéficier des activités :</p>
                            <?php for ($day = 1; $day <= $duree; $day++): ?>
                                <div class="day-section">
                                    <div class="day-header" onclick="toggleDay(<?= $day - 1 ?>)">
                                        <h4>Jour <?= $day ?></h4>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                    <div class="day-content <?= ($day > 1) ? 'collapsed' : '' ?>" id="day-content-<?= $day - 1 ?>">
                                        <div class="options-container" data-day-index="<?= $day - 1 ?>"></div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <script>
                            window.ACTIVITES_DATA = <?= json_encode($voyage['activites']); ?>;
                            window.DUREE = <?= $duree ?>;
                            window.INITIAL_COUNTS = <?= json_encode($nestedCounts); ?>;
                        </script>
                    <?php endif; ?>

                    <div class="order-recap">
                        <h3 class="recap-title">Récapitulatif de votre voyage</h3>

                        <div class="recap-content">
                            <div class="recap-item">
                                <span>Voyage</span>
                                <strong><?= htmlspecialchars($voyage['nom']) ?></strong>
                            </div>

                            <div class="recap-item">
                                <span>Prix de base</span>
                                <strong id="price-per-person" data-base="<?= $voyage['prix'] ?>"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</strong>
                            </div>

                            <div class="recap-item">
                                <span>Participants</span>
                                <strong><span id="nb-participants">1</span></strong>
                            </div>

                            <!-- Affichage dynamique des activités sélectionnées -->
                            <div id="selected-activities-container" style="display: none; margin-top: 1rem;">
                                <h4>Activités sélectionnées</h4>
                                <div id="selected-activities-list">
                                    <!-- Les activités sélectionnées seront affichées ici par JavaScript -->
                                </div>
                            </div>

                            <div class="recap-separator"></div>

                            <div class="recap-item total-price">
                                <span>Prix total</span>
                                <strong><span id="total-price"><?= number_format($voyage['prix'], 0, ',', ' ') ?> €</span></strong>
                            </div>
                        </div>

                        <input type="hidden" id="prix_total_input" name="prix_total" value="<?= $voyage['prix'] ?>">
                        <button type="submit" class="btn-reserver">Passer au paiement</button>

                        <div class="text-center mt-3">
                            <a href="voyages.php" class="btn btn-link">Retour aux voyages</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Voyage non trouvé. <a href="voyages.php">Retour à la liste des voyages</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>