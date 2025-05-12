<?php
require_once __DIR__ . '/includes/session.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$error = '';
$login = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    // Vérification explicite avant d'accéder à $_POST
    if (isset($_POST['login'])) {
        $login = trim($_POST['login']);
    } else {
        $login = ''; // Assurer que $login est toujours défini
    }
    
    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
    } else {
        $password = ''; // Assurer que $password est toujours défini
    }
    
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
            $data = json_decode($usersJson, true);
            
            // Vérification que le décodage JSON a réussi et que la clé 'users' existe
            if ($data === null || !isset($data['users']) || !is_array($data['users'])) {
                $error = 'Erreur système. Veuillez contacter l\'administrateur.';
            } else {
                // Récupération du tableau d'utilisateurs
                $users = $data['users'];
                $user = null;
                foreach ($users as $u) {
                    if ($u['login'] === $login) {
                        $user = $u;
                        break;
                    }
                }
                
                if ($user && $password === $user['password']) {
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
                        header("Location: index.php");
                        exit();
                    }
                } else {
                    $error = 'Login ou mot de passe incorrect';
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
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

<?php
require_once __DIR__ . '/includes/footer.php';
?> 