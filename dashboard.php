<?php
require_once __DIR__ . '/includes/functions.php';
requiert_connexion();

$role = role_utilisateur();

if ($role === 'admin') {
    header('Location: admin/index.php');
    exit;
}
if ($role === 'non_defini') {
    $chemin_racine = '';
    $titre_page = 'Compte en attente';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="alert alert-info">
        Votre compte a bien été créé. Un administrateur doit encore vous attribuer un rôle
        (apprenti, tuteur école ou tuteur entreprise) avant que vous puissiez accéder à la messagerie.
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

header('Location: inbox.php');
exit;
