<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
requiert_role(array('apprenti', 'tuteur_ecole', 'tuteur_entreprise'));

$user_id = $_SESSION['user_id'];
$role    = role_utilisateur();

$destinataires_possibles = destinataires_possibles($pdo, $user_id, $role);

$erreurs = array();
$titre = $texte = '';
$categorie_choisie = '';
$destinataires_choisis = array();

// Pré-remplissage si on répond à un message (repondre_a en GET)
$message_parent_id = isset($_GET['repondre_a']) ? (int) $_GET['repondre_a'] : null;
if ($message_parent_id) {
    $stmt = $pdo->prepare('SELECT titre, expediteur_id FROM messages WHERE id = :id');
    $stmt->execute(array('id' => $message_parent_id));
    $parent = $stmt->fetch();
    if ($parent) {
        $titre = 'Re: ' . $parent['titre'];
        $destinataires_choisis = array((int) $parent['expediteur_id']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre              = trim(isset($_POST['titre']) ? $_POST['titre'] : '');
    $categorie_choisie  = trim(isset($_POST['categorie']) ? $_POST['categorie'] : '');
    $texte              = trim(isset($_POST['texte']) ? $_POST['texte'] : '');
    $destinataires_post = isset($_POST['destinataires']) ? $_POST['destinataires'] : array();
    $destinataires_choisis = array_map('intval', $destinataires_post);
    $message_parent_id  = !empty($_POST['message_parent_id']) ? (int) $_POST['message_parent_id'] : null;

    if ($titre === '') {
        $erreurs[] = 'Le titre est obligatoire.';
    }
    if (!in_array($categorie_choisie, categories_messages(), true)) {
        $erreurs[] = 'Catégorie invalide.';
    }
    if ($texte === '') {
        $erreurs[] = 'Le message ne peut pas être vide.';
    }
    if (!$destinataires_choisis || count($destinataires_choisis) > 2) {
        $erreurs[] = 'Choisissez un ou deux destinataires.';
    } else {
        foreach ($destinataires_choisis as $did) {
            if (!destinataire_autorise($pdo, $user_id, $role, $did)) {
                $erreurs[] = 'Destinataire non autorisé.';
                break;
            }
        }
    }

    // Pièce jointe PDF (facultative)
    $nom_fichier_stocke = null;
    if (!empty($_FILES['document']['name']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fichier = $_FILES['document'];
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur lors de l'envoi du fichier.";
        } elseif ($fichier['size'] > 5 * 1024 * 1024) {
            $erreurs[] = 'Le fichier ne doit pas dépasser 5 Mo.';
        } else {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fichier['tmp_name']);
            finfo_close($finfo);
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

            if ($mimeType !== 'application/pdf' || $extension !== 'pdf') {
                $erreurs[] = 'Seuls les fichiers PDF sont acceptés.';
            } else {
                $nom_fichier_stocke = nom_fichier_aleatoire('pdf');
                $destination = __DIR__ . '/uploads/' . $nom_fichier_stocke;
                if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
                    $erreurs[] = 'Impossible d\'enregistrer le fichier joint.';
                    $nom_fichier_stocke = null;
                }
            }
        }
    }

    if (!$erreurs) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO messages (expediteur_id, titre, categorie, texte, document, message_parent_id)
                 VALUES (:exp, :titre, :cat, :texte, :doc, :parent)'
            );
            $stmt->execute(array(
                'exp'    => $user_id,
                'titre'  => $titre,
                'cat'    => $categorie_choisie,
                'texte'  => $texte,
                'doc'    => $nom_fichier_stocke,
                'parent' => $message_parent_id,
            ));
            $message_id = (int) $pdo->lastInsertId();

            $stmtDest = $pdo->prepare(
                'INSERT INTO message_destinataires (message_id, destinataire_id) VALUES (:mid, :did)'
            );
            foreach (array_unique($destinataires_choisis) as $did) {
                $stmtDest->execute(array('mid' => $message_id, 'did' => $did));
            }

            $pdo->commit();
            header('Location: sent.php?envoi=ok');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = "Erreur lors de l'envoi du message.";
        }
    }
}

$chemin_racine = '';
$titre_page = 'Nouveau message';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <h1 class="h3 mb-3">Nouveau message</h1>

        <?php foreach ($erreurs as $e): ?>
            <div class="alert alert-danger"><?php echo h($e); ?></div>
        <?php endforeach; ?>

        <?php if (!$destinataires_possibles): ?>
            <div class="alert alert-warning">
                Aucun destinataire disponible : votre équipe n'a pas encore été constituée par l'administrateur.
            </div>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data">
            <?php if ($message_parent_id): ?>
                <input type="hidden" name="message_parent_id" value="<?php echo (int) $message_parent_id; ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Destinataire(s)</label>
                <?php foreach ($destinataires_possibles as $d): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="destinataires[]"
                               id="dest<?php echo (int) $d['id']; ?>" value="<?php echo (int) $d['id']; ?>"
                               <?php echo in_array((int) $d['id'], $destinataires_choisis, true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="dest<?php echo (int) $d['id']; ?>">
                            <?php echo h($d['prenom'] . ' ' . $d['nom']); ?> (<?php echo h(libelle_role($d['role'])); ?>)
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-12 col-md-8 mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?php echo h($titre); ?>" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="categorie" name="categorie" required>
                        <option value="" disabled <?php echo $categorie_choisie === '' ? 'selected' : ''; ?>>Choisir...</option>
                        <?php foreach (categories_messages() as $cat): ?>
                            <option value="<?php echo h($cat); ?>" <?php echo $categorie_choisie === $cat ? 'selected' : ''; ?>><?php echo h($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="texte" class="form-label">Message</label>
                <textarea class="form-control" id="texte" name="texte" rows="6" required><?php echo h($texte); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="document" class="form-label">Pièce jointe (PDF, 5 Mo max, facultatif)</label>
                <input type="file" class="form-control" id="document" name="document" accept="application/pdf">
            </div>

            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
