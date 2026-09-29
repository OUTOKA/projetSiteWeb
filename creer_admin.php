<?php
/**
 * SCRIPT À USAGE UNIQUE
 * Crée (ou met à jour) le compte administrateur avec un mot de passe hashé
 * correctement via password_hash(). À lancer une seule fois depuis le
 * navigateur (ex: http://votre-site/creer_admin.php), PUIS À SUPPRIMER
 * (ou renommer) immédiatement après usage pour des raisons de sécurité.
 */
require_once __DIR__ . '/config/db.php';

$email = 'admin@esigelec.fr';
$mdp   = 'Admin123!'; // à changer ici avant de lancer le script, puis à modifier après connexion

$hash = password_hash($mdp, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, role)
     VALUES (:email, :mdp, "Administrateur", "Site", "admin")
     ON DUPLICATE KEY UPDATE mot_de_passe = :mdp2, role = "admin"'
);
$stmt->execute(['email' => $email, 'mdp' => $hash, 'mdp2' => $hash]);

echo "Compte administrateur prêt.<br>";
echo "Email : " . htmlspecialchars($email) . "<br>";
echo "Mot de passe : celui défini dans ce script.<br>";
echo "<strong>Supprimez ce fichier (creer_admin.php) maintenant.</strong>";
