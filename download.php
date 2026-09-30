<?php
/**
 * Téléchargement sécurisé d'une pièce jointe : sert le fichier uniquement si
 * l'utilisateur connecté est l'expéditeur ou un destinataire du message concerné.
 * Les fichiers ne sont jamais accessibles directement dans /uploads (voir .htaccess).
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
requiert_connexion();

$user_id    = $_SESSION['user_id'];
$message_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT expediteur_id, document, titre FROM messages WHERE id = :id');
$stmt->execute(['id' => $message_id]);
$message = $stmt->fetch();

if (!$message || !$message['document']) {
    http_response_code(404);
    die('Fichier introuvable.');
}

$est_expediteur = ((int) $message['expediteur_id'] === $user_id);

$stmtDest = $pdo->prepare(
    'SELECT 1 FROM message_destinataires WHERE message_id = :mid AND destinataire_id = :uid'
);
$stmtDest->execute(['mid' => $message_id, 'uid' => $user_id]);
$est_destinataire = (bool) $stmtDest->fetchColumn();

if (!$est_expediteur && !$est_destinataire) {
    http_response_code(403);
    die('Accès refusé.');
}

// basename() empêche toute tentative de path traversal (../..)
$chemin_fichier = __DIR__ . '/uploads/' . basename($message['document']);
if (!is_file($chemin_fichier)) {
    http_response_code(404);
    die('Fichier introuvable.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($message['titre']) . '.pdf"');
header('Content-Length: ' . filesize($chemin_fichier));
readfile($chemin_fichier);
exit;
