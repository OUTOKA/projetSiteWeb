# Messagerie Apprentis / Tuteurs — ESIGELEC

Projet réalisé pour le module **MO Conception de sites web** (2026, semestre 7).
Site PHP permettant à un apprenti d'échanger avec son
tuteur école et son tuteur entreprise (messages avec pièce jointe PDF, suivi
de lecture), avec un espace d'administration pour attribuer les rôles et
créer les équipes.


## Connexion

Se rendre sur https://moduleweb.fr/grp_11_7/

Se connecter avec les identifiants présents dans le fichier identifiants.txt

OU

Créer un compte

## Structure du projet

```
config/db.php          Connexion PDO (requêtes préparées)
includes/functions.php Fonctions communes (session, sécurité, accès aux droits)
includes/header.php    Gabarit commun : en-tête + navigation Bootstrap
includes/footer.php    Gabarit commun : pied de page
css/style.css          Feuille de style externe
index.php               Page d'accueil 
register.php            Création de compte 
login.php / logout.php  Authentification
dashboard.php            Redirection selon le rôle
inbox.php                Messages reçus 
sent.php                 Messages envoyés 
compose.php               Nouveau message / réponse (destinataire(s), pièce jointe PDF)
message.php                Lecture d'un message + statut de lecture 
download.php               Téléchargement sécurisé d'une pièce jointe
admin/index.php             Tableau de bord admin
admin/users.php              Attribution des rôles
admin/teams.php               Gestion des équipes 
database.sql                   Script de création de la base de données
creer_admin.php                  Script à usage unique pour créer le compte admin
```

## Sécurité mise en œuvre

- Toutes les requêtes SQL utilisent des requêtes préparées PDO (anti injection SQL).
- Tous les affichages dynamiques passent par `h()` (`htmlspecialchars`) contre le XSS.
- Mots de passe hashés avec `password_hash()` / vérifiés avec `password_verify()`.
- Sessions PHP avec régénération de l'identifiant à la connexion.
- Contrôle d'accès sur chaque page (`requiert_connexion()`, `requiert_role()`),
  y compris sur les actions (répondre, supprimer, télécharger).
- Un message n'est lisible que par son expéditeur ou ses destinataires ; un
  destinataire n'est proposé à l'envoi que s'il appartient à la même équipe.
- Pièces jointes : extension et type MIME vérifiés (PDF uniquement), taille
  limitée, nom de fichier régénéré aléatoirement, dossier `uploads/` interdit
  d'accès direct (`.htaccess`), téléchargement uniquement via `download.php`
  après vérification des droits.


