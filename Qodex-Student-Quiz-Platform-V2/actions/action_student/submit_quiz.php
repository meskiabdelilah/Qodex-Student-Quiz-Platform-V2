<?php

require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/student/categories.php');
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    die("Accès refusé.");
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if ($quiz_id <= 0) {
    die("Erreur : Quiz invalide.");
}

$db = Database::getInstance();

try {

    $sql = "SELECT id, option1, option2, option3, option4, correct_option 
            FROM questions 
            WHERE quiz_id = ?";
            
    $stmt = $db->query($sql, [$quiz_id]);
    $questions_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($questions_db)) {
        die("Erreur : Ce quiz est vide (aucune question trouvée).");
    }

    $score = 0;
    $total_questions = count($questions_db);

    $correct_map = [];

    foreach ($questions_db as $q) {
        $correct_index = $q['correct_option']; 
        
        $col_name = 'option' . $correct_index; 
        
        if (isset($q[$col_name])) {
            $correct_map[$q['id']] = trim($q[$col_name]);
        }
    }

    foreach ($answers as $q_id => $user_answer_text) {
        if (isset($correct_map[$q_id])) {
            if (trim($user_answer_text) === $correct_map[$q_id]) {
                $score++;
            }
        }
    }

    $insertSql = "INSERT INTO results (quiz_id, etudiant_id, score, total_questions, completed_at) 
                  VALUES (?, ?, ?, ?, NOW())";
    
    $db->query($insertSql, [
        $quiz_id,
        $user_id,
        $score,
        $total_questions
    ]);

    header('Location: ../../pages/student/mes_resultats.php?success=1');
    exit();

} catch (Exception $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>