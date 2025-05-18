<?php
// Bufferiser la sortie pour éviter l'erreur de headers déjà envoyés
ob_start();
// Vérifier si l'utilisateur est connecté et est un administrateur avant tout envoi de HTML
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/check_admin.php';
checkAuth();
checkAdmin();
// Inclure le header après la vérification d'authentification
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="src/css/admin.css">

<?php
// Fonction pour formater les noms de permissions
function formatPermissionName($permission)
{
    $formattedNames = [
        'voir_profil' => 'Voir profil',
        'modifier_profil' => 'Modifier profil',
        'reserver_voyage' => 'Réserver des voyages',
        'gerer_utilisateurs' => 'Gérer les utilisateurs',
        'gerer_voyages' => 'Gérer les voyages',
        'gerer_avis' => 'Gérer les avis',
        'gerer_commandes' => 'Gérer les commandes',
        'acces_statistiques' => 'Accéder aux statistiques',
        'acces_configuration' => 'Modifier la configuration',
        'voir_statistiques' => 'Voir les statistiques',
        'voir_utilisateurs' => 'Voir les utilisateurs',
        'acces_offres_speciales' => 'Accéder aux offres spéciales'
    ];

    return $formattedNames[$permission] ?? ucfirst(str_replace('_', ' ', $permission));
}

// Lire la liste des utilisateurs
$usersJson = file_get_contents(__DIR__ . '/../data/users.json');
$usersData = json_decode($usersJson, true);
$users = $usersData['users'] ?? [];

// Pagination
$usersPerPage = 5;
$totalUsers = count($users);
$totalPages = ceil($totalUsers / $usersPerPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $usersPerPage;
$displayUsers = array_slice($users, $offset, $usersPerPage);

// Traitement du changement de rôle (simulé pour le moment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = $_POST['user_id'] ?? 0;
    $newRole = $_POST['new_role'] ?? '';
    // Mettre à jour le rôle de l'utilisateur dans le tableau et enregistrer dans le fichier JSON
    foreach ($usersData['users'] as &$user) {
        if ($user['id'] == $userId) {
            $user['role'] = $newRole;
            break;
        }
    }
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    // Ajouter un message flash
    $_SESSION['flash_message'] = "Le rôle de l'utilisateur #$userId a été mis à jour vers $newRole.";
    $_SESSION['flash_type'] = 'success';

    // Rediriger pour éviter les soumissions multiples
    header('Location: admin.php');
    exit;
}
?>

<div class="admin-container">
    <h1 class="page-title">Administration des utilisateurs</h1>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
            <?php echo $_SESSION['flash_message']; ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Onglets d'administration -->
    <div class="admin-tabs">
        <a href="?tab=users" class="tab-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'users') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Utilisateurs
        </a>
        <a href="?tab=roles" class="tab-link <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'roles') ? 'active' : ''; ?>">
            <i class="fas fa-user-tag"></i> Rôles & Permissions
        </a>
        <a href="?tab=stats" class="tab-link <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'stats') ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Statistiques
        </a>
    </div>

    <?php
    // Déterminer quel onglet afficher
    $currentTab = $_GET['tab'] ?? 'users';

    // Afficher l'onglet utilisateurs
    if ($currentTab === 'users'):
    ?>
        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($displayUsers)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun utilisateur trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayUsers as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                                <td title="<?php echo htmlspecialchars($user['email']); ?>"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form class="role-form" method="post" action="admin.php">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="new_role" class="role-select" data-user-id="<?php echo $user['id']; ?>">
                                            <option value="normal" <?php echo $user['role'] === 'normal' ? 'selected' : ''; ?>>Normal</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="moderateur" <?php echo $user['role'] === 'moderateur' ? 'selected' : ''; ?>>Modérateur</option>
                                            <option value="support" <?php echo $user['role'] === 'support' ? 'selected' : ''; ?>>Support</option>
                                            <option value="vip" <?php echo $user['role'] === 'vip' ? 'selected' : ''; ?>>VIP</option>
                                            <option value="banni" <?php echo $user['role'] === 'banni' ? 'selected' : ''; ?>>Banni</option>
                                        </select>
                                        <input type="hidden" name="update_role" value="1">
                                    </form>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['statut'] ?? 'actif'; ?>">
                                        <?php echo $user['statut'] ?? 'actif'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['derniere_connexion'] ?? 'Jamais'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="profil.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                                        <button class="btn btn-sm btn-danger delete-user" data-user-id="<?php echo $user['id']; ?>">Supprimer</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($currentTab === 'roles'):
        // Chargez les informations des rôles
        $rolesInfo = $usersData['roles'] ?? [];
    ?>

        <div class="roles-container">
            <h2>Rôles et Permissions</h2>
            <p>Gérez les différents rôles et leurs permissions associées.</p>

            <div class="roles-grid">
                <?php foreach ($rolesInfo as $roleName => $roleInfo): ?>
                    <div class="role-card">
                        <div class="role-header">
                            <h3 class="role-name"><?php echo ucfirst(htmlspecialchars($roleName)); ?></h3>
                        </div>
                        <div class="role-body">
                            <p class="role-description"><?php echo htmlspecialchars($roleInfo['description']); ?></p>
                            <h4>Permissions</h4>
                            <ul class="permissions-list">
                                <?php foreach ($roleInfo['permissions'] as $permission): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo formatPermissionName(htmlspecialchars($permission)); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="role-footer">
                            <button class="btn btn-sm btn-primary edit-role" data-role="<?php echo $roleName; ?>">Modifier</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php elseif ($currentTab === 'stats'): ?>

        <div class="stats-container">
            <h2>Statistiques des utilisateurs</h2>
            <p>Vue d'ensemble des utilisateurs par rôle et statut.</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Total Utilisateurs</h3>
                        <div class="stat-value"><?php echo count($users); ?></div>
                    </div>
                </div>

                <?php
                // Compter les utilisateurs par rôle
                $roleCount = [];
                foreach ($users as $user) {
                    $role = $user['role'] ?? 'non défini';
                    if (!isset($roleCount[$role])) {
                        $roleCount[$role] = 0;
                    }
                    $roleCount[$role]++;
                }

                // Afficher le nombre d'utilisateurs par rôle
                foreach ($roleCount as $role => $count):
                ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-tag"></i></div>
                        <div class="stat-info">
                            <h3>Rôle: <?php echo ucfirst(htmlspecialchars($role)); ?></h3>
                            <div class="stat-value"><?php echo $count; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php
                // Compter les utilisateurs par statut
                $statusCount = [];
                foreach ($users as $user) {
                    $status = $user['statut'] ?? 'actif';
                    if (!isset($statusCount[$status])) {
                        $statusCount[$status] = 0;
                    }
                    $statusCount[$status]++;
                }

                // Afficher le nombre d'utilisateurs par statut
                foreach ($statusCount as $status => $count):
                ?>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                        <div class="stat-info">
                            <h3>Statut: <?php echo ucfirst(htmlspecialchars($status)); ?></h3>
                            <div class="stat-value"><?php echo $count; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

    <?php if ($currentTab === 'users' && $totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?tab=users&page=<?php echo $currentPage - 1; ?>" class="page-link">&laquo; Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?tab=users&page=<?php echo $i; ?>" class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?tab=users&page=<?php echo $currentPage + 1; ?>" class="page-link">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Appliquer le thème global depuis le localStorage
        const globalTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', globalTheme);
        const themeToggleGlobal = document.getElementById('theme-toggle');
        if (themeToggleGlobal) {
            themeToggleGlobal.checked = globalTheme === 'dark';
        }

        // Gestion du changement de rôle
        const roleSelects = document.querySelectorAll('.role-select');
        roleSelects.forEach(select => {
            select.addEventListener('change', function() {
                const userId = this.dataset.userId;
                const newRole = this.value;
                const form = this.closest('form');

                // Désactiver le select pendant le "chargement"
                this.disabled = true;

                // Simuler un chargement
                setTimeout(() => {
                    // Réactiver le select
                    this.disabled = false;

                    // Soumettre le formulaire
                    form.submit();
                }, 2000);
            });
        });

        // Gestion de la suppression d'utilisateur (simulation)
        const deleteButtons = document.querySelectorAll('.delete-user');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.dataset.userId;
                if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur #${userId} ?`)) {
                    // Désactiver le bouton
                    this.disabled = true;
                    this.textContent = 'Suppression...';

                    // Simuler un chargement
                    setTimeout(() => {
                        alert(`L'utilisateur #${userId} a été supprimé.`);
                        location.reload();
                    }, 2000);
                }
            });
        });

        // Gestion des onglets pour préserver le thème
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Éviter la perte du thème lors des changements d'onglets
                e.preventDefault(); // Empêcher le comportement par défaut

                // Stocker le thème actuel avant de naviguer
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                localStorage.setItem('admin_tab_theme', currentTheme);

                // Rediriger avec le thème préservé
                const url = this.getAttribute('href');
                window.location.href = url + '&theme=' + currentTheme;
            });
        });

        // Appliquer le thème stocké lors du chargement de la page
        const storedTheme = localStorage.getItem('admin_tab_theme');
        const urlTheme = new URLSearchParams(window.location.search).get('theme');

        // Priorité au thème dans l'URL, puis au thème stocké
        if (urlTheme) {
            document.documentElement.setAttribute('data-theme', urlTheme);
            localStorage.setItem('admin_tab_theme', urlTheme); // Mettre à jour le stockage
        } else if (storedTheme) {
            document.documentElement.setAttribute('data-theme', storedTheme);
        }

        // Mettre à jour le toggle de thème si présent
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            themeToggle.checked = currentTheme === 'dark';
        }
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>