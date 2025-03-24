<?php
require_once __DIR__ . '/includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    
    $user = null;
    foreach ($users['users'] as $u) {
        if ($u['login'] === $login) {
            $user = $u;
            break;
        }
    }
    
    if ($user && password_verify($password, $user['password'])) {
        // Stockage de l'utilisateur dans la session
        $_SESSION['user'] = $user;
        
        // Redirection vers la page des voyages
        header("Location: voyages.php");
        exit();
    } else {
        $error = 'Login ou mot de passe incorrect';
    }
}
?>

<div class="page-container">
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Connexion</h2>
                <p>Connectez-vous pour accéder à votre espace personnel</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" placeholder="Entrez votre login" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>

            <div class="form-footer">
                <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 