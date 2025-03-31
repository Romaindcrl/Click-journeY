<?php
require_once __DIR__ . '/includes/header.php';

// Définir le chemin du dossier data et du fichier users.json
$dataDir = __DIR__ . '/../data';
$usersFile = $dataDir . '/users.json';

// Créer le dossier data s'il n'existe pas
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
    chmod($dataDir, 0777);
}

// Créer le fichier users.json s'il n'existe pas
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
    chmod($usersFile, 0777);
}

$error = '';
$success = '';
$formData = [
    'login' => '',
    'email' => '',
    'nom' => '',
    'prenom' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'login' => trim($_POST['login'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? '')
    ];
    
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des champs
    if (empty($formData['login']) || empty($password) || empty($formData['email']) || empty($formData['nom']) || empty($formData['prenom'])) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        // Lire le contenu du fichier users.json
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
        
        // Vérifier si le login existe déjà
        $loginExists = false;
        foreach ($users as $user) {
            if ($user['login'] === $formData['login']) {
                $error = 'Ce login est déjà utilisé';
                $loginExists = true;
                break;
            }
        }
        
        if (!$loginExists) {
            // Générer un nouvel ID unique
            $maxId = 0;
            foreach ($users as $user) {
                $maxId = max($maxId, $user['id']);
            }
            
            $newUser = [
                'id' => $maxId + 1,
                'login' => $formData['login'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $formData['email'],
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'role' => 'normal'
            ];
            
            $users[] = $newUser;
            
            if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) !== false) {
                header('Location: connexion.php?success=1');
                exit();
            } else {
                $error = 'Erreur lors de l\'enregistrement de l\'utilisateur';
            }
        }
    }
}
?>

<div class="page-container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Créer votre compte</h2>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="social-login">
                <div class="social-login-text">Inscrivez-vous avec</div>
                <div class="social-buttons">
                    <button class="social-btn facebook"><i class="fab fa-facebook-f"></i></button>
                    <button class="social-btn google"><i class="fab fa-google"></i></button>
                    <button class="social-btn twitter"><i class="fab fa-twitter"></i></button>
                </div>
                <div class="divider">
                    <span>Ou créez un compte avec votre email</span>
                </div>
            </div>
            
            <form method="POST" action="inscription.php" novalidate>
                <div class="auth-form-row">
                    <div class="auth-form-group">
                        <input type="text" 
                               id="nom" 
                               name="nom" 
                               placeholder="Nom" 
                               value="<?php echo htmlspecialchars($formData['nom']); ?>"
                               autocomplete="family-name"
                               required>
                    </div>
                    
                    <div class="auth-form-group">
                        <input type="text" 
                               id="prenom" 
                               name="prenom" 
                               placeholder="Prénom" 
                               value="<?php echo htmlspecialchars($formData['prenom']); ?>"
                               autocomplete="given-name"
                               required>
                    </div>
                </div>
                
                <div class="auth-form-group">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="Email" 
                           value="<?php echo htmlspecialchars($formData['email']); ?>"
                           autocomplete="email"
                           required>
                </div>
                
                <div class="auth-form-group">
                    <input type="text" 
                           id="login" 
                           name="login" 
                           placeholder="Login" 
                           value="<?php echo htmlspecialchars($formData['login']); ?>"
                           autocomplete="username"
                           required>
                </div>
                
                <div class="auth-form-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Mot de passe (minimum 6 caractères)" 
                           autocomplete="new-password"
                           required>
                </div>
                
                <div class="auth-form-group">
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirmez votre mot de passe" 
                           autocomplete="new-password"
                           required>
                </div>
                
                <div class="auth-form-actions">
                    <button type="submit" class="btn-auth btn-signup">Créer un compte</button>
                </div>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
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
        padding: 2rem 1rem;
    }
    
    .auth-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 480px;
        padding: 2rem;
        transition: transform 0.3s ease;
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
    
    .auth-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .auth-form-group {
        margin-bottom: 1.25rem;
    }
    
    .auth-form-group input {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 1px solid #e1e4e8;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .auth-form-group input:focus {
        outline: none;
        border-color: #4169E1;
        box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.1);
    }
    
    .auth-form-group input::placeholder {
        color: #a0a8b4;
    }
    
    .auth-form-actions {
        margin-top: 1.5rem;
    }
    
    .btn-auth {
        width: 100%;
        padding: 0.9rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-signup {
        background-color: #4169E1;
        color: white;
    }
    
    .btn-signup:hover {
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
    
    /* Responsive */
    @media (max-width: 576px) {
        .auth-form-row {
            grid-template-columns: 1fr;
        }
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
</style>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 