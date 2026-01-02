<?php
require_once '../../config/database.php';
require_once '../../classes/Security.php';
include '../partials/header.php';
require_once '../partials/nav_student.php';

Security::requireStudent();

// Nchddou category_id mn URL
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id === 0) {
    // Ila makanch ID, rj3ou l categories
    echo "<script>window.location.href='categories.php';</script>";
    exit();
}
?>

<div class="container mx-auto p-8">
    <input type="hidden" id="categoryId" value="<?php echo $category_id; ?>">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Liste des Quiz</h2>
        <a href="categories.php" class="text-blue-600 hover:text-blue-800 flex items-center font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour aux catégories
        </a>
    </div>

    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>

    <div id="loader" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-3 text-gray-600 font-medium">Chargement des quiz...</p>
    </div>

    <div id="quizGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
    </div>

    <div id="noQuiz" class="hidden text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-500 text-lg">Aucun quiz disponible pour le moment.</p>
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

                // HNA FIN BEDDELNA DESIGN BACH YNASSEQ M3A CATEGORIES
                quizGrid.innerHTML = result.data.map(quiz => `
                <a href="take_quiz.php?id=${quiz.id}" 
                   class="group block bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-500 hover:shadow-md transition-all duration-200 h-full flex flex-col cursor-pointer">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-clipboard-question text-lg"></i>
                        </div>
                        
                        <span class="bg-indigo-50 text-indigo-600 text-xs font-medium px-2.5 py-1 rounded-md border border-indigo-100">
                            Examen
                        </span>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                            ${escapeHtml(quiz.titre)}
                        </h3>
                        
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">
                            ${escapeHtml(quiz.description || 'Aucune description disponible pour ce quiz.')}
                        </p>
                    </div>

                    <div class="mt-4 flex items-center text-sm font-medium text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        Commencer le test
                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </div>
                </a>
                `).join('');
            } else {
                noQuiz.classList.remove('hidden');
            }
        } catch (error) {
            console.error(error);
            loader.classList.add('hidden');
            errorMsg.textContent = "Impossible de charger les quiz. Veuillez réessayer.";
            errorMsg.classList.remove('hidden');
        }
    });

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }
</script>

<?php require_once '../partials/footer.php'; ?>