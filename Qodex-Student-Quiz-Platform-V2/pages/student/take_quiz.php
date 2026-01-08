<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
include '../partials/header.php'; 
require_once '../partials/nav_student.php';

Security::requireStudent();
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$db = Database::getInstance();
$check = $db->query("SELECT id FROM results WHERE quiz_id = ? AND etudiant_id = ?", [$quiz_id, $_SESSION['user_id']]);

if ($check->rowCount() > 0) {
    header("Location: mes_resultats.php?error=already_taken");
    exit();
}
?>

<div class="container mx-auto px-4 py-8 pt-24 min-h-screen bg-gray-50">

    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">
                <span class="text-blue-600">Quiz #<?php echo $quiz_id; ?></span>
            </h2>
            <p class="text-gray-500 mt-1">Cochez la bonne réponse pour chaque question.</p>
        </div>

        <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg font-mono font-bold">
            <i class="far fa-clock mr-2"></i> Examen en cours
        </div>
    </div>

    <div id="loader" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600">Chargement du quiz...</p>
    </div>

    <div id="errorMessage" class="hidden bg-red-100 text-red-700 p-4 rounded mb-6"></div>

    <form id="quizForm" class="hidden max-w-4xl mx-auto space-y-8">

        <input type="hidden" name="quiz_id" id="quizId" value="<?php echo $quiz_id; ?>">

        <div id="questionGrid" class="grid grid-cols-1 gap-8"></div>

        <div class="sticky bottom-4 z-10">
            <div class="bg-white/90 backdrop-blur p-4 rounded-xl shadow-lg border border-gray-200 flex justify-end">
                <button type="button" onclick="submitQuiz()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-all flex items-center">
                    Soumettre mes réponses
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>

    <div id="noQuestion" class="hidden text-center py-12">
        <p class="text-gray-500">Aucune question trouvée.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const quizId = document.getElementById('quizId').value;
        const loader = document.getElementById('loader');
        const quizForm = document.getElementById('quizForm');
        const questionGrid = document.getElementById('questionGrid');

        try {
            const response = await fetch(`../../actions/action_student/take_quiz.php?quiz_id=${quizId}`);
            const result = await response.json();

            loader.classList.add('hidden');

            if (result.success && result.data.length > 0) {
                quizForm.classList.remove('hidden');

                questionGrid.innerHTML = result.data.map((q, index) => `
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex gap-4">
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold flex-shrink-0">${index + 1}</span>
                            <h3 class="text-lg font-medium text-gray-800 pt-1">${escapeHtml(q.question)}</h3>
                        </div>
                        
                        <div class="p-6 space-y-3">
                            ${renderOption(q.id, q.option1, 'A')}
                            ${renderOption(q.id, q.option2, 'B')}
                            ${renderOption(q.id, q.option3, 'C')}
                            ${renderOption(q.id, q.option4, 'D')}
                        </div>
                    </div>
                `).join('');

            } else if (result.error === 'already_taken') {
                window.location.href = 'mes_resultats.php?error=already_taken';

            } else {
                document.getElementById('noQuestion').classList.remove('hidden');
            }
        } catch (error) {
            console.error(error);
            loader.classList.add('hidden');
        }
    });

    function renderOption(questionId, text, letter) {
        if (!text) return '';
        return `
            <label class="flex items-center p-4 rounded-lg border border-gray-200 cursor-pointer hover:bg-blue-50 hover:border-blue-400 transition-all group">
                <input type="radio" name="answers[${questionId}]" value="${escapeHtml(text)}" class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-gray-300">
                <span class="ml-3 text-gray-700 font-medium group-hover:text-blue-700">${escapeHtml(text)}</span>
            </label>
        `;
    }

    function escapeHtml(text) {
        return text ? text.replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            '\'': '&#039;'
        } [m])) : '';
    }

    async function submitQuiz() {
        if (!confirm("Êtes-vous sûr de vouloir soumettre le quiz ?")) return;

        const form = document.getElementById('quizForm');
        const formData = new FormData(form);

        form.action = "../../actions/action_student/submit_quiz.php";
        form.method = "POST";
        form.submit();
    }
</script>

<?php require_once '../partials/footer.php'; ?>