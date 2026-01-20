<nav class="bg-gray-800 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-1">
                <span class="text-xl font-bold text-gray-100"><a href="/">Админка</a></span>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Кнопки для гостей (неавторизованных) -->
                <div id="guestButtons" class="flex items-center space-x-4">
                    <a href="/user/login" class="text-gray-300 hover:text-white transition duration-200 font-medium px-3 py-2 rounded hover:bg-gray-700">
                        Вход
                    </a>
                    <a href="/user/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                        Регистрация
                    </a>
                </div>

                <!-- Кнопки для авторизованных пользователей -->
                <div id="userButtons" class="hidden flex items-center space-x-4">
                    <a href="/user/profile" class="text-gray-300 hover:text-white transition duration-200 font-medium px-3 py-2 rounded hover:bg-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Профиль</span>
                    </a>
                    <button onclick="logout()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200 font-medium flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Выйти</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Функция для проверки авторизации
    function checkAuth() {
        const token = localStorage.getItem('auth_token');
        const user = localStorage.getItem('user');
        return token && user;
    }

    // Функция для обновления навигации
    function updateNavigation() {
        const guestButtons = document.getElementById('guestButtons');
        const userButtons = document.getElementById('userButtons');

        if (checkAuth()) {
            // Пользователь авторизован
            if (guestButtons) guestButtons.classList.add('hidden');
            if (userButtons) userButtons.classList.remove('hidden');
        } else {
            // Пользователь не авторизован
            if (guestButtons) guestButtons.classList.remove('hidden');
            if (userButtons) userButtons.classList.add('hidden');
        }
    }

    // Обновляем навигацию при загрузке страницы
    window.addEventListener('load', function() {
        updateNavigation();

        // Также проверяем каждые 5 секунд на случай изменения состояния
        setInterval(updateNavigation, 5000);
    });

    // Функция выхода
    async function logout() {
        const token = localStorage.getItem('auth_token');

        try {
            const response = await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            // Независимо от ответа сервера очищаем localStorage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');

            // Обновляем навигацию
            updateNavigation();

            // Перенаправляем на главную
            window.location.href = '/';

        } catch (error) {
            console.error('Logout error:', error);
            // Все равно очищаем и перенаправляем
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            updateNavigation();
            window.location.href = '/';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateNavigation();
    });
</script>
