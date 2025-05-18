<?php
// Activer la mise en tampon pour éviter les erreurs de header déjà envoyés
ob_start();
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="src/css/auth.css">
<link rel="stylesheet" href="src/css/form-validation.css">
<?php

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
    // Initialiser avec un objet contenant la clé "users"
    file_put_contents($usersFile, json_encode(['users' => []], JSON_PRETTY_PRINT));
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
        // Lire le fichier users.json en conservant la structure éventuelle
        $fileContent = file_get_contents($usersFile);
        $jsonData = json_decode($fileContent, true);
        if (!is_array($jsonData)) {
            $jsonData = [];
        }
        if (isset($jsonData['users']) && is_array($jsonData['users'])) {
            $users = $jsonData['users'];
        } else {
            $users = $jsonData;
        }

        // Vérifier si le login existe déjà
        $loginExists = false;
        foreach ($users as $user) {
            if (isset($user['login']) && $user['login'] === $formData['login']) {
                $error = 'Ce login est déjà utilisé';
                $loginExists = true;
                break;
            }
        }

        if (!$loginExists) {
            // Générer un nouvel ID unique
            $maxId = 0;
            foreach ($users as $user) {
                // S'assurer que l'ID existe et est un entier
                $maxId = max($maxId, isset($user['id']) ? (int)$user['id'] : 0);
            }

            $newUser = [
                'id' => $maxId + 1,
                'login' => $formData['login'],
                'password' => $password,
                'email' => $formData['email'],
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'role' => 'normal'
            ];

            $users[] = $newUser;

            // Enregistrer dans le format d'origine (avec clé "users" si nécessaire)
            // Enregistrer dans le format attendu : objet racine avec clé "users"
            $newJson = json_encode(['users' => $users], JSON_PRETTY_PRINT);
            if (file_put_contents($usersFile, $newJson) !== false) {
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

<?php
require_once __DIR__ . '/includes/footer.php';
?>