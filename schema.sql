CREATE DATABASE IF NOT EXISTS `esigelec_tutorat` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `esigelec_tutorat`;

-- Table Utilisateurs
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(50) NOT NULL,
    `prenom` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('pending', 'apprenti', 'tuteur_ecole', 'tuteur_entreprise', 'admin') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table Équipes (1 Apprenti <-> 1 Tuteur École + 1 Tuteur Entreprise)
CREATE TABLE `teams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_apprenti` INT NOT NULL UNIQUE,
    `id_tuteur_ecole` INT NOT NULL,
    `id_tuteur_entreprise` INT NOT NULL,
    FOREIGN KEY (`id_apprenti`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_tuteur_ecole`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_tuteur_entreprise`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table Messages
CREATE TABLE `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `expediteur_id` INT NOT NULL,
    `titre` VARCHAR(150) NOT NULL,
    `categorie` VARCHAR(50) NOT NULL,
    `texte` TEXT NOT NULL,
    `fichier_pdf` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`expediteur_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table Destinataires & Suivi de lecture
CREATE TABLE `message_recipients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `message_id` INT NOT NULL,
    `destinataire_id` INT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destinataire_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertion de l'administrateur par défaut (Mot de passe: admin123 hashé)
INSERT INTO `users` (`nom`, `prenom`, `email`, `password`, `role`) 
VALUES ('Admin', 'ESIGELEC', 'admin@esigelec.fr', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe112k4t7a1cT2y0WJ74v.8E.A61/X9Wi', 'admin');