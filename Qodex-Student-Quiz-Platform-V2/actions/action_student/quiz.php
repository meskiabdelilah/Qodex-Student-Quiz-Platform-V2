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
    $category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

    if (!$category_id) {
        echo json_encode(['success' => false, 'error' => 'ID invalide']);
        exit();
    }

    $db = Database::getInstance();

    $sql = "SELECT id, titre, description
            FROM quiz 
            WHERE categorie_id  
            AND is_active = 1";

    $result = $db->query($sql);
    $quizzes = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $quizzes
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erruer serveur',
        'message'=>$e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
?>