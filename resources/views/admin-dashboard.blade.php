<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления - Администратор</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">

@include('layouts.nav')

<div class="container mx-auto px-4 py-8">
    <div id="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        <p class="text-gray-600 mt-4">Загрузка данных...</p>
    </div>

    <div id="adminContent" class="hidden"></div>

    <div id="errorContent" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        Ошибка загрузки данных. <a href="/user/login" class="underline">Войдите</a> заново.
    </div>
</div>

<!-- Модалки (БАН, РЕДАКТИРОВАНИЕ, РОЛЬ) - Оставлены без изменений дизайна -->
<!-- [Здесь ваш код модалок из исходного файла...] -->
<div id="banModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Бан пользователя</h3>
        <form id="banForm">
            <input type="hidden" id="banUserId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Причина</label>
                <textarea id="banReason" rows="3" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Укажите причину..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Срок бана</label>
                <div class="space-y-2">
                    <label class="flex items-center"><input type="radio" name="expiration_type" value="permanent" checked class="h-4 w-4 text-red-600"><span class="ml-2 text-sm">Перманентный</span></label>
                    <label class="flex items-center"><input type="radio" name="expiration_type" value="temporary" class="h-4 w-4 text-red-600"><span class="ml-2 text-sm">Временный</span></label>
                    <div id="temporaryOptions" class="hidden ml-6">
                        <label class="block text-xs text-gray-600 mb-1">Дней</label>
                        <input type="number" id="banDays" min="1" max="365" value="7" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeBanModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Забанить</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Редактирование пользователя</h3>
        <form id="editForm">
            <input type="hidden" id="editUserId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
                <input type="text" id="editName" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="editEmail" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Новый пароль (пусто, если не меняете)</label>
                <input type="password" id="editPassword" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div id="roleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Изменение роли</h3>
        <form id="roleForm">
            <input type="hidden" id="roleUserId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Роль</label>
                <select id="userRole" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="user">Пользователь</option>
                    <option value="moder">Модератор</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeRoleModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPage = 1;
    let perPage = 10;
    let totalUsers = 0;
    let totalPages = 0;
    let currentUserId = null;
    let usersData = {};

    // --- УМНЫЙ ОБРАБОТЧИК ЗАПРОСОВ (apiService) ---
    async function apiRequest(url, method = 'GET', body = null) {
        let token = localStorage.getItem('auth_token');

        const headers = {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
        if (body) headers['Content-Type'] = 'application/json';

        let options = { method, headers, body: body ? JSON.stringify(body) : null };

        try {
            let response = await fetch(url, options);

            // Если токен просрочен (401)
            if (response.status === 401) {
                console.warn('Админ-токен истек, обновляю...');

                const refreshRes = await fetch('/api/refresh', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });

                if (refreshRes.ok) {
                    const data = await refreshRes.json();
                    localStorage.setItem('auth_token', data.authorization.token);
                    localStorage.setItem('user', JSON.stringify(data.user));

                    // Повторяем запрос с новым токеном
                    options.headers['Authorization'] = `Bearer ${data.authorization.token}`;
                    response = await fetch(url, options);
                } else {
                    localStorage.clear();
                    window.location.href = '/user/login?error=session_expired';
                    return null;
                }
            }

            const data = await response.json();
            if (!response.ok) {
                alert(data.error || data.message || `Ошибка ${response.status}`);
                return null;
            }
            return data;

        } catch (err) {
            alert('Ошибка сети: ' + err.message);
            return null;
        }
    }

    // --- ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ (БЕЗ ИЗМЕНЕНИЙ) ---
    if (!localStorage.getItem('auth_token')) window.location.href = '/user/login';
    try {
        const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
        currentUserId = storedUser.id;
    } catch (e) { console.warn('User session error'); }

    function formatDate(dateString) {
        if (!dateString) return '—';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        } catch { return '—'; }
    }

    function getAvatarUrl(avatar) { return avatar ? `/storage/${avatar}` : null; }

    function getBanStatus(bans) {
        if (!bans || !Array.isArray(bans) || bans.length === 0) return { isBanned: false, text: 'Активен', bg: 'bg-green-100', textColor: 'text-green-800', icon: '✅' };
        const now = new Date();
        const activeBan = bans.find(ban => !ban.expiration || new Date(ban.expiration) > now);
        if (!activeBan) return { isBanned: false, text: 'Активен', bg: 'bg-green-100', textColor: 'text-green-800', icon: '✅' };
        return { isBanned: true, text: activeBan.expiration === null ? 'Забанен (перм.)' : 'Забанен (врем.)', bg: 'bg-red-100', textColor: 'text-red-800', icon: '🚫', reason: activeBan.reason };
    }

    function getRoleDisplay(role) {
        const map = {
            admin:  { text: 'Админ', bg: 'bg-purple-100', textColor: 'text-purple-800', icon: '🔧' },
            moder:  { text: 'Модер', bg: 'bg-blue-100',    textColor: 'text-blue-800',   icon: '🛡️' },
            user:   { text: 'Юзер',  bg: 'bg-gray-100',   textColor: 'text-gray-800',   icon: '👤' }
        };
        return map[role?.name?.toLowerCase()] || { text: role?.name || 'User', bg: 'bg-gray-100', textColor: 'text-gray-800', icon: '👤' };
    }

    // --- ЛОГИКА ТАБЛИЦЫ ---
    async function loadUsers(page = 1) {
        document.getElementById('loading').classList.remove('hidden');
        const data = await apiRequest(`/api/user?page=${page}&per_page=${perPage}`);
        document.getElementById('loading').classList.add('hidden');

        if (!data || !data.users) {
            document.getElementById('errorContent').classList.remove('hidden');
            return;
        }

        const { data: users, current_page, total, last_page } = data.users;
        currentPage = current_page;
        totalUsers = total;
        totalPages = last_page;

        users.forEach(u => usersData[u.id] = u);
        displayUsers(users);
    }

    function displayUsers(users) {
        const container = document.getElementById('adminContent');
        container.classList.remove('hidden');

        const rows = users.map(user => {
            const isCurrent = user.id === currentUserId;
            const role = getRoleDisplay(user.role);
            const ban = getBanStatus(user.bans);
            const avatarUrl = getAvatarUrl(user.avatar);

            let actions = `<a href="/user/profile?id=${user.id}" class="text-blue-600 hover:underline">Профиль</a>`;
            if (!isCurrent) {
                actions += `
                    <button onclick="openEditModal(${user.id})" class="text-green-600 hover:underline">Изменить</button>
                    <button onclick="openRoleModal(${user.id})" class="text-purple-600 hover:underline">Роль</button>
                    ${ban.isBanned ? `<button onclick="unbanUser(${user.id})" class="text-orange-600 font-bold hover:underline">Разбанить</button>`
                    : `<button onclick="openBanModal(${user.id})" class="text-red-600 hover:underline">Забанить</button>`}
                    <button onclick="deleteUser(${user.id})" class="text-red-800 hover:underline">Удалить</button>
                `;
            }

            return `
                <tr class="border-b bg-white hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0 mr-3">
                                ${avatarUrl ? `<img src="${avatarUrl}" class="h-10 w-10 rounded-full object-cover border">`
                : `<div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold border">${user.name.charAt(0)}</div>`}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">${user.name} ${isCurrent ? '<span class="text-xs text-gray-400">(вы)</span>' : ''}</div>
                                <div class="text-sm text-gray-500">${user.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4"><span class="px-2 py-1 rounded-full text-xs font-semibold ${role.bg} ${role.textColor}">${role.icon} ${role.text}</span></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold ${ban.bg} ${ban.textColor}">${ban.icon} ${ban.text}</span>
                        ${ban.isBanned ? `<div class="text-[10px] mt-1 text-gray-400">Причина: ${ban.reason}</div>` : ''}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">ID: ${user.id}<br>${formatDate(user.created_at)}</td>
                    <td class="px-6 py-4"><div class="flex flex-wrap gap-2 text-sm">${actions}</div></td>
                </tr>
            `;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6 border-b bg-white"><h2 class="text-xl font-bold text-gray-800">Пользователи (${totalUsers})</h2></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-medium">
                            <tr><th class="px-6 py-3 border-b">Пользователь</th><th class="px-6 py-3 border-b">Роль</th><th class="px-6 py-3 border-b">Статус</th><th class="px-6 py-3 border-b">Инфо</th><th class="px-6 py-3 border-b">Действия</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t"></div>
            </div>
        `;
        updatePagination();
    }

    // --- ОБРАБОТЧИКИ ДЕЙСТВИЙ (ИСПОЛЬЗУЮТ НОВЫЙ apiRequest) ---

    document.getElementById('banForm').addEventListener('submit', async e => {
        e.preventDefault();
        const userId = document.getElementById('banUserId').value;
        const requestData = { user_id: userId, reason: document.getElementById('banReason').value };
        const type = document.querySelector('input[name="expiration_type"]:checked').value;

        if (type === 'temporary') {
            const date = new Date();
            date.setDate(date.getDate() + parseInt(document.getElementById('banDays').value));
            requestData.expiration = date.toISOString().slice(0, 19).replace('T', ' ');
        }
        if (await apiRequest('/api/user/ban', 'POST', requestData)) { closeBanModal(); loadUsers(currentPage); }
    });

    document.getElementById('editForm').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('editUserId').value;
        const updateData = { name: document.getElementById('editName').value, email: document.getElementById('editEmail').value };
        const pass = document.getElementById('editPassword').value;
        if (pass) updateData.password = pass;

        if (await apiRequest(`/api/user/${id}`, 'PATCH', updateData)) { closeEditModal(); loadUsers(currentPage); }
    });

    async function unbanUser(id) {
        if (confirm('Разблокировать пользователя?') && await apiRequest(`/api/user/unban/${id}`, 'PATCH')) loadUsers(currentPage);
    }

    async function deleteUser(id) {
        if (confirm('Удалить пользователя навсегда?') && await apiRequest(`/api/user/${id}`, 'DELETE')) loadUsers(currentPage);
    }

    document.getElementById('roleForm').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('roleUserId').value;
        const role = document.getElementById('userRole').value;
        if (await apiRequest(`/api/user/setRole/${id}`, 'PATCH', { role })) { closeRoleModal(); loadUsers(currentPage); }
    });

    // --- МОДАЛКИ И ПАГИНАЦИЯ ---
    function openBanModal(id) { document.getElementById('banUserId').value = id; document.getElementById('banReason').value = ''; document.getElementById('banModal').classList.remove('hidden'); }
    function closeBanModal() { document.getElementById('banModal').classList.add('hidden'); }
    function openEditModal(id) {
        const u = usersData[id];
        document.getElementById('editUserId').value = id;
        document.getElementById('editName').value = u.name;
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
    function openRoleModal(id) {
        document.getElementById('roleUserId').value = id;
        document.getElementById('userRole').value = usersData[id]?.role?.name || 'user';
        document.getElementById('roleModal').classList.remove('hidden');
    }
    function closeRoleModal() { document.getElementById('roleModal').classList.add('hidden'); }

    function updatePagination() {
        const el = document.getElementById('pagination');
        let html = `<div class="flex justify-center gap-2">`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button onclick="loadUsers(${i})" class="px-3 py-1 border rounded ${i===currentPage?'bg-blue-600 text-white':'bg-white hover:bg-gray-100'}">${i}</button>`;
        }
        el.innerHTML = html + `</div>`;
    }

    document.querySelectorAll('input[name="expiration_type"]').forEach(radio => {
        radio.addEventListener('change', e => document.getElementById('temporaryOptions').classList.toggle('hidden', e.target.value !== 'temporary'));
    });

    loadUsers();
</script>
</body>
</html>
