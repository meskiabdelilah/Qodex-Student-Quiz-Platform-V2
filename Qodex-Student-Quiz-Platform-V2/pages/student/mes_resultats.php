<?php

require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
include '../partials/header.php';
require_once '../partials/nav_student.php';

Security::requireStudent();

$user_id = $_SESSION['user_id'];
$db = Database::getInstance();

try {
    $sql = "SELECT 
            r.score, 
            r.total_questions, 
            r.completed_at, 
            q.titre as quiz_titre, 
            c.nom as categorie_nom
          FROM results r
          JOIN quiz q ON r.quiz_id = q.id
          LEFT JOIN categories c ON q.categorie_id = c.id
          WHERE r.etudiant_id = ?
          ORDER BY r.completed_at DESC";

    $result = $db->query($sql, [$user_id]);
    $results = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $results = [];
    $error = "Erreur de chargement: " . $e->getMessage();
}
?>

<div class="min-h-screen bg-gray-50 pt-24 pb-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'already_taken'): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r shadow-sm animate-fade-in-down">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <span class="font-bold">Attention :</span>
                            Vous avez déjà passé ce quiz. Une seule tentative est autorisée.
                            Voici vos résultats.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mes Résultats</h1>
                <p class="text-gray-500 mt-1">Historique de vos performances.</p>
            </div>
            <div class="hidden sm:flex space-x-4">
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                    <span class="text-xs text-gray-500 uppercase font-bold">Total Quiz</span>
                    <div class="text-xl font-bold text-indigo-600"><?= count($results) ?></div>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <?php if (count($results) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz / Catégorie</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($results as $row):
                                $score = $row['score'];
                                $total = $row['total_questions'];
                                $percent = ($total > 0) ? ($score / $total) * 100 : 0;
                                $is_success = $percent >= 50;
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                                <i class="fas fa-book-open"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['quiz_titre']) ?></div>
                                                <div class="text-xs text-gray-500"><?= htmlspecialchars($row['categorie_nom'] ?? 'Général') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900"><?= date('d/m/Y', strtotime($row['completed_at'])) ?></span>
                                            <span class="text-xs text-gray-400">à <?= date('H:i', strtotime($row['completed_at'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-middle">
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-bold <?= $is_success ? 'text-green-600' : 'text-red-600' ?>">
                                                <?= $score ?> / <?= $total ?>
                                            </span>
                                            <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-2">
                                                <div class="h-1.5 rounded-full <?= $is_success ? 'bg-green-500' : 'bg-red-500' ?>" style="width: <?= $percent ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if ($is_success): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Réussi
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Échoué
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Aucun résultat</h3>
                    <p class="text-gray-500 mt-1">Vous n'avez pas encore passé de quiz.</p>
                    <a href="categories.php" class="mt-4 inline-block text-indigo-600 font-medium hover:text-indigo-500">
                        Commencer un quiz &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>