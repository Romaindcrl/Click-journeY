<?php
require_once __DIR__ . '/includes/header.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($login) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        // Vérification que le fichier existe
        $usersFile = __DIR__ . '/../data/users.json';
        if (!file_exists($usersFile)) {
            $error = 'Erreur système. Veuillez contacter l\'administrateur.';
        } else {
            // Lecture du fichier users.json
            $usersJson = file_get_contents($usersFile);
            $users = json_decode($usersJson, true);
            
            // Vérification que le décodage JSON a réussi
            if ($users === null) {
                $error = 'Erreur système. Veuillez contacter l\'administrateur.';
            } else {
                $user = null;
                foreach ($users as $u) {
                    if ($u['login'] === $login) {
                        $user = $u;
                        break;
                    }
                }
                
                if ($user && password_verify($password, $user['password'])) {
                    // Stockage de l'utilisateur dans la session
                    $_SESSION['user'] = $user;
                    
                    // Message de succès
                    $_SESSION['flash'] = [
                        'type' => 'success',
                        'message' => 'Connexion réussie !'
                    ];
                    
                    // Si connexion réussie et qu'il y a une redirection en attente
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $redirect");
                        exit();
                    } else {
                        header("Location: /Click-journeY/www/index.php");
                        exit();
                    }
                } else {
                    $error = 'Login ou mot de passe incorrect';
                }
            }
        }
    }
}
?>

<div class="page-container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Connexion</h2>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="social-login">
                <div class="social-login-text">Connectez-vous avec</div>
                <div class="social-buttons">
                    <button class="social-btn facebook"><i class="fab fa-facebook-f"></i></button>
                    <button class="social-btn google"><i class="fab fa-google"></i></button>
                    <button class="social-btn twitter"><i class="fab fa-twitter"></i></button>
                </div>
                <div class="divider">
                    <span>Ou connectez vous avec votre compte</span>
                </div>
            </div>
            
            <form method="POST" novalidate>
                <div class="auth-form-group">
                    <input type="text" 
                           id="login" 
                           name="login" 
                           value="<?php echo htmlspecialchars($login); ?>"
                           placeholder="Login" 
                           autocomplete="username"
                           required>
                </div>
                
                <div class="auth-form-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Mot de passe" 
                           autocomplete="current-password"
                           required>
                    <div class="password-options">
                        <a href="#" class="forgot-password">Mot de passe oublié</a>
                    </div>
                </div>
                
                <div class="auth-form-actions">
                    <button type="submit" name="connexion" class="btn-auth btn-login">Se connecter</button>
                </div>
            </form>

            <div class="auth-footer">
                <p>Pas encore de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styles pour le nouveau formulaire d'authentification */
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 200px);
        padding: 2rem 0;
    }
    
    .auth-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 480px;
        padding: 2rem;
        transition: transform 0.3s ease;
        margin: 0 auto;
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    
    .auth-header h2 {
        font-size: 2rem;
        color: #1e2a3b;
        margin-bottom: 0.5rem;
    }
    
    .social-login {
        margin-bottom: 1.5rem;
    }
    
    .social-login-text {
        text-align: center;
        margin-bottom: 0.75rem;
        color: #687282;
        font-size: 0.9rem;
    }
    
    .social-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .social-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid #e1e4e8;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .social-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .facebook { color: #4267B2; }
    .google { color: #DB4437; }
    .twitter { color: #1DA1F2; }
    
    .divider {
        position: relative;
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e1e4e8;
        z-index: 0;
    }
    
    .divider span {
        position: relative;
        background: white;
        padding: 0 1rem;
        font-size: 0.85rem;
        color: #687282;
        z-index: 1;
    }
    
    .auth-form-group {
        margin-bottom: 1rem;
        width: 100%;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .auth-form-group input {
        width: 100%;
        padding: 0.9rem 1rem;
        border: 1px solid #dde2e8;
        border-radius: 8px;
        background-color: #f8f9fa;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-align: center;
        box-sizing: border-box;
    }
    
    .auth-form-group input:focus {
        outline: none;
        border-color: #4169E1;
        box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.1);
    }
    
    .auth-form-group input::placeholder {
        color: #a0a8b4;
    }
    
    .password-options {
        display: flex;
        justify-content: center;
        margin-top: 0.5rem;
        text-align: center;
    }
    
    .forgot-password {
        font-size: 0.85rem;
        color: #4169E1;
        text-decoration: none;
    }
    
    .forgot-password:hover {
        text-decoration: underline;
    }
    
    .auth-form-actions {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .btn-auth {
        width: 100%;
        max-width: 400px;
        padding: 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        text-transform: uppercase;
        margin: 0 auto;
        display: block;
    }
    
    .btn-login {
        background-color: #4169E1;
        color: white;
    }
    
    .btn-login:hover {
        background-color: #3251AC;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(65, 105, 225, 0.3);
    }
    
    .auth-footer {
        margin-top: 2rem;
        text-align: center;
        color: #687282;
        font-size: 0.9rem;
    }
    
    .auth-footer a {
        color: #4169E1;
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
    
    /* Dark mode */
    [data-theme="dark"] .auth-card {
        background-color: #2d2d2d;
    }
    
    [data-theme="dark"] .auth-header h2 {
        color: #e1e1e1;
    }
    
    [data-theme="dark"] .divider::before {
        background: #404040;
    }
    
    [data-theme="dark"] .divider span {
        background: #2d2d2d;
        color: #a0a0a0;
    }
    
    [data-theme="dark"] .auth-form-group input {
        background-color: #1a1a1a;
        border-color: #404040;
        color: #e1e1e1;
    }
    
    [data-theme="dark"] .auth-form-group input::placeholder {
        color: #a0a0a0;
    }
    
    [data-theme="dark"] .social-btn {
        background: #1a1a1a;
        border-color: #404040;
    }
    
    [data-theme="dark"] .auth-footer {
        color: #a0a0a0;
    }
    
    @media (max-width: 576px) {
        .auth-card {
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 