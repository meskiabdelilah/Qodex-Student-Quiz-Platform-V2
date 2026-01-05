<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';
require_once '../../classes/Quiz.php';
include '../partials/header.php';
require_once '../partials/nav_student.php';

Security::requireStudent();
?>

<div class="container mx-auto px-4 py-8 pt-24 min-h-screen bg-gray-50">
    
    <div class="mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-3xl font-bold text-gray-800">
            <span class="text-blue-600">Catégories</span> Disponibles
        </h2>
        <p class="text-gray-500 mt-2">Choisissez un domaine pour commencer vos tests.</p>
    </div>

    <?php if (isset($_SESSION['category_success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm mb-6 flex items-center animate-fade-in-down">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <?php
            echo htmlspecialchars($_SESSION['category_success']);
            unset($_SESSION['category_success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['category_error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm mb-6 flex items-center animate-fade-in-down">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <?php
            echo htmlspecialchars($_SESSION['category_error']);
            unset($_SESSION['category_error']);
            ?>
        </div>
    <?php endif; ?>

    <div id="loader" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Chargement du catalogue...</p>
    </div>

    <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-600 px-6 py-4 rounded-xl mb-6 text-center">
        <p class="font-bold mb-1">Oups !</p>
        <p id="errorText"></p>
    </div>

    <div id="noCategories" class="hidden flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
        <div class="bg-gray-50 p-4 rounded-full mb-4">
            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
        </div>
        <h3 class="text-xl font-medium text-gray-900">Aucune catégorie</h3>
        <p class="mt-1 text-gray-500">Revenez plus tard pour de nouveaux quiz.</p>
    </div>

    <div id="categoriesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
        </div>
</div>

<script>
    // Nafs l-script dyalk, ma bddelt fih walo f logic
    async function loadCategories() {
        const loader = document.getElementById('loader');
        const noCategories = document.getElementById('noCategories');
        const categoriesGrid = document.getElementById('categoriesGrid');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        try {
            const response = await fetch('../../actions/action_student/category.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erreur lors du chargement');
            }

            const result = await response.json();
            loader.classList.add('hidden');

            if (result.success && result.data.length > 0) {
                categoriesGrid.classList.remove('hidden');

                categoriesGrid.innerHTML = result.data.map(category => `
                <a href="quiz.php?category_id=${category.id}" 
                   class="group flex flex-col h-full bg-white rounded-xl border border-gray-200 p-6 hover:border-blue-400 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>

                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center shadow-md">
                             <i class="fas ${category.icon || 'fa-layer-group'} text-lg"></i>
                        </div>
                        
                        <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1 rounded-full border border-blue-100">
                            ${category.quiz_count} Quiz
                        </span>
                    </div>

                    <div class="flex-1 relative z-10">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                            ${escapeHtml(category.nom)}
                        </h3>
                        
                        <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">
                            ${escapeHtml(category.description || 'Aucune description disponible pour cette catégorie.')}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center text-sm font-semibold text-blue-600 group-hover:translate-x-1 transition-transform duration-200">
                        Explorer les quiz
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </div>
                </a>
                `).join('');

            } else {
                noCategories.classList.remove('hidden');
            }

        } catch (error) {
            console.error('Erreur:', error);
            loader.classList.add('hidden');
            errorText.textContent = "Impossible de charger les catégories.";
            errorMessage.classList.remove('hidden');
        }
    }

    function escapeHtml(text) {
        return text ? text.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])) : '';
    }

    document.addEventListener('DOMContentLoaded', loadCategories);
</script>

<?php require_once '../partials/footer.php'; ?>