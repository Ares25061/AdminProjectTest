<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">

@include('layouts.nav')

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Индикатор загрузки -->
    <div id="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        <p class="text-gray-600 mt-4">Загрузка данных профиля...</p>
    </div>

    <!-- Основной контент -->
    <div id="profileContent" class="hidden"></div>

    <!-- Ошибка -->
    <div id="errorContent" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        Ошибка загрузки профиля. <a href="/user/login" class="underline">Войдите</a> заново.
    </div>
</div>

<!-- Модальное окно загрузки аватара -->
<div id="avatarModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">Загрузка аватара</h3>
            <div class="mb-4">
                <div class="flex justify-center mb-4">
                    <img id="avatarPreview" src="" alt="Предпросмотр" class="hidden w-32 h-32 rounded-full object-cover border-2 border-gray-300">
                </div>
                <form id="avatarUploadForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Выберите изображение</label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAvatarModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Отмена</button>
                        <button type="submit" id="uploadAvatarBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Загрузить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Глобальная переменная для хранения ID просматриваемого пользователя
    let targetUserId = null;

    /**
     * API СЕРВИС: Автоматически обновляет JWT при ошибке 401
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
            date.setHours(date.getHours() + 3); // UTC+3
            return date.toLocaleDateString('ru-RU', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        } catch (e) { return 'Ошибка даты'; }
    }

    function getRoleInfo(roleName) {
        const roles = {
            'admin': { text: 'Администратор', color: 'purple', bgColor: 'purple-100', textColor: 'purple-800', icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>` },
            'moder': { text: 'Модератор', color: 'blue', bgColor: 'blue-100', textColor: 'blue-800', icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>` },
            'user':  { text: 'Пользователь', color: 'green', bgColor: 'green-100', textColor: 'green-800', icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>` }
        };
        return roles[roleName] || roles['user'];
    }

    function getBanStatus(bans) {
        if (!bans || !Array.isArray(bans) || bans.length === 0) return { isBanned: false, text: 'Активен', color: 'green', icon: '✅' };

        const now = new Date();
        now.setHours(now.getHours() + 3);

        const activeBan = bans.find(ban => {
            if (ban.expiration === null) return true;
            return new Date(ban.expiration) > now;
        });

        if (!activeBan) return { isBanned: false, text: 'Активен (был забанен)', color: 'blue', icon: 'ℹ️' };

        return {
            isBanned: true,
            text: activeBan.expiration === null ? 'Забанен (перм.)' : 'Забанен (врем.)',
            color: 'red',
            reason: activeBan.reason,
            expiration: activeBan.expiration
        };
    }

    async function loadProfile() {
        const loggedUser = getLoggedUser();
        const urlParams = new URLSearchParams(window.location.search);
        targetUserId = urlParams.get('id') || loggedUser.id;

        try {
            const response = await apiService(`/api/user/${targetUserId}?with=role`);
            if (response.ok) {
                const data = await response.json();
                // Обновляем localStorage только если смотрим свой профиль
                if (data.user.id === loggedUser.id) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                }
                displayProfile(data.user);
            } else { showError(); }
        } catch (error) { showError(); }
    }

    function displayProfile(user) {
        document.getElementById('loading').classList.add('hidden');
        const profileContent = document.getElementById('profileContent');
        profileContent.classList.remove('hidden');

        const loggedUser = getLoggedUser();
        const isOwnProfile = loggedUser.id === user.id;
        const iAmAdminOrModer = loggedUser.role?.name === 'admin' || loggedUser.role?.name === 'moder';

        // Кнопки управления аватаркой видны владельцу ИЛИ админу/модеру
        const canManageAvatar = isOwnProfile || iAmAdminOrModer;

        const banStatus = getBanStatus(user.bans);
        const roleInfo = getRoleInfo(user.role?.name || 'user');
        const initials = user.name ? user.name.charAt(0).toUpperCase() : 'U';
        const avatarUrl = user.avatar ? `/storage/${user.avatar}?t=${new Date().getTime()}` : null;

        let banWarning = '';
        if (banStatus.isBanned) {
            const expireText = banStatus.expiration
                ? `Забанен до: <span class="font-bold">${formatDate(banStatus.expiration)}</span>`
                : '<span class="font-bold">Перманентная блокировка</span>';

            banWarning = `
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg flex items-center shadow-sm">
                    <span class="text-2xl mr-3">🚫</span>
                    <div>
                        <h3 class="font-bold">Этот аккаунт заблокирован</h3>
                        <p class="text-sm">Причина: ${banStatus.reason || 'не указана'}</p>
                        <p class="text-sm mt-1">${expireText}</p>
                    </div>
                </div>
            `;
        }

        let avatarHtml = `
            <div class="relative">
                <div class="w-20 h-20 rounded-full overflow-hidden bg-white border-2 border-white shadow">
                    ${avatarUrl
            ? `<img src="${avatarUrl}" class="w-full h-full object-cover">`
            : `<div class="w-full h-full flex items-center justify-center text-2xl font-bold text-blue-500">${initials}</div>`}
                </div>
                ${canManageAvatar ? `
                    ${user.avatar ? `
                        <button onclick="deleteAvatar()" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow-md z-30">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    ` : ''}
                    <button onclick="openAvatarModal()" class="absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-blue-600 shadow-md z-30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                ` : ''}
            </div>`;

        profileContent.innerHTML = banWarning + `
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="${banStatus.isBanned ? 'bg-gradient-to-r from-red-500 to-red-700' : roleInfo.color === 'purple' ? 'bg-gradient-to-r from-purple-500 to-pink-600' : 'bg-gradient-to-r from-blue-500 to-purple-600'} p-6 text-white">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="flex items-center space-x-4">
                            ${avatarHtml}
                            <div>
                                <h1 class="text-2xl font-bold">${user.name}</h1>
                                <p class="opacity-80">${user.email}</p>
                                <span class="text-sm px-2 py-1 rounded bg-white bg-opacity-20 mt-2 inline-block">${roleInfo.text} (ID: ${user.id})</span>
                            </div>
                        </div>
                        ${isOwnProfile ? `<button onclick="logout()" class="mt-4 md:mt-0 bg-white text-blue-600 px-4 py-2 rounded-lg font-bold shadow hover:bg-gray-100 transition">Выйти</button>` : ''}
                    </div>
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2 text-center md:text-left">Личная информация</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="border p-4 rounded-lg shadow-sm"><p class="text-xs text-gray-500 uppercase">Имя</p><p class="text-lg font-semibold">${user.name}</p></div>
                            <div class="border p-4 rounded-lg shadow-sm"><p class="text-xs text-gray-500 uppercase">Email</p><p class="text-lg font-semibold">${user.email}</p></div>

                            ${canManageAvatar ? `
                                <div class="border border-blue-100 bg-blue-50 p-4 rounded-lg">
                                    <p class="text-xs text-blue-500 uppercase mb-3">Управление аватаром ${!isOwnProfile ? '(как модератор)' : ''}</p>
                                    <div class="flex gap-2">
                                        <button onclick="openAvatarModal()" class="px-3 py-1.5 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition">Загрузить</button>
                                        ${user.avatar ? `<button onclick="deleteAvatar()" class="px-3 py-1.5 bg-red-500 text-white rounded text-sm hover:bg-red-600 transition">Удалить</button>` : ''}
                                    </div>
                                </div>
                            ` : ''}

                            ${banStatus.isBanned && banStatus.expiration ? `
                                <div class="border border-red-200 bg-red-50 p-4 rounded-lg">
                                    <p class="text-xs text-red-500 uppercase">Срок разблокировки</p>
                                    <p class="font-bold text-red-700">${formatDate(banStatus.expiration)}</p>
                                </div>
                            ` : ''}
                        </div>
                        <div class="space-y-4">
                            <div class="border border-${roleInfo.color}-200 bg-${roleInfo.bgColor} p-4 rounded-lg">
                                <p class="text-xs text-gray-400 uppercase">Права доступа</p>
                                <div class="flex items-center gap-2">${roleInfo.icon} <span class="font-bold">${roleInfo.text}</span></div>
                            </div>
                            <div class="border p-4 rounded-lg shadow-sm"><p class="text-xs text-gray-400 uppercase">Дата регистрации</p><p class="font-semibold">${formatDate(user.created_at)}</p></div>
                            <div class="border p-4 rounded-lg shadow-sm"><p class="text-xs text-gray-400 uppercase">Текущий статус</p><p class="font-bold text-${banStatus.isBanned ? 'red' : 'green'}-600">${banStatus.text}</p></div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t flex flex-wrap gap-4 justify-center md:justify-start">
                        ${isOwnProfile ? `<a href="/user/edit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">Редактировать профиль</a>` : ''}
                        ${iAmAdminOrModer ? `<a href="/admin/dashboard" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-black transition font-medium">Панель управления</a>` : ''}
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                 <div class="bg-white p-4 rounded-lg shadow border text-center">
                    <h3 class="text-gray-500 text-sm">Дней в системе</h3>
                    <p class="text-3xl font-bold text-blue-600">${Math.floor((new Date() - new Date(user.created_at)) / (1000 * 60 * 60 * 24))}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow border text-center">
                    <h3 class="text-gray-500 text-sm">Верификация</h3>
                    <p class="text-lg font-bold ${user.email_verified_at ? 'text-green-500' : 'text-orange-500'}">${user.email_verified_at ? 'Подтвержден' : 'Ожидает'}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow border text-center">
                    <h3 class="text-gray-500 text-sm">ID аккаунта</h3>
                    <p class="text-xl font-bold text-gray-700">#${user.id}</p>
                </div>
            </div>
        `;
    }

    function openAvatarModal() { document.getElementById('avatarModal').classList.remove('hidden'); }
    function closeAvatarModal() { document.getElementById('avatarModal').classList.add('hidden'); }
    function showError() { document.getElementById('loading').classList.add('hidden'); document.getElementById('errorContent').classList.remove('hidden'); }

    async function uploadAvatar() {
        const fileInput = document.getElementById('avatarInput');
        if (!fileInput.files.length) return;
        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);
        // Добавляем ID целевого пользователя
        formData.append('user_id', targetUserId);

        const btn = document.getElementById('uploadAvatarBtn');
        btn.disabled = true; btn.textContent = 'Загрузка...';

        try {
            const res = await apiService('/api/user/avatar/upload', { method: 'POST', body: formData });
            if (res.ok) {
                const data = await res.json();
                loadProfile();
                closeAvatarModal();
            }
        } catch (e) { alert('Ошибка загрузки'); }
        finally { btn.disabled = false; btn.textContent = 'Загрузить'; }
    }

    async function deleteAvatar() {
        if (!confirm('Вы уверены, что хотите удалить аватар?')) return;
        try {
            // Отправляем JSON с user_id согласно DestroyAvatarRequest
            const res = await apiService('/api/user/avatar/destroy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: targetUserId })
            });
            if (res.ok) {
                loadProfile();
                alert('Аватар успешно удален');
            }
        } catch (e) { alert('Ошибка при удалении'); }
    }

    async function logout() {
        try { await apiService('/api/logout', { method: 'POST' }); }
        finally { localStorage.clear(); window.location.href = '/user/login'; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadProfile();
        document.getElementById('avatarUploadForm').addEventListener('submit', e => { e.preventDefault(); uploadAvatar(); });
        document.getElementById('avatarInput').addEventListener('change', function() {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { const p = document.getElementById('avatarPreview'); p.src = e.target.result; p.classList.remove('hidden'); };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
</body>
</html>
