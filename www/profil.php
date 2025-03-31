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
    foreach ($users as &$user_data) {
        if ($user_data['id'] === $_SESSION['user']['id']) {
            // Vérification de l'ancien mot de passe
            if (password_verify($old_password, $user_data['password'])) {
                // Mise à jour du mot de passe
                $user_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
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
$commandesJson = file_exists(__DIR__ . '/../data/commandes.json') ? file_get_contents(__DIR__ . '/../data/commandes.json') : '[]';
$commandes = json_decode($commandesJson, true) ?: [];
$commandesUtilisateur = [];

if (is_array($commandes)) {
    $commandesUtilisateur = array_filter($commandes, function($commande) use ($user) {
        return isset($commande['user_id']) && $commande['user_id'] === $user['id'];
    });
}

// Lecture des voyages
$voyagesJson = file_exists(__DIR__ . '/../data/voyages.json') ? file_get_contents(__DIR__ . '/../data/voyages.json') : '{"voyages":[]}';
$voyagesData = json_decode($voyagesJson, true);
$voyages = $voyagesData['voyages'] ?? [];
?>

<div class="page-container">
    <h1 class="page-title">Mon profil</h1>
    
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
            <div class="info-group">
                <label>Rôle</label>
                <p>
                    <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                    <?php if ($user['role'] === 'vip'): ?>
                        <span class="vip-badge">⭐</span>
                    <?php endif; ?>
                </p>
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
                <div class="form-actions">
                    <a href="voyages.php" class="btn btn-primary">Découvrir nos voyages</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($commandesUtilisateur as $commande): 
                        $voyage = null;
                        foreach ($voyages as $v) {
                            if (isset($v['id']) && isset($commande['voyage_id']) && $v['id'] == $commande['voyage_id']) {
                                $voyage = $v;
                                break;
                            }
                        }
                        if (!$voyage) continue;
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h4><?php echo htmlspecialchars($voyage['nom']); ?></h4>
                                <span class="order-status <?php echo isset($commande['status']) ? $commande['status'] : 'confirmé'; ?>">
                                    <?php echo ucfirst(isset($commande['status']) ? $commande['status'] : 'confirmé'); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <p><strong>Date de départ :</strong> <?php echo isset($commande['date_depart']) ? date('d/m/Y', strtotime($commande['date_depart'])) : 'Non spécifiée'; ?></p>
                                <p><strong>Date de réservation :</strong> <?php echo isset($commande['date_reservation']) ? date('d/m/Y H:i', strtotime($commande['date_reservation'])) : 'Non spécifiée'; ?></p>
                                <p><strong>Montant :</strong> <?php echo isset($commande['prix_total']) ? number_format($commande['prix_total'], 0, ',', ' ') : '0'; ?> €</p>
                                
                                <?php if (isset($commande['activites']) && !empty($commande['activites'])): ?>
                                <div class="order-activities">
                                    <strong>Activités choisies :</strong>
                                    <ul>
                                        <?php 
                                        foreach ($commande['activites'] as $activiteId): 
                                            $activiteNom = 'Activité';
                                            // Rechercher le nom de l'activité dans les données du voyage
                                            if (isset($voyage['activites'])) {
                                                foreach ($voyage['activites'] as $act) {
                                                    if (isset($act['id']) && $act['id'] == $activiteId && isset($act['nom'])) {
                                                        $activiteNom = $act['nom'];
                                                        break;
                                                    }
                                                }
                                            }
                                        ?>
                                            <li><?php echo htmlspecialchars($activiteNom); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-section">
            <h3>Actions</h3>
            <div class="profile-actions">
                <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-info {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.profile-section {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.profile-section h3 {
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0.5rem;
}

.info-group {
    margin-bottom: 1.5rem;
}

.info-group label {
    display: block;
    color: var(--text-light);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.info-group p {
    color: var(--text-color);
    font-size: 1.1rem;
    margin: 0;
}

.profile-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1rem;
}

.no-orders {
    text-align: center;
    margin-bottom: 1.5rem;
    color: var(--text-light);
}

@media (min-width: 768px) {
    .profile-info {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .profile-section:nth-child(3),
    .profile-section:nth-child(4) {
        grid-column: span 2;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 