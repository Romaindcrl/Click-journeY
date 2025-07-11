<?php
// Bufferiser la sortie pour éviter l'erreur de headers déjà envoyés
ob_start();
// Inclure le header et démarrer la session
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';

// Vérifier si l'utilisateur est connecté
checkAuth();

// ID de l'utilisateur connecté (cast en integer)
$sessionUserId = intval($_SESSION['user']['id'] ?? 0);
// Récupérer l'ID de l'utilisateur à afficher
$userId = isset($_GET['id']) ? intval($_GET['id']) : $sessionUserId;

// Vérifier si l'utilisateur demandé est l'utilisateur connecté ou si l'utilisateur connecté est admin
$isOwner = $userId === $sessionUserId;
$isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';

if (!$isOwner && !$isAdmin) {
    // Rediriger si l'utilisateur n'a pas les droits
    $_SESSION['flash_message'] = "Vous n'avez pas les droits pour accéder à ce profil.";
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Charger les données des utilisateurs
$usersJson = file_get_contents(__DIR__ . '/../data/users.json');
$usersData = json_decode($usersJson, true);
$users = $usersData['users'] ?? [];

// Rechercher l'utilisateur par ID
$user = null;
foreach ($users as $u) {
    if ($u['id'] === $userId) {
        $user = $u;
        break;
    }
}

if (!$user) {
    // Rediriger si l'utilisateur n'existe pas
    $_SESSION['flash_message'] = "Cet utilisateur n'existe pas.";
    $_SESSION['flash_type'] = 'error';
    header('Location: index.php');
    exit;
}

// Charger les voyages achetés
$commandesJson = file_get_contents(__DIR__ . '/../data/commandes.json');
$commandesData = json_decode($commandesJson, true);
$commandes = $commandesData['commandes'] ?? [];

// Filtrer les commandes de l'utilisateur
$userCommandes = [];
foreach ($commandes as $commande) {
    if (intval($commande['user_id']) === $userId) {
        $userCommandes[] = $commande;
    }
}

// Traitement du formulaire de modification de profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Mise à jour des informations utilisateur (seulement si postées)
    $user['nom'] = isset($_POST['nom']) ? trim($_POST['nom']) : $user['nom'];
    $user['prenom'] = isset($_POST['prenom']) ? trim($_POST['prenom']) : $user['prenom'];
    $user['email'] = isset($_POST['email']) ? trim($_POST['email']) : $user['email'];
    $user['adresse'] = isset($_POST['adresse']) ? trim($_POST['adresse']) : $user['adresse'];

    // Mettre à jour l'utilisateur dans le tableau
    foreach ($users as $key => $u) {
        if ($u['id'] === $userId) {
            $users[$key] = $user;
            break;
        }
    }

    // Enregistrer les modifications
    $usersData['users'] = $users;
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($usersData, JSON_PRETTY_PRINT));

    // Mettre à jour la session si c'est l'utilisateur courant
    if ($isOwner) {
        $_SESSION['user'] = $user;
    }

    // Ajouter un message flash
    $_SESSION['flash_message'] = "Profil mis à jour avec succès.";
    $_SESSION['flash_type'] = 'success';

    // Rediriger pour éviter la double soumission
    header('Location: profil.php' . ($isOwner ? '' : '?id=' . $userId));
    exit;
}

// Traitement du formulaire de modification de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Vérifier que le nouveau mot de passe correspond à la confirmation
    if ($newPassword !== $confirmPassword) {
        $_SESSION['flash_message'] = "Les mots de passe ne correspondent pas.";
        $_SESSION['flash_type'] = 'error';
    }
    // Vérifier que le nouveau mot de passe est assez fort
    elseif (strlen($newPassword) < 8) {
        $_SESSION['flash_message'] = "Le mot de passe doit contenir au moins 8 caractères.";
        $_SESSION['flash_type'] = 'error';
    } else {
        // Vérifier l'ancien mot de passe
        if ($oldPassword === $user['password']) {
            // Mettre à jour le mot de passe
            $user['password'] = $newPassword;

            // Mettre à jour l'utilisateur dans le tableau
            foreach ($users as $key => $u) {
                if ($u['id'] === $userId) {
                    $users[$key] = $user;
                    break;
                }
            }

            // Enregistrer les modifications
            $usersData['users'] = $users;
            file_put_contents(__DIR__ . '/../data/users.json', json_encode($usersData, JSON_PRETTY_PRINT));

            // Mettre à jour la session si c'est l'utilisateur courant
            if ($isOwner) {
                $_SESSION['user'] = $user;
            }

            // Ajouter un message flash
            $_SESSION['flash_message'] = "Mot de passe mis à jour avec succès.";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Ancien mot de passe incorrect.";
            $_SESSION['flash_type'] = 'error';
        }
    }

    // Rediriger pour éviter la double soumission
    header('Location: profil.php' . ($isOwner ? '' : '?id=' . $userId));
    exit;
}

// Charger les voyages pour les commandes
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];

// Rechercher les voyages par ID
$voyagesById = [];
foreach ($voyages as $voyage) {
    $voyagesById[$voyage['id']] = $voyage;
}
// Charger les avis existants
$reviewsJson = file_get_contents(__DIR__ . '/../data/avis.json');
$reviewsData = json_decode($reviewsJson, true);
$reviewsList = $reviewsData['avis'] ?? [];
?>
<link rel="stylesheet" href="src/css/profile.css">
<div class="page-container">
    <h1 class="page-title">
        <?php echo $isOwner ? 'Mon profil' : 'Profil de ' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
        <?php if ($user['role'] === 'vip'): ?>
            <span class="vip-badge" title="Utilisateur VIP">⭐</span>
        <?php endif; ?>
    </h1>

    <div class="profile-container">
        <div class="profile-section user-info">
            <h2>Informations personnelles</h2>

            <form id="profil-form" method="post" action="profil.php<?php echo $isOwner ? '' : '?id=' . $userId; ?>">
                <div class="editable-field">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" disabled>
                    <div class="field-buttons">
                        <button type="button" class="edit-btn" data-tooltip="Modifier">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="save-btn" data-tooltip="Enregistrer">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="cancel-btn" data-tooltip="Annuler">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="editable-field">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" disabled>
                    <div class="field-buttons">
                        <button type="button" class="edit-btn" data-tooltip="Modifier">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="save-btn" data-tooltip="Enregistrer">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="cancel-btn" data-tooltip="Annuler">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="editable-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" pattern="[^\s@]+@[^\s@]+\.[^\s@]+" title="Veuillez entrer une adresse email valide" disabled>
                    <div class="field-buttons">
                        <button type="button" class="edit-btn" data-tooltip="Modifier">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="save-btn" data-tooltip="Enregistrer">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="cancel-btn" data-tooltip="Annuler">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="editable-field">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>" disabled>
                    <div class="field-buttons">
                        <button type="button" class="edit-btn" data-tooltip="Modifier">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" class="save-btn" data-tooltip="Enregistrer">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="cancel-btn" data-tooltip="Annuler">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="update_profile" value="1">
                <button type="submit" class="btn btn-primary submit-btn">Enregistrer les modifications</button>
            </form>
        </div>

        <div class="profile-section password-section">
            <h2>Changer de mot de passe</h2>

            <form method="post" action="profil.php<?php echo $isOwner ? '' : '?id=' . $userId; ?>" class="password-form">
                <div class="form-group editable-field">
                    <label for="old_password">Ancien mot de passe</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>

                <div class="form-group editable-field">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required data-max-length="20">
                </div>

                <div class="form-group editable-field">
                    <label for="confirm_password">Confirmez le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_password" value="1" class="btn btn-primary">Changer le mot de passe</button>
                </div>
            </form>
        </div>

        <div class="profile-section orders-section">
            <h2>Mes voyages</h2>

            <?php if (empty($userCommandes)): ?>
                <p class="no-orders">Aucun voyage n'a encore été réservé.</p>
                <div class="centered-button">
                    <a href="voyages.php" class="btn btn-primary">Découvrir nos voyages</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($userCommandes as $commande): ?>
                        <?php
                        $voyage = $voyagesById[$commande['voyage_id']] ?? null;
                        if (!$voyage) continue;
                        // Vérifier si l'utilisateur peut ajouter un avis
                        $hasReviewed = false;
                        if ($isOwner) {
                            foreach ($reviewsList as $r) {
                                if (isset($r['order_id']) && $r['order_id'] == $commande['id'] && $r['user_id'] == $userId) {
                                    $hasReviewed = true;
                                    break;
                                }
                            }
                        }
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h4><?php echo htmlspecialchars($voyage['nom']); ?></h4>
                                <span class="order-status <?php echo strtolower($commande['statut']); ?>">
                                    <?php echo $commande['statut']; ?>
                                </span>
                            </div>

                            <div class="order-details">
                                <p><strong>Date de commande:</strong> <?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?></p>
                                <p><strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($commande['date_depart'])); ?></p>
                                <p><strong>Nombre de participants:</strong> <?php echo $commande['nb_participants']; ?></p>
                                <p><strong>Prix total:</strong> <?php echo number_format($commande['prix_total'], 0, ',', ' '); ?> €</p>

                                <?php if (!empty($commande['options_choisies'])): ?>
                                    <div class="order-options">
                                        <button type="button" class="toggle-options" onclick="toggleOptions(<?= $commande['id']; ?>)">Voir les options</button>
                                        <div class="options-content collapsed" id="options-<?= $commande['id']; ?>">
                                            <?php
                                            // Réorganiser les activités par jour
                                            $activitesParJour = [];
                                            $hebergementParJour = [];
                                            $restaurationParJour = [];

                                            // Parcourir toutes les options et les regrouper par jour
                                            foreach ($commande['options_choisies'] as $etapeId => $options) {
                                                $etapeDay = (int)str_replace('etape_', '', $etapeId) + 1;

                                                // Stocke l'hébergement et la restauration dans le jour de l'étape
                                                if (isset($options['hebergement'])) {
                                                    $hebergementParJour[$etapeDay] = $options['hebergement'];
                                                }

                                                if (isset($options['restauration'])) {
                                                    $restaurationParJour[$etapeDay] = $options['restauration'];
                                                }

                                                // Pour les activités, on utilise le jour spécifié ou le jour de l'étape
                                                if (!empty($options['activites']) && is_array($options['activites'])) {
                                                    foreach ($options['activites'] as $act) {
                                                        $actDay = isset($act['day']) ? $act['day'] : $etapeDay;
                                                        if (!isset($activitesParJour[$actDay])) {
                                                            $activitesParJour[$actDay] = [];
                                                        }
                                                        $activitesParJour[$actDay][] = $act;
                                                    }
                                                }
                                            }

                                            // Fusion des jours de toutes les options pour avoir une liste complète
                                            $tousLesJours = array_unique(array_merge(
                                                array_keys($activitesParJour),
                                                array_keys($hebergementParJour),
                                                array_keys($restaurationParJour)
                                            ));
                                            sort($tousLesJours);

                                            // Afficher les options par jour
                                            foreach ($tousLesJours as $jour):
                                            ?>
                                                <div class="order-day-section">
                                                    <h4>Jour <?= $jour; ?></h4>
                                                    <ul>
                                                        <?php if (isset($hebergementParJour[$jour])): ?>
                                                            <li><strong>Hébergement:</strong> <?= htmlspecialchars($hebergementParJour[$jour]); ?></li>
                                                        <?php endif; ?>

                                                        <?php if (isset($restaurationParJour[$jour])): ?>
                                                            <li><strong>Restauration:</strong> <?= htmlspecialchars($restaurationParJour[$jour]); ?></li>
                                                        <?php endif; ?>

                                                        <?php if (isset($activitesParJour[$jour])): ?>
                                                            <?php foreach ($activitesParJour[$jour] as $act): ?>
                                                                <li><?= htmlspecialchars($act['nom']); ?> (<?= $act['count']; ?>) - <?= number_format($act['prix'] * $act['count'], 0, ',', ' '); ?> €</li>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="order-actions">
                                    <a href="voyage-details.php?id=<?php echo $commande['voyage_id']; ?>" class="btn btn-sm btn-primary">Voir le voyage</a>
                                    <?php if ($isOwner && !$hasReviewed): ?>
                                        <a href="add_review.php?order_id=<?php echo $commande['id']; ?>&voyage_id=<?php echo $commande['voyage_id']; ?>" class="btn btn-sm btn-primary" style="background-color: var(--lapis-lazuli); border: none; color: white;">Ajouter un avis</a>
                                    <?php elseif ($hasReviewed): ?>
                                        <span class="text-success">Avis déjà envoyé</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>