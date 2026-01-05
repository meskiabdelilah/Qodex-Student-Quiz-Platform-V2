<?php
require_once '../../config/database.php';
require_once '../../classes/Security.php';
include '../partials/header.php';
require_once '../partials/nav_student.php';

Security::requireStudent();

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id === 0) {
    echo "<script>window.location.href='categories.php';</script>";
    exit();
}
?>

<div class="container mx-auto px-4 py-8 pt-24 min-h-screen bg-gray-50">
    <input type="hidden" id="categoryId" value="<?php echo $category_id; ?>">

    <div class="flex flex-col md:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-200 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">
                Liste des <span class="text-blue-600">Quiz</span>
            </h2>
            <p class="text-gray-500 mt-1">Sélectionnez un quiz pour commencer l'examen.</p>
        </div>

        <a href="categories.php" class="group inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour aux catégories
        </a>
    </div>

    <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-center"></div>

    <div id="loader" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Recherche des quiz...</p>
    </div>

    <div id="quizGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
        </div>

    <div id="noQuiz" class="hidden flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
        <div class="bg-gray-50 p-4 rounded-full mb-4">
            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-gray-500 text-lg font-medium">Aucun quiz disponible dans cette catégorie.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const categoryId = document.getElementById('categoryId').value;
        const quizGrid = document.getElementById('quizGrid');
        const loader = document.getElementById('loader');
        const noQuiz = document.getElementById('noQuiz');
        const errorMsg = document.getElementById('errorMessage');

        try {
            const response = await fetch(`../../actions/action_student/quiz.php?category_id=${categoryId}`);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erreur lors du chargement');
            }

            const result = await response.json();
            loader.classList.add('hidden');

            if (result.success && result.data.length > 0) {
                quizGrid.classList.remove('hidden');

                // DESIGN: Nafs style dyal Categories bach yban site professionnel
                quizGrid.innerHTML = result.data.map(quiz => `
                <a href="take_quiz.php?quiz_id=${quiz.id}" 
                   class="group flex flex-col bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-400 hover:shadow-lg transition-all duration-300 relative overflow-hidden h-full">
                    
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-indigo-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>

                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-lg"></i>
                        </div>
                        
                        <span class="bg-green-50 text-green-700 text-xs font-bold px-2.5 py-1 rounded-md border border-green-100 uppercase tracking-wide">
                            Disponible
                        </span>
                    </div>

                    <div class="flex-1 relative z-10">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors leading-tight">
                            ${escapeHtml(quiz.titre)}
                        </h3>
                        
                        <p class="text-gray-500 text-sm line-clamp-2">
                            ${escapeHtml(quiz.description || 'Testez vos connaissances sur ce sujet.')}
                        </p>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                         <span class="text-xs text-gray-400 font-medium">Durée: Standard</span>

                        <div class="flex items-center text-sm font-semibold text-blue-600 group-hover:translate-x-1 transition-transform">
                            Commencer
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </div>
                    </div>
                </a>
                `).join('');
            } else {
                noQuiz.classList.remove('hidden');
            }
        } catch (error) {
            console.error(error);
            loader.classList.add('hidden');
            errorMsg.textContent = "Impossible de charger la liste des quiz.";
            errorMsg.classList.remove('hidden');
        }
    });

    function escapeHtml(text) {
        return text ? text.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])) : '';
    }
</script>

<?php require_once '../partials/footer.php'; ?>