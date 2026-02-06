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
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                        <input type="text" id="name" name="name"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Введите новое имя">
                        <p class="text-sm text-gray-500 mt-1">Оставьте пустым, если не хотите менять</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Введите новый email">
                        <p class="text-sm text-gray-500 mt-1">При изменении потребуется повторная верификация.</p>
                    </div>

                    <!-- Информация (желтый блок) -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.212 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <div>
                                <h3 class="font-medium text-yellow-800">Информация</h3>
                                <ul class="mt-2 text-sm text-yellow-700 space-y-1">
                                    <li>• Можно изменить только имя или только email</li>
                                    <li>• При изменении email статус подтверждения сбросится</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" id="saveBtn"
                                class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 font-medium flex items-center gap-2 flex-1 justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Сохранить изменения
                        </button>
                        <a href="/user/profile" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200 font-medium text-center">Отмена</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Текущая информация -->
        <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 text-center">Текущая информация</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Имя</p><p id="currentName" class="font-semibold text-gray-800"></p></div>
                    <div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Email</p><p id="currentEmail" class="font-semibold text-gray-800"></p></div>
                    <div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Статус</p><p id="currentVerification" class="font-semibold"></p></div>
                    <div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Создан</p><p id="currentCreatedAt" class="font-semibold text-gray-800"></p></div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorContent" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        Ошибка загрузки данных. <a href="/user/profile" class="underline">Вернуться в профиль</a>.
    </div>
</div>

<script>
    /**
     * УМНЫЙ СЕРВИС ЗАПРОСОВ
     */
    async function apiService(url, options = {}) {
        let token = localStorage.getItem('auth_token');
        const headers = {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            ...options.headers
        };

        let response = await fetch(url, { ...options, headers });

        if (response.status === 401) {
            console.warn('Токен истек, обновляю...');
            const refreshRes = await fetch('/api/refresh', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });

            if (refreshRes.ok) {
                const data = await refreshRes.json();
                localStorage.setItem('auth_token', data.authorization.token);
                localStorage.setItem('user', JSON.stringify(data.user));

                headers['Authorization'] = `Bearer ${data.authorization.token}`;
                return await fetch(url, { ...options, headers });
            } else {
                localStorage.clear();
                window.location.href = '/user/login?error=expired';
                return response;
            }
        }
        return response;
    }

    const getLoggedUser = () => JSON.parse(localStorage.getItem('user') || '{}');

    function formatDate(dateString) {
        if (!dateString) return 'Не указано';
        try {
            const date = new Date(dateString);
            date.setHours(date.getHours() + 3);
            return date.toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch (e) { return 'Ошибка даты'; }
    }

    function showMessage(type, message) {
        const errorDiv = document.getElementById('errorMessage');
        const successDiv = document.getElementById('successMessage');
        if (type === 'error') {
            errorDiv.textContent = message; errorDiv.classList.remove('hidden'); successDiv.classList.add('hidden');
        } else {
            successDiv.textContent = message; successDiv.classList.remove('hidden'); errorDiv.classList.add('hidden');
        }
    }

    async function loadUserData() {
        if (!localStorage.getItem('auth_token')) { window.location.href = '/user/login'; return; }

        const user = getLoggedUser();
        try {
            const response = await apiService(`/api/user/${user.id}`);
            if (response.ok) {
                const data = await response.json();
                localStorage.setItem('user', JSON.stringify(data.user));
                displayEditForm(data.user);
            } else {
                showError();
            }
        } catch (error) {
            if (user.id) displayEditForm(user); else showError();
        }
    }

    function displayEditForm(user) {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('editFormContainer').classList.remove('hidden');

        document.getElementById('name').placeholder = `Текущее: ${user.name || ''}`;
        document.getElementById('email').placeholder = `Текущий: ${user.email || ''}`;
        document.getElementById('currentName').textContent = user.name || '—';
        document.getElementById('currentEmail').textContent = user.email || '—';
        document.getElementById('currentCreatedAt').textContent = formatDate(user.created_at);

        const ver = document.getElementById('currentVerification');
        ver.textContent = user.email_verified_at ? 'Подтверждён' : 'Не подтверждён';
        ver.className = user.email_verified_at ? 'font-semibold text-green-600' : 'font-semibold text-red-600';
    }

    function showError() {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('errorContent').classList.remove('hidden');
    }

    // ОТПРАВКА ФОРМЫ
    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {};
        const nameInput = document.getElementById('name').value.trim();
        const emailInput = document.getElementById('email').value.trim().toLowerCase();

        if (nameInput) formData.name = nameInput;
        if (emailInput) formData.email = emailInput;

        if (Object.keys(formData).length === 0) {
            showMessage('error', 'Заполните хотя бы одно поле');
            return;
        }

        const btn = document.getElementById('saveBtn');
        btn.disabled = true; btn.textContent = 'Сохранение...';

        try {
            const response = await apiService('/api/user/edit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok) {
                const oldUser = getLoggedUser();
                localStorage.setItem('user', JSON.stringify(data.user));

                const emailChanged = formData.email && oldUser.email !== data.user.email;
                showMessage('success', emailChanged ? 'Профиль обновлен! Требуется повторная верификация email.' : 'Изменения сохранены!');

                // Сбрасываем форму и обновляем UI
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';
                displayEditForm(data.user);

                if (emailChanged) confirm('Email изменён. Отправить письмо для подтверждения?') && sendVerificationEmail();

                setTimeout(() => document.getElementById('successMessage').classList.add('hidden'), 4000);
            } else {
                const msg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Ошибка');
                showMessage('error', msg);
            }
        } catch (error) {
            showMessage('error', 'Ошибка соединения');
        } finally {
            btn.disabled = false; btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Сохранить изменения`;
        }
    });

    async function sendVerificationEmail() {
        const user = getLoggedUser();
        alert('Письмо отправлено на ' + user.email);
        // Здесь можно вызвать ваш API для отправки письма
    }

    window.addEventListener('load', loadUserData);
</script>
</body>
</html>
