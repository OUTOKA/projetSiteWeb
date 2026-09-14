# Dossier de Conception - Service d'Échange Apprentis / Tuteurs (ESIGELEC)

## 1. Arborescence du site

- **`/index.php`** (Page d'accueil / Présentation)
  ├── **`/login.php`** (Formulaire de connexion)
  ├── **`/register.php`** (Formulaire d'inscription - sans choix de rôle)
  ├── **`/dashboard.php`** (Espace personnel - Redirige selon le rôle)
  │    ├── **`/messages/index.php`** (Liste de la boîte de réception - Filtrage Lu/Non lu)
  │    ├── **`/messages/sent.php`** (Liste des messages envoyés - Bonus)
  │    ├── **`/messages/show.php?id=X`** (Lecture d'un message + Pièce jointe PDF)
  │    ├── **`/messages/create.php`** (Rédaction d'un message / Réponse)
  │    └── **`/messages/delete.php?id=X`** (Suppression d'un message - Bonus)
  └── **`/admin/index.php`** (Espace d'Administration)
       ├── **`/admin/users.php`** (Attribution des rôles : Apprenti, Tuteur École, Tuteur Entreprise)
       ├── **`/admin/teams.php`** (Gestion des équipes : Création / Modification / Suppression)

---

## 2. Description détaillée de chaque page

### 2.1 Page d'accueil (`index.php`)
- **Contenu :** Présentation du service d'échange entre apprentis et tuteurs (école & entreprise). En-tête avec logo, section héro avec appel à l'action.
- **Images :** Logo ESIGELEC / Illustration d'échange/tutorat.
- **Liens :** Connexion (`login.php`), Inscription (`register.php`).

### 2.2 Formulaire de connexion (`login.php`)
- **Contenu :** Champs Email et Mot de passe.
- **Liens :** Validation vers authentification, lien vers `register.php`.

### 2.3 Formulaire d'inscription (`register.php`)
- **Contenu :** Nom, Prénom, Email, Mot de passe. *Note : Pas de sélection de rôle à l'inscription.*
- **Liens :** Validation (crée le compte sans rôle), retour vers `login.php`.

### 2.4 Boîte de réception / Dashboard (`messages/index.php`)
- **Contenu :** Table/Liste des messages reçus. Badge visuel explicite pour les messages non lus. Affichage de la catégorie (Suivi, Info, etc.), expéditeur, date et heure.
- **Liens :** Lire (`show.php`), Nouveaux messages (`create.php`), Messages envoyés (`sent.php`).

### 2.5 Message & Réponse (`messages/show.php` & `create.php`)
- **Contenu :**
  - **Consultation :** Détails du message, lien de téléchargement/visuel du PDF joint, statut de lecture.
  - **Envoi/Réponse :** Choix des destinataires (1 ou 2 tuteurs/apprenti selon l'équipe), Titre, Catégorie, Texte, Champ d'envoi de fichier PDF.

### 2.6 Administration (`admin/users.php` & `admin/teams.php`)
- **Contenu :**
  - **Gestion utilisateurs :** Attribution du rôle (Apprenti, Tuteur École, Tuteur Entreprise).
  - **Gestion équipes :** Association d'un apprenti à son tuteur école ET son tuteur entreprise. Option de modification ou suppression d'équipe (avec suppression des messages associés).

---

## 3. Schéma de la Base de Données (Ébauche Relationnelle)

### Table : `users`
- `id` (INT, PK, AUTO_INCREMENT)
- `nom` (VARCHAR(50))
- `prenom` (VARCHAR(50))
- `email` (VARCHAR(100), UNIQUE)
- `password` (VARCHAR(255)) -- Hashé
- `role` (ENUM('pending', 'apprenti', 'tuteur_ecole', 'tuteur_entreprise', 'admin'))

### Table : `teams`
- `id` (INT, PK, AUTO_INCREMENT)
- `id_apprenti` (INT, FK -> users.id, UNIQUE) -- Un apprenti a une seule équipe
- `id_tuteur_ecole` (INT, FK -> users.id)
- `id_tuteur_entreprise` (INT, FK -> users.id)

### Table : `messages`
- `id` (INT, PK, AUTO_INCREMENT)
- `expediteur_id` (INT, FK -> users.id)
- `titre` (VARCHAR(150))
- `categorie` (VARCHAR(50)) -- Suivi, Info, etc.
- `texte` (TEXT)
- `fichier_pdf` (VARCHAR(255), NULLABLE) -- Chemin du fichier PDF
- `created_at` (DATETIME)

### Table : `message_recipients` (Gestion multi-destinataires & Statut de lecture)
- `id` (INT, PK, AUTO_INCREMENT)
- `message_id` (INT, FK -> messages.id ON DELETE CASCADE)
- `destinataire_id` (INT, FK -> users.id)
- `is_read` (BOOLEAN, DEFAULT FALSE)
- `read_at` (DATETIME, NULLABLE)