# QuizManager - Plateforme d'Évaluation en Ligne (V2)

## Description

**QuizManager** est une application web éducative développée en **PHP Orienté Objet (OOP)**. Cette version **(V2)** se concentre spécifiquement sur l'expérience **Étudiant**.

L'application permet aux étudiants de s'inscrire, d'explorer des catégories de connaissances, de passer des quiz interactifs créés par les enseignants, et de suivre leur progression en temps réel avec un haut niveau de sécurité.

---

## Fonctionnalités Principales (Espace Étudiant)

### Authentification & Sécurité

- **Inscription & Connexion** : Sécurisées avec hachage de mot de passe (`password_hash`).
- **Gestion de Session** : Protection contre le vol de session et régénération d'ID.
- **Contrôle d'accès** : Redirection stricte via `Security::requireStudent()`.

### Navigation & Quiz

- **Catalogue** : Visualisation des catégories et des quiz actifs.
- **Passage de Quiz** : Interface fluide pour répondre aux questions.
- **Système de Tentatives** : Gestion des tentatives (classe `Attempt`) pour limiter ou suivre les essais.

### Résultats & Suivi

- **Calcul Automatique** : Correction instantanée côté serveur (anti-triche).
- **Historique** : Tableau de bord personnel avec les scores et dates de passage.

---

## Architecture Technique

Le projet respecte les principes de la **Programmation Orientée Objet (OOP)** et suit une structure claire.

### Classes Principales

- **`User`** : Gestion des utilisateurs (Étudiants/Enseignants).
- **`Category`** : Organisation des thématiques (Lecture seule pour l'étudiant).
- **`Quiz`** : Entité principale contenant les paramètres du test.
- **`Question`** : Gestion des questions et options (la bonne réponse est masquée).
- **`Attempt`** : (Nouveau) Suivi du cycle de vie d'une tentative (Début/Fin).
- **`Result`** : Stockage des scores finaux.

### Mesures de Sécurité (Security First)

- **CSRF Protection** : Tokens générés et vérifiés pour chaque formulaire (Login, Inscription, Soumission Quiz).
- **SQL Injection** : Utilisation exclusive de **PDO Prepared Statements**.
- **XSS Protection** : Échappement des sorties (`htmlspecialchars`) sur toutes les vues.
- **Validations** : Sanitization des entrées (Email, ID, Réponses).

---

## Installation

1. **Cloner le dépôt**Bash
    
    `git clone https://github.com/votre-username/quiz-manager-v2.git`
    
2. **Base de données**
    - Créez une base de données MySQL (ex: `quiz_db`).
    - Importez le fichier `database/schema.sql` (ou `script.sql`).
    - Configurez la connexion dans `config/database.php`.
3. **Lancement**
    - Hébergez le projet sous un serveur local (XAMPP, Laragon, ou PHP built-in server).
    - Accédez à `http://localhost/quiz-manager/public/`.