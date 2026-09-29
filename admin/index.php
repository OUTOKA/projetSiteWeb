<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requiert_role(['admin']);

$nb_utilisateurs = $pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
$nb_non_definis  = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'non_defini'")->fetchColumn();
$nb_equipes      = $pdo->query('SELECT COUNT(*) FROM equipes')->fetchColumn();
$nb_messages     = $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

$chemin_racine = '../';
$titre_page = 'Administration';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-4">Tableau de bord administrateur</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center"><div class="card-body">
            <div class="display-6"><?= (int) $nb_utilisateurs ?></div>
            <div class="text-muted">Utilisateurs</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center"><div class="card-body">
            <div class="display-6"><?= (int) $nb_non_definis ?></div>
            <div class="text-muted">Rôles à attribuer</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center"><div class="card-body">
            <div class="display-6"><?= (int) $nb_equipes ?></div>
            <div class="text-muted">Équipes</div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center"><div class="card-body">
            <div class="display-6"><?= (int) $nb_messages ?></div>
            <div class="text-muted">Messages</div>
        </div></div>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    <a href="users.php" class="btn btn-primary">Gérer les utilisateurs / rôles</a>
    <a href="teams.php" class="btn btn-primary">Gérer les équipes</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
