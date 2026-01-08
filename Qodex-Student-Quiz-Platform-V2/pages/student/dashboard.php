    <?php

    require_once '../../config/database.php';
    require_once '../../classes/Database.php';
    require_once '../../classes/Security.php';
    require_once '../../classes/Category.php';
    require_once '../../classes/Quiz.php';
    include '../partials/header.php';
    require_once '../partials/nav_student.php';

    Security::requireStudent();
    $user_id = $_SESSION['user_id'];
    $user_nom = $_SESSION['user_nom'] ?? 'Étudiant';
    $db = Database::getInstance();

    $query1 = "SELECT COUNT(*) as total_passes FROM results WHERE etudiant_id = ?";
    $stmt1 = $db->query($query1, [$user_id]);
    $data1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    $quiz_passes = $data1['total_passes'] ?? 0;

    $query2 = " SELECT 
                COUNT(*) as total_passes,
                COALESCE(AVG((score / total_questions) * 100), 0) as moyenne
               FROM results 
               WHERE etudiant_id = ?";
    $stmt2 = $db->query($query2, [$user_id]);
    $data2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $moyenne = $data2['moyenne'] ? number_format($data2['moyenne'], 1) : 0;

    $query3 = " SELECT COALESCE(MAX((score / total_questions) * 100), 0) as top_score 
          FROM results 
          WHERE etudiant_id = ?";
    $stmt3 = $db->query($query3, [$user_id]);
    $data3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    $top_score = number_format($data3['top_score'], 1);

    $query4 = " SELECT 
                        q.id, 
                        q.titre, 
                        q.description, 
                        c.nom as categorie_nom, 
                        u.nom as enseignant_nom 
                    FROM quiz q
                    JOIN categories c ON q.categorie_id = c.id
                    JOIN users u ON q.enseignant_id = u.id
                    WHERE q.is_active = 1 
                    AND q.id NOT IN (
                        SELECT quiz_id FROM results WHERE etudiant_id = ?
                    )
                    ORDER BY q.created_at DESC";
    $stmt4 = $db->query($query4, [$user_id]);
    $data4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    $quiz_disponibles = count($data4);


    ?>

    <div class="min-h-screen bg-gray-50 pt-24 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto space-y-8">

            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Bonjour, <span class="text-indigo-600"><?= htmlspecialchars($user_nom) ?></span> 👋
                    </h1>
                    <p class="text-gray-500 mt-2 text-lg">Prêt à relever de nouveaux défis aujourd'hui ?</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="categories.php" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition-all transform hover:-translate-y-0.5">
                        Commencer un Quiz
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Quiz Passés</h3>
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-extrabold text-gray-900"><?= $quiz_passes ?></span>
                        <span class="ml-2 text-sm text-gray-400">terminés</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Moyenne</h3>
                        <div class="p-3 bg-yellow-50 rounded-full text-yellow-600">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-extrabold text-gray-900"><?= $moyenne ?>%</span>
                        <span class="ml-2 text-sm text-gray-400">de réussite</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Top Score</h3>
                        <div class="p-3 bg-green-50 rounded-full text-green-600">
                            <i class="fas fa-trophy text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-extrabold text-gray-900"><?= $top_score ?>%</span>
                        <span class="ml-2 text-sm text-gray-400">meilleur perf.</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Disponibles</h3>
                        <div class="p-3 bg-purple-50 rounded-full text-purple-600">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                    </div>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-extrabold text-gray-900"><?= $quiz_disponibles ?></span>
                        <span class="ml-2 text-sm text-gray-400">à découvrir</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <?php require_once '../partials/footer.php'; ?>