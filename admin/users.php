<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
requiert_role(['admin']);

$roles_valides = ['non_defini', 'apprenti', 'tuteur_ecole', 'tuteur_entreprise', 'admin'];
$message_ok = null;
$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $nouveau_role = isset($_POST['role']) ? $_POST['role'] : '';

    if (!in_array($nouveau_role, $roles_valides, true)) {
        $erreur = 'Rôle invalide.';
    } elseif ($user_id === (int) $_SESSION['user_id'] && $nouveau_role !== 'admin') {
        $erreur = 'Vous ne pouvez pas retirer votre propre rôle administrateur.';
    } else {
        $stmt = $pdo->prepare('UPDATE utilisateurs SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $nouveau_role, 'id' => $user_id]);
        $message_ok = 'Rôle mis à jour avec succès.';
    }
}

$utilisateurs = $pdo->query(
    'SELECT id, email, nom, prenom, role, date_creation FROM utilisateurs ORDER BY date_creation DESC'
)->fetchAll();

$chemin_racine = '../';
$titre_page = 'Gestion des utilisateurs';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Utilisateurs et attribution des rôles</h1>

<?php if ($message_ok): ?><div class="alert alert-success"><?= h($message_ok) ?></div><?php endif; ?>
<?php if ($erreur): ?><div class="alert alert-danger"><?= h($erreur) ?></div><?php endif; ?>

<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>Nom</th>
            <th>E-mail</th>
            <th>Inscrit le</th>
            <th>Rôle</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($utilisateurs as $u): ?>
        <tr>
            <td><?= h($u['prenom'] . ' ' . $u['nom']) ?></td>
            <td><?= h($u['email']) ?></td>
            <td><?= h(date('d/m/Y', strtotime($u['date_creation']))) ?></td>
            <td>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                    <select name="role" class="form-select form-select-sm">
                        <?php foreach ($roles_valides as $r): ?>
                            <option value="<?= h($r) ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= h(libelle_role($r)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Valider</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
