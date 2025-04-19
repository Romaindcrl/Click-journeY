<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/check_admin.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
checkAuth();
checkAdmin();

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
    
    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Dernière connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($displayUsers)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Aucun utilisateur trouvé</td>
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
                                        <option value="vip" <?php echo $user['role'] === 'vip' ? 'selected' : ''; ?>>VIP</option>
                                        <option value="banni" <?php echo $user['role'] === 'banni' ? 'selected' : ''; ?>>Banni</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
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
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>" class="page-link">&laquo; Précédent</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>" class="page-link">Suivant &raquo;</a>
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

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

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

/* Dark mode */
[data-theme="dark"] .users-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 