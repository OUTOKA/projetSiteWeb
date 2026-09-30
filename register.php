<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if (est_connecte()) {
    header('Location: dashboard.php');
    exit;
}

$erreurs = array();
$email = $nom = $prenom = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $nom      = trim(isset($_POST['nom']) ? $_POST['nom'] : '');
    $prenom   = trim(isset($_POST['prenom']) ? $_POST['prenom'] : '');
    $mdp      = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
    $mdp2     = isset($_POST['mot_de_passe2']) ? $_POST['mot_de_passe2'] : '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse e-mail invalide.';
    }
    if ($nom === '' || $prenom === '') {
        $erreurs[] = 'Le nom et le prénom sont obligatoires.';
    }
    if (strlen($mdp) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($mdp !== $mdp2) {
        $erreurs[] = 'Les deux mots de passe ne correspondent pas.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email');
        $stmt->execute(array('email' => $email));
        if ($stmt->fetch()) {
            $erreurs[] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
    }

    if (!$erreurs) {
        // Le rôle n'est jamais choisi par l'utilisateur : attribué ensuite par l'admin
        $stmt = $pdo->prepare(
            'INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role)
             VALUES (:email, :mdp, :nom, :prenom, "non_defini")'
        );
        $stmt->execute(array(
            'email'  => $email,
            'mdp'    => password_hash($mdp, PASSWORD_BCRYPT),
            'nom'    => $nom,
            'prenom' => $prenom,
        ));

        header('Location: login.php?inscription=ok');
        exit;
    }
}

$chemin_racine = '';
$titre_page = 'Créer un compte';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <h1 class="h3 mb-3">Créer un compte</h1>

        <?php foreach ($erreurs as $e): ?>
            <div class="alert alert-danger"><?php echo h($e); ?></div>
        <?php endforeach; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo h($prenom); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo h($nom); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse e-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo h($email); ?>" required>
            </div>
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe (8 caractères min.)</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" minlength="8" required>
            </div>
            <div class="mb-3">
                <label for="mot_de_passe2" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe2" name="mot_de_passe2" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Créer mon compte</button>
        </form>
        <p class="mt-3 text-center">
            <small>Votre rôle (apprenti / tuteur) sera attribué par un administrateur après inscription.</small>
        </p>
        <p class="text-center"><a href="login.php">Déjà un compte ? Se connecter</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
