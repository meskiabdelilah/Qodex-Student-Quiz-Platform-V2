<?php

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    require_once '../../classes/Database.php';
    require_once '../../classes/Security.php';

    // 4. Sécurité : Gher l'étudiant li ydouz
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
        echo json_encode([
            'success' => false,
            'error' => 'Non autorisé'
        ]);
        exit();
    }

    // 5. Validation de l'ID
    $user_id = $_SESSION['user_id'];
    $quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

    if (!$quiz_id) {
        echo json_encode(['success' => false, 'error' => 'ID invalide']);
        exit();
    }

    $db = Database::getInstance();

    $checkSql = "SELECT id FROM results WHERE quiz_id = ? AND etudiant_id = ?";
    $stmt = $db->query($checkSql, [$quiz_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'already_taken',
            'message' => 'Quiz déjà passé'
        ]);
        exit();
    }

    $sql = "SELECT id, question, option1, option2, option3, option4 
            FROM questions 
            WHERE quiz_id = ?";

    $result = $db->query($sql, [$quiz_id]);
    $question = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $question
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) { 
    http_response_code(500);
    echo json_encode([  
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
