# Messagerie Apprentis / Tuteurs — ESIGELEC

Projet réalisé pour le module **MO Conception de sites web** (2026, semestre 7).
Site PHP / MySQL / Bootstrap 5 permettant à un apprenti d'échanger avec son
tuteur école et son tuteur entreprise (messages avec pièce jointe PDF, suivi
de lecture), avec un espace d'administration pour attribuer les rôles et
créer les équipes.

## Prérequis

- PHP 8.1+ (utilise `match`, `fn`, etc.)
- MySQL / MariaDB
- Un serveur web (Apache recommandé, `.htaccess` fourni pour `/uploads`)

## Installation

1. Créer la base de données :
   ```
   mysql -u root -p < database.sql
   ```
2. Configurer l'accès à la base dans `config/db.php` (hôte, nom de la base,
   utilisateur, mot de passe).
3. Donner les droits d'écriture au dossier `uploads/` (stockage des PDF joints) :
   ```
   chmod 755 uploads
   ```
4. Créer le compte administrateur : modifier le mot de passe souhaité dans
   `creer_admin.php`, puis charger la page une fois dans le navigateur
   (`http://votre-site/creer_admin.php`). **Supprimer ensuite ce fichier.**
5. Se connecter avec le compte admin, puis :
   - attribuer les rôles aux comptes inscrits (`admin/users.php`),
   - créer les équipes apprenti / tuteur école / tuteur entreprise (`admin/teams.php`).

## Structure du projet

```
config/db.php          Connexion PDO (requêtes préparées)
includes/functions.php Fonctions communes (session, sécurité, accès aux droits)
includes/header.php    Gabarit commun : en-tête + navigation Bootstrap
includes/footer.php    Gabarit commun : pied de page
css/style.css          Feuille de style externe
index.php               Page d'accueil (visiteur)
register.php            Création de compte (sans choix du rôle)
login.php / logout.php  Authentification
dashboard.php            Redirection selon le rôle
inbox.php                Messages reçus (+ suppression, bonus)
sent.php                 Messages envoyés (bonus)
compose.php               Nouveau message / réponse (destinataire(s), pièce jointe PDF)
message.php                Lecture d'un message + statut de lecture (bonus)
download.php               Téléchargement sécurisé d'une pièce jointe
admin/index.php             Tableau de bord admin
admin/users.php              Attribution des rôles
admin/teams.php               Gestion des équipes (création + suppression, bonus)
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

## Ce qu'il reste à faire (hors code)

Le sujet demande également, pour le Labo 1 (conception), un dossier séparé :
schéma d'arborescence du site, maquette (Pencil) de la page d'accueil en
version mobile et fixe, description de chaque page, ébauche du diagramme de
base de données. Le schéma de base de données (`database.sql`) correspondant
à ce diagramme est fourni ici ; la maquette visuelle (Pencil) reste à réaliser
séparément, l'outil produisant un format graphique propre.
