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
    $initialData = ['users' => []];
    file_put_contents($usersFile, json_encode($initialData, JSON_PRETTY_PRINT));
    chmod($usersFile, 0777);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    
    // Validation des champs
    if (empty($login) || empty($password) || empty($email) || empty($nom) || empty($prenom)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        // Lire le contenu du fichier users.json
        $users = json_decode(file_get_contents($usersFile), true);
        
        // Vérifier si le login existe déjà
        $loginExists = false;
        foreach ($users['users'] as $user) {
            if ($user['login'] === $login) {
                $error = 'Ce login est déjà utilisé';
                $loginExists = true;
                break;
            }
        }
        
        if (!$loginExists) {
            $newUser = [
                'id' => count($users['users']) + 1,
                'login' => $login,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'nom' => $nom,
                'prenom' => $prenom,
                'role' => 'user'
            ];
            
            $users['users'][] = $newUser;
            
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
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Inscription</h2>
                <p>Créez votre compte pour rejoindre la communauté Click-journeY</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="inscription.php">
                <div class="form-group">
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" placeholder="Choisissez un login" required value="<?php echo htmlspecialchars($login ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required value="<?php echo htmlspecialchars($nom ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required value="<?php echo htmlspecialchars($prenom ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Choisissez un mot de passe (minimum 6 caractères)" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>

            <div class="form-footer">
                <p>Déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 