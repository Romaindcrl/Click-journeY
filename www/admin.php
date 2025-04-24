<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/check_admin.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
checkAuth();
checkAdmin();

// Fonction pour formater les noms de permissions
function formatPermissionName($permission) {
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
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
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
                            <td class="action-buttons">
                                <a href="profil.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Voir</a>
                                <button class="btn btn-sm btn-danger delete-user" data-user-id="<?php echo $user['id']; ?>">Supprimer</button>
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
});
</script>

<style>
.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

/* Styles pour les onglets */
.admin-tabs {
    display: flex;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--border-color);
}

.tab-link {
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: var(--text-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}

.tab-link i {
    font-size: 1.2rem;
}

.tab-link:hover {
    color: var(--primary-color);
    background-color: rgba(0, 0, 0, 0.03);
}

.tab-link.active {
    color: var(--primary-color);
    border-bottom: 3px solid var(--primary-color);
}

/* Styles pour le tableau d'utilisateurs */
.users-table-container {
    overflow-x: auto;
    margin: 2rem 0;
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: var(--shadow-md);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th,
.users-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.users-table th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
}

.users-table tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Status badge */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    text-transform: capitalize;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge.actif {
    background-color: #e1f5e9;
    color: #0d6832;
}

.status-badge.inactif {
    background-color: #f0f0f0;
    color: #666;
}

.status-badge.banni {
    background-color: #fee2e2;
    color: #b91c1c;
}

/* Styles pour le sélecteur de rôle */
.role-select {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background-color: var(--card-bg);
    color: var(--text-color);
}

.role-select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(59, 91, 219, 0.25);
}

.role-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Styles pour les boutons d'action */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Styles pour la pagination */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    gap: 0.5rem;
}

.page-link {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    text-decoration: none;
    color: var(--primary-color);
    background-color: var(--card-bg);
    transition: all 0.2s ease;
}

.page-link:hover {
    background-color: var(--primary-color);
    color: white;
}

.page-link.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Styles pour l'onglet Rôles et Permissions */
.roles-container {
    margin: 2rem 0;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.role-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.role-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.role-header {
    background-color: var(--primary-color);
    color: white;
    padding: 1rem;
    text-align: center;
}

.role-name {
    margin: 0;
    font-size: 1.5rem;
}

.role-body {
    padding: 1.5rem;
}

.role-description {
    color: var(--text-color);
    margin-bottom: 1.5rem;
}

.permissions-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.permissions-list li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    color: var(--text-color);
}

.permissions-list i {
    color: #10b981;
}

.role-footer {
    padding: 1rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: center;
}

/* Styles pour l'onglet Statistiques */
.stats-container {
    margin: 2rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.stat-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-md);
}

.stat-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(59, 91, 219, 0.1);
    border-radius: 50%;
}

.stat-info {
    flex: 1;
}

.stat-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: var(--text-light);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Dark mode */
[data-theme="dark"] .users-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .permissions-list i {
    color: #34d399;
}

@media (max-width: 768px) {
    .admin-tabs {
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .roles-grid, .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 