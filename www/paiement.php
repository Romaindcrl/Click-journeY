<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Rediriger si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['flash_message'] = 'Veuillez vous connecter pour accéder à cette page.';
    $_SESSION['flash_type'] = 'info';
    header("Location: connexion.php");
    exit();
}

// Vérifier qu'une réservation existe
if (!isset($_SESSION['reservations']) || empty($_SESSION['reservations'])) {
    $_SESSION['flash_message'] = 'Aucune réservation trouvée. Veuillez choisir un voyage.';
    $_SESSION['flash_type'] = 'error';
    header('Location: voyages.php');
    exit;
}

// Inclure la fonction d'API Key
require_once __DIR__ . '/api/getapikey.php';

// Charger les réservations du panier
$reservations = $_SESSION['reservations'];
// Calculer le montant total pour tous les voyages
$montant_total = 0;
foreach ($reservations as $res) {
    $montant_total += $res['prix_total'];
}
// Formater le montant pour la plateforme de paiement
$montant = number_format($montant_total, 2, '.', '');

// Charger les données du voyage
$voyagesFile = __DIR__ . '/../data/voyages.json';
if (!file_exists($voyagesFile)) {
    $voyagesFile = __DIR__ . '/data/voyages.json';
}
$voyagesJson = file_get_contents($voyagesFile);
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Préparation pour CYBank
$transaction = uniqid('TXN');
$vendeur = 'MIM_B';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$retour = $protocol . '://' . $host . $basePath . '/retour_paiement.php?session=' . session_id() . '&user=' . $_SESSION['user']['id'];
$api_key = getAPIKey($vendeur);
$control = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $retour . '#');

// Inclure l'en-tête HTML
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title">Paiement</h1>

    <div class="payment-container">
        <?php foreach ($reservations as $reservation): ?>
            <?php
            // Initialiser les données pour chaque réservation
            $voyage_id = $reservation['voyage_id'];
            $date_depart = $reservation['date_depart'];
            $nb_participants = $reservation['nb_participants'];
            $activites = $reservation['activites'] ?? [];
            // Rechercher le voyage correspondant
            $voyage = null;
            foreach ($voyages as $v) {
                if ($v['id'] == $voyage_id) {
                    $voyage = $v;
                    break;
                }
            }
            // Calcul des dates
            $duree = $voyage['duree'] ?? 7;
            $dateRetour = date('Y-m-d', strtotime($date_depart . ' + ' . $duree . ' days'));
            $dateDepartFormatted = DateTime::createFromFormat('Y-m-d', $date_depart);
            $dateRetourFormatted = DateTime::createFromFormat('Y-m-d', $dateRetour);
            ?>
            <div class="payment-summary">
                <div class="card-header">
                    <h2>Récapitulatif de votre réservation</h2>
                </div>
                <div class="card-body">
                    <div class="trip-summary">
                        <div class="trip-image">
                            <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['nom']); ?>">
                        </div>
                        <div class="trip-details">
                            <h3><?php echo htmlspecialchars($voyage['nom']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($voyage['description'], 0, 100)); ?>...</p>

                            <div class="trip-info">
                                <div class="info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Départ: <?php echo $dateDepartFormatted->format('d/m/Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>Retour: <?php echo $dateRetourFormatted->format('d/m/Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users"></i>
                                    <span>Participants: <?php echo $nb_participants; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($activites)): ?>
                        <div class="selected-activities">
                            <h4>Activités sélectionnées</h4>
                            <ul>
                                <?php foreach ($activites as $activite): ?>
                                    <li>
                                        <span class="activity-name"><?php echo htmlspecialchars($activite['nom']); ?></span>
                                        <span class="activity-price"><?php echo number_format($activite['prix'] * $nb_participants, 0, ',', ' '); ?> €</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="price-summary">
                        <div class="price-item">
                            <span>Prix du voyage (<?php echo $nb_participants; ?> personne<?php echo $nb_participants > 1 ? 's' : ''; ?>)</span>
                            <span><?php echo number_format($voyage['prix'] * $nb_participants, 0, ',', ' '); ?> €</span>
                        </div>

                        <?php if (!empty($activites)): ?>
                            <div class="price-item">
                                <span>Options additionnelles</span>
                                <?php
                                $activitesTotal = 0;
                                foreach ($activites as $activite) {
                                    $activitesTotal += $activite['prix'] * $nb_participants;
                                }
                                ?>
                                <span><?php echo number_format($activitesTotal, 0, ',', ' '); ?> €</span>
                            </div>
                        <?php endif; ?>

                        <div class="price-item total">
                            <span>Total</span>
                            <span><?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> €</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Total global -->
        <div class="payment-summary total-summary">
            <div class="card-header">
                <h2>Total à payer</h2>
            </div>
            <div class="card-body">
                <div class="price-summary">
                    <div class="price-item total">
                        <span>Total</span>
                        <span><?php echo number_format($montant_total, 0, ',', ' '); ?> €</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-form">
            <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
                <input type="hidden" name="transaction" value="<?php echo $transaction; ?>">
                <input type="hidden" name="montant" value="<?php echo $montant; ?>">
                <input type="hidden" name="vendeur" value="<?php echo $vendeur; ?>">
                <input type="hidden" name="retour" value="<?php echo $retour; ?>">
                <input type="hidden" name="control" value="<?php echo $control; ?>">
                <button type="submit" class="btn-payment">Carte bancaire</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>