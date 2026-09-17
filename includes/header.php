<?php
/**
 * Gabarit commun : en-tête + barre de navigation.
 * Attend éventuellement $titre_page et $chemin_racine définis avant l'inclusion.
 */
require_once __DIR__ . '/functions.php';

if (!isset($titre_page)) {
    $titre_page = 'Messagerie Apprentis';
}
if (!isset($chemin_racine)) {
    $chemin_racine = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($titre_page); ?> - Messagerie Apprentis ESIGELEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $chemin_racine; ?>css/style.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $chemin_racine; ?>index.php">Messagerie Apprentis</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                <?php if (est_connecte()): ?>
                    <?php if (role_utilisateur() === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>admin/index.php">Administration</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>admin/users.php">Utilisateurs</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>admin/teams.php">Équipes</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>inbox.php">Messagerie</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>sent.php">Envoyés</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>compose.php">Nouveau message</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><span class="nav-link text-white-50">
                        <?php
                        $prenom_aff = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '';
                        $nom_aff    = isset($_SESSION['nom']) ? $_SESSION['nom'] : '';
                        echo h($prenom_aff . ' ' . $nom_aff);
                        ?>
                        (<?php echo h(libelle_role(role_utilisateur())); ?>)
                    </span></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $chemin_racine; ?>register.php">Créer un compte</a></li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container py-4">
