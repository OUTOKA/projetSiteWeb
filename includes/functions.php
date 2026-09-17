<?php
/**
 * Fonctions utilitaires communes : sécurité, session, accès aux données.
 * Compatible PHP 5.6 (pas de types scalaires, pas de ??, pas de match, pas de fn()).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Échappe une chaîne pour un affichage HTML sûr (anti XSS). */
function h($value)
{
    if ($value === null) {
        $value = '';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** L'utilisateur est-il connecté ? */
function est_connecte()
{
    return isset($_SESSION['user_id']);
}

/** Rôle de l'utilisateur connecté, ou null. */
function role_utilisateur()
{
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}

/** Redirige vers login.php si l'utilisateur n'est pas connecté. */
function requiert_connexion()
{
    if (!est_connecte()) {
        header('Location: login.php');
        exit;
    }
}

/** Redirige si l'utilisateur connecté n'a pas l'un des rôles autorisés. */
function requiert_role($roles_autorises)
{
    requiert_connexion();
    if (!in_array(role_utilisateur(), $roles_autorises, true)) {
        http_response_code(403);
        die('Accès refusé : vous n\'avez pas les droits nécessaires pour accéder à cette page.');
    }
}

/** Libellé humain d'un rôle. */
function libelle_role($role)
{
    switch ($role) {
        case 'apprenti':
            return 'Apprenti';
        case 'tuteur_ecole':
            return 'Tuteur école';
        case 'tuteur_entreprise':
            return 'Tuteur entreprise';
        case 'admin':
            return 'Administrateur';
        default:
            return 'Rôle non attribué';
    }
}

/**
 * Retourne la liste des destinataires possibles pour l'utilisateur connecté :
 * les autres membres de sa/ses équipe(s) (hors lui-même).
 */
function destinataires_possibles(PDO $pdo, $user_id, $role)
{
    $destinataires = array();

    if ($role === 'apprenti') {
        $stmt = $pdo->prepare(
            'SELECT u.id, u.nom, u.prenom, u.role
             FROM equipes e
             JOIN utilisateurs u ON u.id IN (e.tuteur_ecole_id, e.tuteur_entreprise_id)
             WHERE e.apprenti_id = :uid'
        );
        $stmt->execute(array('uid' => $user_id));
        $destinataires = $stmt->fetchAll();
    } elseif ($role === 'tuteur_ecole' || $role === 'tuteur_entreprise') {
        // Un tuteur peut écrire à chacun de ses apprentis suivis
        $colonne = $role === 'tuteur_ecole' ? 'tuteur_ecole_id' : 'tuteur_entreprise_id';
        $stmt = $pdo->prepare(
            "SELECT u.id, u.nom, u.prenom, u.role
             FROM equipes e
             JOIN utilisateurs u ON u.id = e.apprenti_id
             WHERE e.$colonne = :uid"
        );
        $stmt->execute(array('uid' => $user_id));
        $destinataires = $stmt->fetchAll();
    }

    return $destinataires;
}

/**
 * Vérifie que le destinataire choisi est bien autorisé (même équipe) pour éviter
 * qu'un utilisateur ne force l'envoi à quelqu'un hors de son équipe.
 */
function destinataire_autorise(PDO $pdo, $user_id, $role, $destinataire_id)
{
    $liste = destinataires_possibles($pdo, $user_id, $role);
    foreach ($liste as $d) {
        if ((int) $d['id'] === (int) $destinataire_id) {
            return true;
        }
    }
    return false;
}

/** Catégories de messages disponibles. */
function categories_messages()
{
    return array('Suivi', 'Info', 'Question', 'Autre');
}

/** Génère un nom de fichier aléatoire (remplace random_bytes(), absent avant PHP 7). */
function nom_fichier_aleatoire($extension)
{
    $octets = function_exists('openssl_random_pseudo_bytes')
        ? openssl_random_pseudo_bytes(16)
        : md5(uniqid(mt_rand(), true));
    return bin2hex($octets) . '.' . $extension;
}
