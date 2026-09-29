<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
requiert_role(array('apprenti', 'tuteur_ecole', 'tuteur_entreprise'));

$user_id    = $_SESSION['user_id'];
$message_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT m.*, u.nom AS exp_nom, u.prenom AS exp_prenom
     FROM messages m
     JOIN utilisateurs u ON u.id = m.expediteur_id
     WHERE m.id = :id'
);
$stmt->execute(array('id' => $message_id));
$message = $stmt->fetch();

if (!$message) {
    http_response_code(404);
    die('Message introuvable.');
}

// Contrôle d'accès : uniquement l'expéditeur ou un destinataire du message
$est_expediteur = ((int) $message['expediteur_id'] === $user_id);

$stmtDest = $pdo->prepare(
    'SELECT md.*, u.nom, u.prenom
     FROM message_destinataires md
     JOIN utilisateurs u ON u.id = md.destinataire_id
     WHERE md.message_id = :mid'
);
$stmtDest->execute(array('mid' => $message_id));
$destinataires = $stmtDest->fetchAll();

$est_destinataire = false;
$ma_ligne_destinataire = null;
foreach ($destinataires as $d) {
    if ((int) $d['destinataire_id'] === $user_id) {
        $est_destinataire = true;
        $ma_ligne_destinataire = $d;
    }
}

if (!$est_expediteur && !$est_destinataire) {
    http_response_code(403);
    die('Vous n\'avez pas accès à ce message.');
}

// Marquer comme lu (si je suis destinataire et pas encore lu)
if ($est_destinataire && (int) $ma_ligne_destinataire['lu'] === 0) {
    $upd = $pdo->prepare('UPDATE message_destinataires SET lu = 1, date_lecture = NOW() WHERE id = :id');
    $upd->execute(array('id' => $ma_ligne_destinataire['id']));
}

$chemin_racine = '';
$titre_page = $message['titre'];
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="<?php echo $est_expediteur ? 'sent.php' : 'inbox.php'; ?>" class="btn btn-sm btn-outline-secondary mb-3">&larr; Retour</a>

        <div class="card card-message">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap">
                    <h1 class="h4"><?php echo h($message['titre']); ?></h1>
                    <span class="badge bg-secondary align-self-start"><?php echo h($message['categorie']); ?></span>
                </div>
                <p class="text-muted mb-1">
                    De : <?php echo h($message['exp_prenom'] . ' ' . $message['exp_nom']); ?><br>
                    À :
                    <?php
                    $noms_destinataires = array();
                    foreach ($destinataires as $d) {
                        $noms_destinataires[] = $d['prenom'] . ' ' . $d['nom'];
                    }
                    echo h(implode(', ', $noms_destinataires));
                    ?><br>
                    Envoyé le <?php echo h(date('d/m/Y à H:i', strtotime($message['date_envoi']))); ?>
                </p>
                <hr>
                <p style="white-space: pre-wrap;"><?php echo h($message['texte']); ?></p>

                <?php if ($message['document']): ?>
                    <a href="download.php?id=<?php echo (int) $message['id']; ?>" class="btn btn-outline-primary btn-sm">
                        📎 Télécharger la pièce jointe (PDF)
                    </a>
                <?php endif; ?>

                <?php if ($est_expediteur): ?>
                    <hr>
                    <h2 class="h6">Statut de lecture</h2>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($destinataires as $d): ?>
                            <li>
                                <?php echo h($d['prenom'] . ' ' . $d['nom']); ?> :
                                <?php if ($d['lu']): ?>
                                    <span class="badge bg-success">Lu le <?php echo h(date('d/m/Y H:i', strtotime($d['date_lecture']))); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Non lu</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($est_destinataire): ?>
            <a href="compose.php?repondre_a=<?php echo (int) $message['id']; ?>" class="btn btn-primary mt-3">Répondre</a>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
