<?php
require_once __DIR__ . '/includes/functions.php';
$chemin_racine = '';
$titre_page = 'Accueil';
require __DIR__ . '/includes/header.php';
?>

<section class="hero mb-5">
    <div class="row align-items-center">
        <div class="col-12 col-md-8">
            <h1>Bienvenue sur la Messagerie Apprentis / Tuteurs</h1>
            <p class="lead">
                L'ESIGELEC met à disposition ce service pour faciliter les échanges entre
                un apprenti, son tuteur école et son tuteur entreprise : suivi, informations,
                questions... tout en un seul endroit, avec possibilité de joindre des documents PDF.
            </p>
            <?php if (!est_connecte()): ?>
                <a href="register.php" class="btn btn-light btn-lg me-2">Créer un compte</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Se connecter</a>
            <?php else: ?>
                <a href="inbox.php" class="btn btn-light btn-lg">Accéder à ma messagerie</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="row g-4">
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Communiquez simplement</h2>
                <p class="card-text">Envoyez un message à un seul interlocuteur ou aux deux membres
                de votre équipe (apprenti, tuteur école, tuteur entreprise) en quelques clics.</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Joignez vos documents</h2>
                <p class="card-text">Chaque message peut être accompagné d'une pièce jointe au
                format PDF (compte-rendu, rapport, convention...).</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Suivez vos échanges</h2>
                <p class="card-text">Retrouvez tous vos messages reçus et envoyés, avec leur
                statut de lecture, à tout moment.</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
