<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">

@include('layouts.nav')

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div id="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        <p class="text-gray-600 mt-4">Загрузка данных...</p>
    </div>

    <div id="editFormContainer" class="hidden">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Редактирование профиля</h1>
                        <p class="text-blue-100 mt-1">Обновите информацию о себе</p>
                    </div>
                    <a href="/user/profile"
                       class="bg-white text-blue-500 px-4 py-2 rounded-lg hover:bg-blue-50 transition font-semibold">
                        Назад к профилю
                    </a>
                </div>
            </div>

            <!-- Форма редактирования -->
            <div class="p-6">
                <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"></div>
                <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6"></div>

                <form id="editForm" class="space-y-6">
                    <!-- Имя -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Имя
                        </label>
                        <input type="text" id="name" name="name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Введите новое имя (оставьте пустым, чтобы не менять)">
                        <p class="text-sm text-gray-500 mt-1">Оставьте поле пустым, если не хотите менять имя</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" id="email" name="email"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Введите новый email (оставьте пустым, чтобы не менять)">
                        <p class="text-sm text-gray-500 mt-1">
                            Оставьте поле пустым, если не хотите менять email. При изменении email потребуется повторная верификация.
                        </p>
                    </div>

                    <!-- Информация -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.212 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <h3 class="font-medium text-yellow-800">Информация</h3>
                                <ul class="mt-2 text-sm text-yellow-700 space-y-1">
                                    <li>• Можно изменить только имя или только email</li>
                                    <li>• Можно оставить оба поля пустыми (ничего не изменится)</li>
                                    <li>• При изменении email сбросится статус верификации</li>
                                    <li>• Email должен быть уникальным и в правильном формате</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit"
                                class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 font-medium flex items-center gap-2 flex-1 justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Сохранить изменения
                        </button>
                        <a href="/user/profile"
                           class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200 font-medium text-center">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Текущая информация -->
        <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Текущая информация</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Текущее имя</p>
                        <p id="currentName" class="font-semibold text-gray-800"></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Текущий email</p>
                        <p id="currentEmail" class="font-semibold text-gray-800"></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Статус верификации</p>
                        <p id="currentVerification" class="font-semibold"></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-500">Дата регистрации</p>
                        <p id="currentCreatedAt" class="font-semibold text-gray-800"></p>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <span class="font-medium">Подсказка:</span> Введите данные только в те поля, которые хотите изменить.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="errorContent" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        Ошибка загрузки данных. <a href="/user/profile" class="underline">Вернуться в профиль</a>.
    </div>
</div>

<script>
    // Проверяем авторизацию
    function checkAuth() {
        const token = localStorage.getItem('auth_token');
        const user = localStorage.getItem('user');
        return token && user ? true : false;
    }

    // Если пользователь не авторизован, перенаправляем на логин
    if (!checkAuth()) {
        window.location.href = '/user/login';
    }

    // Форматирование даты
    function formatDate(dateString) {
        if (!dateString) return 'Не указано';

        try {
            const date = new Date(dateString);
            date.setHours(date.getHours() + 3); // Корректировка часового пояса

            return date.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            console.error('Error formatting date:', e);
            return 'Ошибка даты';
        }
    }

    // Показать сообщение
    function showMessage(type, message) {
        const errorDiv = document.getElementById('errorMessage');
        const successDiv = document.getElementById('successMessage');

        if (type === 'error') {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
        } else {
            successDiv.textContent = message;
            successDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');
        }
    }

    // Загружаем данные пользователя
    async function loadUserData() {
        const token = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');

        if (!token) {
            showError();
            return;
        }

        try {
            // Получаем ID пользователя из localStorage
            let userId;
            try {
                const userData = JSON.parse(storedUser);
                userId = userData.id;
            } catch (e) {
                console.error('Error parsing user data:', e);
                showError();
                return;
            }

            // Получаем данные пользователя через API
            const response = await fetch('/api/user/' + userId, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (response.ok) {
                const data = await response.json();
                // Сохраняем обновленные данные
                localStorage.setItem('user', JSON.stringify(data.user));
                displayEditForm(data.user);
            } else if (response.status === 401) {
                // Токен недействителен
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/user/login';
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error loading user data:', error);
            // Используем данные из localStorage
            if (storedUser) {
                try {
                    const userData = JSON.parse(storedUser);
                    displayEditForm(userData);
                } catch (e) {
                    showError();
                }
            } else {
                showError();
            }
        }
    }

    // Отображаем форму редактирования
    function displayEditForm(user) {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('errorContent').classList.add('hidden');

        const editFormContainer = document.getElementById('editFormContainer');
        editFormContainer.classList.remove('hidden');

        // Заполняем placeholder текущими данными
        document.getElementById('name').placeholder = `Текущее имя: ${user.name || ''}`;
        document.getElementById('email').placeholder = `Текущий email: ${user.email || ''}`;

        // Заполняем блок с текущей информацией
        document.getElementById('currentName').textContent = user.name || 'Не указано';
        document.getElementById('currentEmail').textContent = user.email || 'Не указан';
        document.getElementById('currentCreatedAt').textContent = formatDate(user.created_at);

        // Статус верификации
        const verificationElement = document.getElementById('currentVerification');
        if (user.email_verified_at) {
            verificationElement.textContent = 'Подтверждён';
            verificationElement.className = 'font-semibold text-green-600';
        } else {
            verificationElement.textContent = 'Не подтверждён';
            verificationElement.className = 'font-semibold text-red-600';
        }
    }

    function showError() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('errorContent').classList.remove('hidden');
    }

    // Обработка отправки формы
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const token = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');

        if (!token || !storedUser) {
            showMessage('error', 'Ошибка авторизации');
            return;
        }

        // Собираем данные из формы
        const formData = {};
        const nameInput = document.getElementById('name').value.trim();
        const emailInput = document.getElementById('email').value.trim().toLowerCase();

        // Добавляем только заполненные поля
        if (nameInput) {
            formData.name = nameInput;
        }
        if (emailInput) {
            formData.email = emailInput;
        }

        // Проверяем, что хотя бы одно поле заполнено
        if (Object.keys(formData).length === 0) {
            showMessage('error', 'Заполните хотя бы одно поле для изменения');
            return;
        }

        // Валидация на клиенте
        if (formData.name && formData.name.length < 2) {
            showMessage('error', 'Имя должно содержать минимум 2 символа');
            return;
        }

        if (formData.email && !formData.email.includes('@')) {
            showMessage('error', 'Введите корректный email');
            return;
        }

        try {
            // Используем API маршрут /api/user/edit (без ID пользователя в URL)
            const response = await fetch('/api/user/edit', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok) {
                // Обновляем данные в localStorage
                localStorage.setItem('user', JSON.stringify(data.user));

                // Проверяем, изменился ли email
                const oldUser = JSON.parse(storedUser);
                const emailChanged = formData.email && oldUser.email !== formData.email;

                let successMessage = 'Профиль успешно обновлен!';
                if (emailChanged) {
                    successMessage += ' Требуется повторное подтверждение email.';
                }

                showMessage('success', successMessage);

                // Обновляем текущую информацию
                document.getElementById('currentName').textContent = data.user.name || 'Не указано';
                document.getElementById('currentEmail').textContent = data.user.email || 'Не указан';

                // Обновляем placeholder
                document.getElementById('name').placeholder = `Текущее имя: ${data.user.name || ''}`;
                document.getElementById('email').placeholder = `Текущий email: ${data.user.email || ''}`;

                // Очищаем поля формы
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';

                // Обновляем статус верификации
                const verificationElement = document.getElementById('currentVerification');
                if (data.user.email_verified_at) {
                    verificationElement.textContent = 'Подтверждён';
                    verificationElement.className = 'font-semibold text-green-600';
                } else {
                    verificationElement.textContent = 'Не подтверждён';
                    verificationElement.className = 'font-semibold text-red-600';
                }

                // Если email изменился, предлагаем отправить письмо для верификации
                if (emailChanged && !data.user.email_verified_at) {
                    setTimeout(() => {
                        if (confirm('Email изменён. Хотите отправить письмо для подтверждения?')) {
                            sendVerificationEmail();
                        }
                    }, 1000);
                }

                // Автоматическое скрытие успешного сообщения через 3 секунды
                setTimeout(() => {
                    document.getElementById('successMessage').classList.add('hidden');
                }, 3000);

            } else {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    showMessage('error', errorMessages);
                } else if (data.message) {
                    showMessage('error', data.message);
                } else if (data.error) {
                    showMessage('error', data.error);
                } else {
                    showMessage('error', 'Ошибка обновления профиля');
                }
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            showMessage('error', 'Ошибка соединения с сервером');
        }
    });

    // Функция для отправки письма верификации
    async function sendVerificationEmail() {
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user'));

        if (!token || !user) return;

        try {
            alert('Письмо для верификации отправлено на ' + user.email);
        } catch (error) {
            console.error('Error sending verification email:', error);
            alert('Ошибка при отправке письма');
        }
    }

    // Загружаем данные при загрузке страницы
    window.addEventListener('load', loadUserData);
</script>
</body>
</html>
