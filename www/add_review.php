<?php
// Bufferiser la sortie
ob_start();

// Inclure le header et les fonctions d'authentification
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
?>
<link rel="stylesheet" href="src/css/add-review.css">


<?php
// Vérifier si l'utilisateur est connecté
checkAuth();

// Récupérer l'ID de la commande et l'ID du voyage depuis les paramètres d'URL
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$voyageId = isset($_GET['voyage_id']) ? intval($_GET['voyage_id']) : null;

// Charger les commandes pour vérifier si la commande existe et appartient à l'utilisateur connecté
$commandesJson = file_get_contents(__DIR__ . '/../data/commandes.json');
$commandesData = json_decode($commandesJson, true);
$commandes = $commandesData['commandes'] ?? [];

$commande = null;
foreach ($commandes as $c) {
  if ($c['id'] === $orderId && $c['user_id'] === ($_SESSION['user']['id'] ?? null)) {
    $commande = $c;
    break;
  }
}

// Charger les avis existants pour vérifier si l'avis a déjà été soumis
$reviewsJson = file_get_contents(__DIR__ . '/../data/avis.json');
$reviewsData = json_decode($reviewsJson, true);
$reviewsList = $reviewsData['avis'] ?? [];

$hasReviewed = false;
if ($commande) {
  foreach ($reviewsList as $r) {
    if (isset($r['order_id']) && $r['order_id'] == $orderId && $r['user_id'] == ($_SESSION['user']['id'] ?? null)) {
      $hasReviewed = true;
      break;
    }
  }
}


// Rediriger si la commande est invalide ou si l'avis a déjà été soumis
if (!$commande) {
  $_SESSION['flash_message'] = "Commande introuvable ou vous n'êtes pas autorisé à laisser un avis.";
  $_SESSION['flash_type'] = 'error';
  header('Location: profil.php');
  exit;
}

if ($hasReviewed) {
  $_SESSION['flash_message'] = "Vous avez déjà laissé un avis pour cette commande.";
  $_SESSION['flash_type'] = 'warning';
  header('Location: profil.php');
  exit;
}

// Charger les voyages pour afficher le nom du voyage
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

$voyage = null;
foreach ($voyages as $v) {
  if ($v['id'] == $voyageId) {
    $voyage = $v;
    break;
  }
}

// Gérer la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Inclure le fichier de traitement de soumission d'avis
  // Note: Le traitement réel se fait dans submit_review.php via fetch/AJAX normalement
  // Ici, pour la démo, on pourrait simuler ou rediriger.
  // La logique AJAX depuis le front-end est préférable pour une vraie application.
  // Pour l'instant, on va juste afficher un message ou rediriger après succès théorique.

  // Simuler le succès et rediriger vers le profil
  $_SESSION['flash_message'] = "Avis soumis avec succès (simulé).";
  $_SESSION['flash_type'] = 'success';
  header('Location: profil.php');
  exit;
}

?>

<div class="page-container">
  <div class="add-review-card">
    <h1 class="page-title">Laisser un avis pour le voyage : <?php echo htmlspecialchars($voyage['nom'] ?? 'N/A'); ?></h1>

    <div class="form-container">
      <form action="submit_review.php" method="POST">
        <input type="hidden" name="commande_id" value="<?php echo htmlspecialchars($orderId); ?>">
        <input type="hidden" name="voyage_id" value="<?php echo htmlspecialchars($voyageId); ?>">

        <div class="form-group rating-group">
          <label>Note :</label>
          <div class="rating-stars">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 étoiles">★</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 étoiles">★</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 étoiles">★</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 étoiles">★</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 étoile">★</label>
          </div>
        </div>

        <div class="form-group">
          <label for="comment">Commentaire :</label>
          <textarea id="comment" name="comment" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Soumettre l'avis</button>
      </form>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
ob_end_flush();
?>