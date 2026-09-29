<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requiert_role(['admin']);

$erreurs = [];
$message_ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_equipe'])) {
    $apprenti_id          = isset($_POST['apprenti_id']) ? (int) $_POST['apprenti_id'] : 0;
    $tuteur_ecole_id      = !empty($_POST['tuteur_ecole_id']) ? (int) $_POST['tuteur_ecole_id'] : null;
    $tuteur_entreprise_id = !empty($_POST['tuteur_entreprise_id']) ? (int) $_POST['tuteur_entreprise_id'] : null;

    if (!$apprenti_id) {
        $erreurs[] = 'Veuillez sélectionner un apprenti.';
    }
    if (!$tuteur_ecole_id && !$tuteur_entreprise_id) {
        $erreurs[] = 'Sélectionnez au moins un tuteur.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare(
            'INSERT INTO equipes (apprenti_id, tuteur_ecole_id, tuteur_entreprise_id)
             VALUES (:app, :te, :ten)
             ON DUPLICATE KEY UPDATE tuteur_ecole_id = :te2, tuteur_entreprise_id = :ten2'
        );
        $stmt->execute([
            'app'  => $apprenti_id,
            'te'   => $tuteur_ecole_id,
            'ten'  => $tuteur_entreprise_id,
            'te2'  => $tuteur_ecole_id,
            'ten2' => $tuteur_entreprise_id,
        ]);
        $message_ok = 'Équipe enregistrée avec succès.';
    }
}

// Suppression d'une équipe (bonus) : supprime aussi l'apprenti et ses messages (ON DELETE CASCADE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_equipe_id'])) {
    $equipe_id = (int) $_POST['supprimer_equipe_id'];
    $stmt = $pdo->prepare('SELECT apprenti_id FROM equipes WHERE id = :id');
    $stmt->execute(['id' => $equipe_id]);
    $row = $stmt->fetch();
    if ($row) {
        // Supprime l'apprenti : la contrainte ON DELETE CASCADE supprime équipe + messages envoyés par lui
        $pdo->prepare('DELETE FROM utilisateurs WHERE id = :id')->execute(['id' => $row['apprenti_id']]);
        $message_ok = 'Équipe, apprenti et messages associés supprimés.';
    }
}

$apprentis = $pdo->query(
    "SELECT id, nom, prenom FROM utilisateurs WHERE role = 'apprenti'
     AND id NOT IN (SELECT apprenti_id FROM equipes) ORDER BY nom"
)->fetchAll();

$tuteurs_ecole = $pdo->query(
    "SELECT id, nom, prenom FROM utilisateurs WHERE role = 'tuteur_ecole' ORDER BY nom"
)->fetchAll();

$tuteurs_entreprise = $pdo->query(
    "SELECT id, nom, prenom FROM utilisateurs WHERE role = 'tuteur_entreprise' ORDER BY nom"
)->fetchAll();

$equipes = $pdo->query(
    'SELECT e.id, a.nom AS a_nom, a.prenom AS a_prenom,
            te.nom AS te_nom, te.prenom AS te_prenom,
            ten.nom AS ten_nom, ten.prenom AS ten_prenom
     FROM equipes e
     JOIN utilisateurs a ON a.id = e.apprenti_id
     LEFT JOIN utilisateurs te ON te.id = e.tuteur_ecole_id
     LEFT JOIN utilisateurs ten ON ten.id = e.tuteur_entreprise_id
     ORDER BY a.nom'
)->fetchAll();

$chemin_racine = '../';
$titre_page = 'Gestion des équipes';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Équipes (apprenti + tuteurs)</h1>

<?php if ($message_ok): ?><div class="alert alert-success"><?= h($message_ok) ?></div><?php endif; ?>
<?php foreach ($erreurs as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Créer une équipe</h2>
        <?php if (!$apprentis): ?>
            <p class="text-muted mb-0">Tous les apprentis ont déjà une équipe, ou aucun apprenti n'a encore ce rôle.</p>
        <?php else: ?>
        <form method="post" class="row g-3">
            <input type="hidden" name="creer_equipe" value="1">
            <div class="col-12 col-md-4">
                <label class="form-label">Apprenti</label>
                <select name="apprenti_id" class="form-select" required>
                    <option value="">Choisir...</option>
                    <?php foreach ($apprentis as $a): ?>
                        <option value="<?= (int) $a['id'] ?>"><?= h($a['prenom'] . ' ' . $a['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Tuteur école</label>
                <select name="tuteur_ecole_id" class="form-select">
                    <option value="">Aucun</option>
                    <?php foreach ($tuteurs_ecole as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= h($t['prenom'] . ' ' . $t['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">Tuteur entreprise</label>
                <select name="tuteur_entreprise_id" class="form-select">
                    <option value="">Aucun</option>
                    <?php foreach ($tuteurs_entreprise as $t): ?>
                        <option value="<?= (int) $t['id'] ?>"><?= h($t['prenom'] . ' ' . $t['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Créer l'équipe</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<h2 class="h5">Équipes existantes</h2>
<?php if (!$equipes): ?>
    <p class="text-muted">Aucune équipe créée pour le moment.</p>
<?php else: ?>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
        <tr><th>Apprenti</th><th>Tuteur école</th><th>Tuteur entreprise</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($equipes as $e): ?>
        <tr>
            <td><?= h($e['a_prenom'] . ' ' . $e['a_nom']) ?></td>
            <td><?= $e['te_nom'] ? h($e['te_prenom'] . ' ' . $e['te_nom']) : '<span class="text-muted">—</span>' ?></td>
            <td><?= $e['ten_nom'] ? h($e['ten_prenom'] . ' ' . $e['ten_nom']) : '<span class="text-muted">—</span>' ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Supprimer cette équipe ? L\'apprenti et tous ses messages seront supprimés.');">
                    <input type="hidden" name="supprimer_equipe_id" value="<?= (int) $e['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
