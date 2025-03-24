<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
checkAuth();

require_once __DIR__ . '/check_banned.php';
checkBanned();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$user = $_SESSION['user'];

// Traitement du formulaire de modification du mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $error = null;
    $success = null;

    // Lecture du fichier users.json
    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);

    // Recherche de l'utilisateur
    foreach ($users as &$user) {
        if ($user['id'] === $_SESSION['user']['id']) {
            // Vérification de l'ancien mot de passe
            if (password_verify($old_password, $user['password'])) {
                // Mise à jour du mot de passe
                $user['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                file_put_contents(__DIR__ . '/../data/users.json', json_encode($users, JSON_PRETTY_PRINT));
                $success = "Votre mot de passe a été modifié avec succès.";
                $_SESSION['flash'] = ['type' => 'success', 'message' => $success];
            } else {
                $error = "L'ancien mot de passe est incorrect.";
                $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            }
            break;
        }
    }
}

// Lecture des commandes de l'utilisateur
$commandesJson = file_get_contents(__DIR__ . '/../data/commandes.json');
$commandes = json_decode($commandesJson, true)['commandes'];
$commandesUtilisateur = array_filter($commandes, function($commande) use ($user) {
    return $commande['user_id'] === $user['id'];
});

// Lecture des voyages
$voyagesJson = file_get_contents(__DIR__ . '/../data/voyages.json');
$voyages = json_decode($voyagesJson, true)['voyages'];
?>

<div class="page-container">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Bienvenue <?php echo htmlspecialchars($user['prenom']); ?> !</h2>
                <p>Gérez vos informations personnelles</p>
            </div>

            <div class="profile-info">
                <div class="profile-section">
                    <h3>Informations personnelles</h3>
                    <div class="info-group">
                        <label>Login</label>
                        <p><?php echo htmlspecialchars($user['login']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Nom</label>
                        <p><?php echo htmlspecialchars($user['nom']); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Prénom</label>
                        <p><?php echo htmlspecialchars($user['prenom']); ?></p>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Modifier mon mot de passe</h3>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST" class="password-form">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label for="old_password">Ancien mot de passe</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" required 
                                   pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                                   title="Le mot de passe doit contenir au moins 8 caractères, dont des lettres et des chiffres">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
                        </div>
                    </form>
                </div>

                <div class="profile-section">
                    <h3>Mes Voyages</h3>
                    <?php if (empty($commandesUtilisateur)): ?>
                        <p class="no-orders">Vous n'avez pas encore de voyages réservés.</p>
                        <a href="voyages.php" class="btn btn-primary">Découvrir nos voyages</a>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($commandesUtilisateur as $commande): 
                                $voyage = null;
                                foreach ($voyages as $v) {
                                    if ($v['id'] === $commande['voyage_id']) {
                                        $voyage = $v;
                                        break;
                                    }
                                }
                                if (!$voyage) continue;
                            ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <h4><?php echo htmlspecialchars($voyage['nom']); ?></h4>
                                        <span class="order-status <?php echo $commande['statut']; ?>">
                                            <?php echo ucfirst($commande['statut']); ?>
                                        </span>
                                    </div>
                                    <div class="order-details">
                                        <p><strong>Date de départ :</strong> <?php echo date('d/m/Y', strtotime($commande['date_depart'])); ?></p>
                                        <p><strong>Date de réservation :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['date_commande'])); ?></p>
                                        <p><strong>Montant :</strong> <?php echo number_format($commande['montant'], 0, ',', ' '); ?> €</p>
                                        <div class="order-activities">
                                            <strong>Activités choisies :</strong>
                                            <ul>
                                                <?php foreach ($commande['activites'] as $activite): ?>
                                                    <li><?php echo htmlspecialchars($activite); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-section">
                    <h3>Actions</h3>
                    <div class="profile-actions">
                        <button class="btn btn-primary">Modifier mes infos</button>
                        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 