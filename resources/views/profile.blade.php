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
    <div id="loading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        <p class="text-gray-600 mt-4">Загрузка данных профиля...</p>
    </div>

    <div id="profileContent" class="hidden">
        <!-- Контент профиля будет загружен через JavaScript -->
    </div>

    <div id="errorContent" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        Ошибка загрузки профиля. <a href="/user/login" class="underline">Войдите</a> заново.
    </div>
</div>

<!-- Модальное окно для загрузки аватара -->
<div id="avatarModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Загрузка аватара</h3>

            <div class="mb-4">
                <div class="flex justify-center mb-4">
                    <img id="avatarPreview" src="" alt="Предпросмотр" class="hidden w-32 h-32 rounded-full object-cover border-2 border-gray-300">
                </div>

                <form id="avatarUploadForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Выберите изображение</label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF до 2MB</p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAvatarModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Отмена
                        </button>
                        <button type="submit" id="uploadAvatarBtn"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Загрузить
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

    // Форматирование даты с учетом часового пояса
    function formatDate(dateString) {
        if (!dateString) return 'Не указано';

        try {
            const date = new Date(dateString);
            date.setHours(date.getHours() + 3);

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

    // Получение информации о роли
    function getRoleInfo(role) {
        const roles = {
            'administrator': {
                text: 'Администратор',
                color: 'purple',
                bgColor: 'purple-100',
                textColor: 'purple-800',
                icon: `
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                `
            },
            'moderator': {
                text: 'Модератор',
                color: 'blue',
                bgColor: 'blue-100',
                textColor: 'blue-800',
                icon: `
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                `
            },
            'user': {
                text: 'Пользователь',
                color: 'green',
                bgColor: 'green-100',
                textColor: 'green-800',
                icon: `
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                `
            }
        };

        return roles[role] || {
            text: role || 'Пользователь',
            color: 'gray',
            bgColor: 'gray-100',
            textColor: 'gray-800',
            icon: `
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            `
        };
    }

    // Проверка верификации email
    function getVerificationStatus(emailVerifiedAt) {
        if (!emailVerifiedAt) {
            return {
                status: 'not_verified',
                text: 'Не верифицирован',
                color: 'red',
                bgColor: 'red-50',
                borderColor: 'red-200',
                icon: `
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.212 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                `
            };
        } else {
            return {
                status: 'verified',
                text: 'Верифицирован ' + formatDate(emailVerifiedAt),
                color: 'green',
                bgColor: 'green-50',
                borderColor: 'green-200',
                icon: `
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            };
        }
    }

    // Проверка статуса бана
    function getBanStatus(bans) {
        if (!bans || !Array.isArray(bans) || bans.length === 0) {
            return {
                isBanned: false,
                text: 'Активен',
                color: 'green',
                bgColor: 'green-50',
                borderColor: 'green-200',
                icon: `
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            };
        }

        const now = new Date();
        now.setHours(now.getHours() + 3);

        const activeBan = bans.find(ban => {
            if (ban.expiration === null) return true;

            try {
                const expirationDate = new Date(ban.expiration);
                expirationDate.setHours(expirationDate.getHours() + 3);
                return expirationDate > now;
            } catch (e) {
                return false;
            }
        });

        if (!activeBan) {
            return {
                isBanned: false,
                text: 'Активен (был забанен ранее)',
                color: 'blue',
                bgColor: 'blue-50',
                borderColor: 'blue-200',
                icon: `
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            };
        }

        return {
            isBanned: true,
            text: activeBan.expiration === null ? 'Забанен (перманентно)' : 'Забанен (временно)',
            color: 'red',
            bgColor: 'red-50',
            borderColor: 'red-200',
            icon: `
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            `,
            reason: activeBan.reason,
            activeBan: activeBan
        };
    }

    // Получение URL аватара
    function getAvatarUrl(user) {
        if (user.avatar) {
            const timestamp = new Date().getTime();
            return `/storage/${user.avatar}?t=${timestamp}`;
        }
        return null;
    }

    // Открытие модального окна для загрузки аватара
    function openAvatarModal() {
        const modal = document.getElementById('avatarModal');
        modal.classList.remove('hidden');
        document.getElementById('avatarInput').value = '';
        document.getElementById('avatarPreview').classList.add('hidden');
        document.getElementById('avatarPreview').src = '';
    }

    // Закрытие модального окна
    function closeAvatarModal() {
        const modal = document.getElementById('avatarModal');
        modal.classList.add('hidden');
    }

    // Загрузка аватара
    async function uploadAvatar() {
        const token = localStorage.getItem('auth_token');
        const fileInput = document.getElementById('avatarInput');

        if (!fileInput.files.length) {
            alert('Пожалуйста, выберите файл');
            return;
        }

        const file = fileInput.files[0];

        if (file.size > 2 * 1024 * 1024) {
            alert('Файл слишком большой. Максимальный размер: 2MB');
            return;
        }

        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Пожалуйста, выберите изображение в формате JPG, PNG, GIF или WebP');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            const uploadBtn = document.getElementById('uploadAvatarBtn');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Загрузка...';

            const response = await fetch('/api/user/avatar/upload', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                const user = JSON.parse(localStorage.getItem('user'));
                user.avatar = data.path;
                localStorage.setItem('user', JSON.stringify(user));

                loadProfile();
                closeAvatarModal();
                alert('Аватар успешно загружен!');
            } else {
                alert('Ошибка при загрузке аватара: ' + (data.message || 'Неизвестная ошибка'));
            }
        } catch (error) {
            console.error('Error uploading avatar:', error);
            alert('Ошибка при загрузке аватара');
        } finally {
            const uploadBtn = document.getElementById('uploadAvatarBtn');
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Загрузить';
        }
    }

    // Удаление аватара
    async function deleteAvatar() {
        if (!confirm('Вы уверены, что хотите удалить аватар?')) {
            return;
        }

        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user'));

        try {
            const response = await fetch('/api/user/avatar/destroy', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();

            if (response.ok) {
                user.avatar = null;
                localStorage.setItem('user', JSON.stringify(user));

                loadProfile();
                alert('Аватар успешно удален!');
            } else {
                alert('Ошибка при удалении аватара: ' + (data.message || 'Неизвестная ошибка'));
            }
        } catch (error) {
            console.error('Error deleting avatar:', error);
            alert('Ошибка при удалении аватара');
        }
    }

    // Предпросмотр аватара перед загрузкой
    function setupAvatarPreview() {
        const fileInput = document.getElementById('avatarInput');
        const preview = document.getElementById('avatarPreview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();

                reader.addEventListener('load', function() {
                    preview.src = this.result;
                    preview.classList.remove('hidden');
                });

                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
                preview.src = '';
            }
        });
    }

    // Загружаем данные профиля
    async function loadProfile() {
        const token = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');

        if (!token) {
            showError();
            return;
        }

        try {
            let userId;
            try {
                const userData = JSON.parse(storedUser);
                userId = userData.id;
            } catch (e) {
                console.error('Error parsing user data:', e);
                showError();
                return;
            }

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
                localStorage.setItem('user', JSON.stringify(data.user));
                displayProfile(data.user);
            } else if (response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/user/login';
            } else if (response.status === 403) {
                const errorData = await response.json();
                showBanError(errorData.message || 'Ваш аккаунт забанен');
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            if (storedUser) {
                try {
                    const userData = JSON.parse(storedUser);
                    displayProfile(userData);
                } catch (e) {
                    showError();
                }
            } else {
                showError();
            }
        }
    }

    function displayProfile(user) {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('errorContent').classList.add('hidden');

        const profileContent = document.getElementById('profileContent');
        profileContent.classList.remove('hidden');

        const verification = getVerificationStatus(user.email_verified_at);
        const banStatus = getBanStatus(user.bans);
        const roleInfo = getRoleInfo(user.role || 'user');
        const initials = user.name ? user.name.charAt(0).toUpperCase() : 'U';
        const avatarUrl = getAvatarUrl(user);

        let banWarning = '';
        if (banStatus.isBanned) {
            banWarning = `
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.212 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <h3 class="font-bold">Ваш аккаунт забанен!</h3>
                            <p class="mt-1">${banStatus.activeBan.reason || 'Причина не указана'}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        // Создаем безопасный HTML для аватара
        let avatarHtml = '';
        if (avatarUrl) {
            avatarHtml = `
               <div class="relative">
                <!-- Аватар или заглушка -->
                <div class="w-20 h-20 rounded-full overflow-hidden bg-white">
                    <img src="${avatarUrl.replace(/"/g, '&quot;')}"
                         alt="Аватар"
                         class="w-full h-full object-cover"
                         onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\\'w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold text-blue-500\\'>${initials}</div>';">
                </div>

                <!-- Кнопки управления — поверх аватара -->
                ${user.avatar ? `
                <button onclick="deleteAvatar()"
                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition shadow-md z-30">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                ` : ''}

                <button onclick="openAvatarModal()"
                        class="absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-blue-600 transition shadow-md z-30">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
            `;
        } else {
            avatarHtml = `
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-2xl font-bold ${banStatus.isBanned ? 'text-red-500' : roleInfo.color === 'purple' ? 'text-purple-500' : roleInfo.color === 'blue' ? 'text-blue-500' : 'text-blue-500'} shadow-lg">
                    ${initials}
                </div>
                <button onclick="openAvatarModal()"
                        class="absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-blue-600 transition shadow-md">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            `;
        }

        profileContent.innerHTML = banWarning + `
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Заголовок профиля -->
                <div class="${banStatus.isBanned ? 'bg-gradient-to-r from-red-500 to-red-700' : roleInfo.color === 'purple' ? 'bg-gradient-to-r from-purple-500 to-pink-600' : roleInfo.color === 'blue' ? 'bg-gradient-to-r from-blue-500 to-cyan-600' : 'bg-gradient-to-r from-blue-500 to-purple-600'} p-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                ${avatarHtml}
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold">${user.name || 'Пользователь'}</h1>
                                <p class="${banStatus.isBanned ? 'text-red-100' : roleInfo.color === 'purple' ? 'text-purple-100' : roleInfo.color === 'blue' ? 'text-blue-100' : 'text-blue-100'}">${user.email || ''}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-sm px-2 py-1 rounded ${banStatus.isBanned ? 'bg-red-100 text-red-800' : `bg-${roleInfo.bgColor} text-${roleInfo.textColor}`} font-medium">
                                        ${roleInfo.text}
                                    </span>
                                    <p class="text-sm ${banStatus.isBanned ? 'text-red-100' : roleInfo.color === 'purple' ? 'text-purple-100' : roleInfo.color === 'blue' ? 'text-blue-100' : 'text-blue-100'}">ID: ${user.id || ''}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex items-center gap-3">
                            ${banStatus.isBanned ? `
                                <div class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    <span>Забанен</span>
                                </div>
                            ` : ''}
                            <button onclick="logout()"
                                    class="bg-white ${banStatus.isBanned ? 'text-red-500 hover:bg-red-50' : roleInfo.color === 'purple' ? 'text-purple-500 hover:bg-purple-50' : roleInfo.color === 'blue' ? 'text-blue-500 hover:bg-blue-50' : 'text-blue-500 hover:bg-blue-50'} px-4 py-2 rounded-lg transition font-semibold shadow">
                                Выйти
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Основная информация -->
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Личная информация</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Левая колонка -->
                        <div class="space-y-4">
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-gray-500 mb-1">Имя</p>
                                <p class="text-lg font-semibold text-gray-800">${user.name || 'Не указано'}</p>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="text-lg font-semibold text-gray-800">${user.email || 'Не указан'}</p>
                            </div>

                            <div class="border border-${verification.borderColor} bg-${verification.bgColor} rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-gray-500 mb-1">Статус email</p>
                                <div class="flex items-center gap-2">
                                    ${verification.icon}
                                    <p class="text-lg font-semibold text-${verification.color}-600">
                                        ${verification.text}
                                    </p>
                                </div>
                                ${!user.email_verified_at ? `
                                <div class="mt-3">
                                    <button onclick="sendVerificationEmail()"
                                            class="text-sm bg-${verification.color}-100 text-${verification.color}-600 px-3 py-1 rounded hover:bg-${verification.color}-200 transition">
                                        Отправить письмо для верификации
                                    </button>
                                </div>
                                ` : ''}
                            </div>

                            <!-- Блок управления аватаром -->
                            <div class="border border-blue-200 bg-blue-50 rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-blue-500 mb-3 font-medium">Управление аватаром</p>
                               <div class="flex flex-wrap gap-3">
                                <button onclick="openAvatarModal()"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 text-sm font-medium flex items-center gap-2 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Загрузить
                                </button>
                                ${user.avatar ? `
                                <button onclick="deleteAvatar()"
                                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200 text-sm font-medium flex items-center gap-2 whitespace-nowrap relative z-10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Удалить
                                </button>
                                ` : ''}
                            </div>
                                ${user.avatar ? `
                                <div class="mt-3">
                                    <p class="text-xs text-gray-600 truncate">Текущий аватар: ${user.avatar}</p>
                                </div>
                                ` : `
                                <div class="mt-3">
                                    <p class="text-sm text-gray-600">Аватар не установлен</p>
                                </div>
                                `}
                            </div>
                        </div>

                        <!-- Правая колонка -->
                        <div class="space-y-4">
                            <!-- Роль пользователя -->
                            <div class="border border-${roleInfo.color}-200 bg-${roleInfo.bgColor} rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-gray-500 mb-1">Роль</p>
                                <div class="flex items-center gap-2">
                                    ${roleInfo.icon}
                                    <p class="text-lg font-semibold text-${roleInfo.color}-600">
                                        ${roleInfo.text}
                                    </p>
                                </div>
                                ${user.role === 'administrator' ? `
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                        Полные права доступа
                                    </span>
                                </div>
                                ` : user.role === 'moderator' ? `
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Права модератора
                                    </span>
                                </div>
                                ` : ''}
                            </div>

                            <!-- Статус аккаунта (бан/активен) -->
                            <div class="border border-${banStatus.borderColor} bg-${banStatus.bgColor} rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-gray-500 mb-1">Статус аккаунта</p>
                                <div class="flex items-center gap-2">
                                    ${banStatus.icon}
                                    <p class="text-lg font-semibold text-${banStatus.color}-600">
                                        ${banStatus.text}
                                    </p>
                                </div>
                                ${banStatus.isBanned && banStatus.reason ? `
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-500 mb-1">Причина бана:</p>
                                        <p class="text-sm font-medium text-${banStatus.color}-700">${banStatus.reason}</p>
                                    </div>
                                ` : ''}
                            </div>

                            ${user.created_at ? `
                            <div class="border border-blue-200 bg-blue-50 rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-blue-500 mb-1">Дата регистрации</p>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-lg font-semibold text-blue-700">
                                        ${formatDate(user.created_at)}
                                    </p>
                                </div>
                            </div>
                            ` : ''}

                            ${user.updated_at ? `
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4 hover:shadow-sm transition">
                                <p class="text-sm text-green-500 mb-1">Последнее обновление</p>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-lg font-semibold text-green-700">
                                        ${formatDate(user.updated_at)}
                                    </p>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Блок с действиями -->
                    <div class="mt-8 pt-6 border-t">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Действия</h3>
                        <div class="flex flex-wrap gap-4">
                            <a href="/user/edit"
                               class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200 font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Редактировать профиль
                            </a>

                            ${!user.email_verified_at ? `
                            <button onclick="sendVerificationEmail()"
                                    class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition duration-200 font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Подтвердить Email
                            </button>
                            ` : ''}

                            ${user.role === 'administrator' || user.role === 'moderator' ? `
                            <a href="/admin/dashboard"
                               class="px-6 py-2 ${user.role === 'administrator' ? 'bg-purple-500 hover:bg-purple-600' : 'bg-blue-500 hover:bg-blue-600'} text-white rounded-lg transition duration-200 font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Панель управления
                            </a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика и информация -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-2">Роль и статус</h3>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-1 rounded text-xs font-medium bg-${roleInfo.bgColor} text-${roleInfo.textColor}">
                            ${roleInfo.text}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full ${banStatus.isBanned ? 'bg-red-500' : user.email_verified_at ? 'bg-green-500' : 'bg-yellow-500'}"></div>
                        <p class="font-medium ${banStatus.isBanned ? 'text-red-600' : user.email_verified_at ? 'text-green-600' : 'text-yellow-600'}">
                            ${banStatus.isBanned ? 'Забанен' : user.email_verified_at ? 'Активен' : 'Требует подтверждения'}
                        </p>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-2">В системе</h3>
                    ${user.created_at ? `
                    <p class="text-2xl font-bold text-blue-500">
                        ${Math.floor((new Date() - new Date(user.created_at)) / (1000 * 60 * 60 * 24))}
                    </p>
                    <p class="text-sm text-gray-500">дней</p>
                    ` : 'Неизвестно'}
                </div>

                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-2">Безопасность</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            ${user.email_verified_at ?
            '<span class="text-green-500">✓</span><span class="text-sm">Email подтвержден</span>' :
            '<span class="text-yellow-500">!</span><span class="text-sm">Email не подтвержден</span>'}
                        </div>
                        <div class="flex items-center gap-2">
                            ${banStatus.isBanned ?
            '<span class="text-red-500">✗</span><span class="text-sm">Аккаунт забанен</span>' :
            '<span class="text-green-500">✓</span><span class="text-sm">Аккаунт активен</span>'}
                        </div>
                        ${user.avatar ? `
                        <div class="flex items-center gap-2">
                            <span class="text-green-500">✓</span>
                            <span class="text-sm">Аватар установлен</span>
                        </div>
                        ` : `
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">○</span>
                            <span class="text-sm">Аватар не установлен</span>
                        </div>
                        `}
                    </div>
                </div>
            </div>
        `;
    }

    function showError() {
        document.getElementById('loading').classList.add('hidden');
        const errorContent = document.getElementById('errorContent');
        errorContent.classList.remove('hidden');
        errorContent.innerHTML = 'Ошибка загрузки профиля. <a href="/user/login" class="underline">Войдите</a> заново.';
    }

    function showBanError(message) {
        document.getElementById('loading').classList.add('hidden');
        const errorContent = document.getElementById('errorContent');
        errorContent.classList.remove('hidden');
        errorContent.innerHTML = `
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.212 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <strong class="font-bold">${message || 'Ваш аккаунт забанен!'}</strong>
                    <p class="mt-1">Обратитесь к администратору для выяснения причин.</p>
                    <div class="mt-2">
                        <button onclick="logout()" class="text-sm bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                            Выйти
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

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

    async function logout() {
        const token = localStorage.getItem('auth_token');

        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/user/login';
        }
    }

    // Инициализация
    document.addEventListener('DOMContentLoaded', function() {
        // Настройка формы загрузки аватара
        const avatarForm = document.getElementById('avatarUploadForm');
        if (avatarForm) {
            avatarForm.addEventListener('submit', function(e) {
                e.preventDefault();
                uploadAvatar();
            });
        }

        // Настройка предпросмотра аватара
        setupAvatarPreview();

        // Закрытие модального окна при клике вне его
        const modal = document.getElementById('avatarModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAvatarModal();
                }
            });
        }

        // Закрытие модального окна по клавише ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeAvatarModal();
            }
        });

        // Загружаем профиль при загрузке страницы
        loadProfile();
    });
</script>
</body>
</html>
