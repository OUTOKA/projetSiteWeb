<?php
/**
 * Connexion PDO à la base de données.
 */

const DB_HOST = 'localhost';
const DB_NAME = 'bdd_11_7';
const DB_USER = 'grp_11_7';
const DB_PASS = 'mohfKn9SExaC7L18';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Erreur de connexion à la base de données. Contactez l\'administrateur.');
}
