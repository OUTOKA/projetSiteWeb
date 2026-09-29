<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if (est_connecte()) {
    header('Location: dashboard.php');
    exit;
}

$erreur = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $mdp   = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';

    $stmt = $pdo->prepare('SELECT id, email, mot_de_passe, nom, prenom, role FROM utilisateurs WHERE email = :email');
    $stmt->execute(array('email' => $email));
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {
        session_regenerate_id(true); // anti session fixation
        $_SESSION['user_id'] = $utilisateur['id'];
        $_SESSION['nom']     = $utilisateur['nom'];
        $_SESSION['prenom']  = $utilisateur['prenom'];
        $_SESSION['role']    = $utilisateur['role'];

        header('Location: dashboard.php');
        exit;
    }

    $erreur = 'Adresse e-mail ou mot de passe incorrect.';
}

$chemin_racine = '';
$titre_page = 'Connexion';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <h1 class="h3 mb-3">Connexion</h1>

        <?php if (isset($_GET['inscription'])): ?>
            <div class="alert alert-success">Compte créé avec succès, vous pouvez vous connecter.</div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-danger"><?php echo h($erreur); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo h($email); ?>" required>
            </div>
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <p class="text-center mt-3"><a href="register.php">Pas encore de compte ? S'inscrire</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
