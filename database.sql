-- =====================================================================
-- Base de données : Messagerie Apprentis / Tuteurs (ESIGELEC)
-- MO Conception de sites web - 2026 semestre 7
-- =====================================================================

CREATE DATABASE IF NOT EXISTS messagerie_apprentis
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE messagerie_apprentis;

-- ---------------------------------------------------------------------
-- Table utilisateurs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    role            ENUM('non_defini','apprenti','tuteur_ecole','tuteur_entreprise','admin')
                        NOT NULL DEFAULT 'non_defini',
    date_creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table equipes : un apprenti + son tuteur école + son tuteur entreprise
-- Un apprenti n'a qu'une seule équipe (contrainte UNIQUE sur apprenti_id)
-- Un tuteur peut apparaître dans plusieurs équipes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS equipes (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    apprenti_id          INT NOT NULL UNIQUE,
    tuteur_ecole_id      INT NULL,
    tuteur_entreprise_id INT NULL,
    date_creation        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apprenti_id)          REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (tuteur_ecole_id)      REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (tuteur_entreprise_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id      INT NOT NULL,
    titre              VARCHAR(200) NOT NULL,
    categorie          VARCHAR(50) NOT NULL,
    texte              TEXT NOT NULL,
    document           VARCHAR(255) NULL,
    date_envoi         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    message_parent_id  INT NULL,
    FOREIGN KEY (expediteur_id)     REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (message_parent_id) REFERENCES messages(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table message_destinataires : 1 ou 2 destinataires par message,
-- avec suivi de lecture (bonus) et suppression individuelle (bonus)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS message_destinataires (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    message_id       INT NOT NULL,
    destinataire_id  INT NOT NULL,
    lu               TINYINT(1) NOT NULL DEFAULT 0,
    date_lecture     DATETIME NULL,
    supprime         TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (message_id)      REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_msg_dest (message_id, destinataire_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Compte administrateur par défaut
-- Ne pas insérer de mot de passe "en dur" ici : lancez une seule fois le
-- script creer_admin.php (à la racine du site) pour créer le compte admin
-- avec un hash BCRYPT correctement généré par PHP. Voir README.md.
-- ---------------------------------------------------------------------
