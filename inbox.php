<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
requiert_role(array('apprenti', 'tuteur_ecole', 'tuteur_entreprise'));

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_id'])) {
    $stmt = $pdo->prepare(
        'UPDATE message_destinataires SET supprime = 1
         WHERE message_id = :mid AND destinataire_id = :uid'
    );
    $stmt->execute(array('mid' => (int) $_POST['supprimer_id'], 'uid' => $user_id));
    header('Location: inbox.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT m.id, m.titre, m.categorie, m.date_envoi, m.document,
            u.nom AS exp_nom, u.prenom AS exp_prenom,
            md.lu
     FROM message_destinataires md
     JOIN messages m       ON m.id = md.message_id
     JOIN utilisateurs u   ON u.id = m.expediteur_id
     WHERE md.destinataire_id = :uid AND md.supprime = 0
     ORDER BY m.date_envoi DESC'
);
$stmt->execute(array('uid' => $user_id));
$messages = $stmt->fetchAll();

$chemin_racine = '';
$titre_page = 'Boîte de réception';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Boîte de réception</h1>
    <a href="compose.php" class="btn btn-primary">+ Nouveau message</a>
</div>

<?php if (!$messages): ?>
    <p class="text-muted">Vous n'avez reçu aucun message pour le moment.</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($messages as $m): ?>
            <div class="list-group-item d-flex justify-content-between align-items-start <?php echo $m['lu'] ? '' : 'message-non-lu'; ?>">
                <a href="message.php?id=<?php echo (int) $m['id']; ?>" class="text-decoration-none text-dark flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <span>
                            <?php if (!$m['lu']): ?><span class="badge bg-primary">Nouveau</span><?php endif; ?>
                            <strong><?php echo h($m['exp_prenom'] . ' ' . $m['exp_nom']); ?></strong>
                            — <span class="badge bg-secondary"><?php echo h($m['categorie']); ?></span>
                            <?php echo h($m['titre']); ?>
                            <?php if ($m['document']): ?><span title="Pièce jointe">📎</span><?php endif; ?>
                        </span>
                    </div>
                    <small class="text-muted"><?php echo h(date('d/m/Y H:i', strtotime($m['date_envoi']))); ?></small>
                </a>
                <form method="post" onsubmit="return confirm('Supprimer ce message de votre boîte ?');" class="ms-2">
                    <input type="hidden" name="supprimer_id" value="<?php echo (int) $m['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
