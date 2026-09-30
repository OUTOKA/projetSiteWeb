<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
requiert_role(['apprenti', 'tuteur_ecole', 'tuteur_entreprise']);

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT m.id, m.titre, m.categorie, m.date_envoi, m.document,
            GROUP_CONCAT(DISTINCT CONCAT(u.prenom, " ", u.nom) SEPARATOR ", ") AS destinataires
     FROM messages m
     JOIN message_destinataires md ON md.message_id = m.id
     JOIN utilisateurs u ON u.id = md.destinataire_id
     WHERE m.expediteur_id = :uid
     GROUP BY m.id
     ORDER BY m.date_envoi DESC'
);
$stmt->execute(['uid' => $user_id]);
$messages = $stmt->fetchAll();

$chemin_racine = '';
$titre_page = 'Messages envoyés';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Messages envoyés</h1>

<?php if (!$messages): ?>
    <p class="text-muted">Vous n'avez envoyé aucun message pour le moment.</p>
<?php else: ?>
    <div class="list-group">
        <?php foreach ($messages as $m): ?>
            <a href="message.php?id=<?= (int) $m['id'] ?>&mode=envoye" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <span>
                        <span class="badge bg-secondary"><?= h($m['categorie']) ?></span>
                        <strong><?= h($m['titre']) ?></strong>
                        <?php if ($m['document']): ?><span title="Pièce jointe">📎</span><?php endif; ?>
                        <br><small>À : <?= h($m['destinataires']) ?></small>
                    </span>
                    <small class="text-muted"><?= h(date('d/m/Y H:i', strtotime($m['date_envoi']))) ?></small>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
