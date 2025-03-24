<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/check_auth.php';
checkAuth();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Traitement du changement de rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $user_id = $_POST['user_id'] ?? '';
    $new_role = $_POST['role'] ?? '';
    
    if ($user_id && $new_role) {
        $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
        
        foreach ($users as &$user) {
            if ($user['id'] === $user_id) {
                $user['role'] = $new_role;
                break;
            }
        }
        
        file_put_contents(__DIR__ . '/../data/users.json', json_encode($users, JSON_PRETTY_PRINT));
        
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Le rôle de l\'utilisateur a été modifié avec succès.'
        ];
        
        header('Location: admin.php');
        exit();
    }
}

// Lecture des utilisateurs
$users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
?>

<div class="page-container">
    <div class="admin-container">
        <div class="admin-header">
            <h2>Administration des utilisateurs</h2>
            <p>Gérez les rôles et les accès des utilisateurs</p>
        </div>

        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Login</th>
                        <th>Email</th>
                        <th>Rôle actuel</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role'] ?? 'normal'; ?>">
                                        <?php echo ucfirst($user['role'] ?? 'normal'); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" class="role-select" onchange="this.form.submit()">
                                            <option value="normal" <?php echo ($user['role'] ?? 'normal') === 'normal' ? 'selected' : ''; ?>>Normal</option>
                                            <option value="vip" <?php echo ($user['role'] ?? '') === 'vip' ? 'selected' : ''; ?>>VIP</option>
                                            <option value="banni" <?php echo ($user['role'] ?? '') === 'banni' ? 'selected' : ''; ?>>Banni</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 