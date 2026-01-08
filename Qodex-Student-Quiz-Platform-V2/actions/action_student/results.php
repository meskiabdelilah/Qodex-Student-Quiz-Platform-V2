<?php

require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';

Security::requireStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../pages/student/categories.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if ($quiz_id === 0) {
    die("Erreur : Quiz ID introuvable.");
}

$db = Database::getInstance();

try {

    $sql = "SELECT id, reponse_correcte FROM questions WHERE quiz_id = ?";
    $result = $db->query($sql, [$quiz_id]);
    $questions_db = $result->fetchAll(PDO::FETCH_ASSOC);

    $score = 0;
    $total_questions = count($questions_db);

    $correct_answers_map = [];
    foreach ($questions_db as $q) {
        $correct_answers_map[$q['id']] = trim($q['reponse_correcte']);
    }

    foreach ($user_answers as $q_id => $user_answer) {
        if (isset($correct_answers_map[$q_id])) {
            if (trim($user_answer) === $correct_answers_map[$q_id]) {
                $score++;
            }
        }
    }


    $insertSql = "INSERT INTO results (etudiant_id, quiz_id, score, total_questions, completed_at) 
                  VALUES (?, ?, ?, ?, NOW())";
    
    $db->query($insertSql, [
        $user_id,
        $quiz_id,
        $score,
        $total_questions
    ]);

    header('Location: ../../pages/student/mes_resultats.php?success=1');
    exit();

} catch (Exception $e) {
    die("Erreur lors de la soumission : " . $e->getMessage());
}