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
            // Добавляем 3 часа для корректировки часового пояса
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

    // Форматирование даты без времени
    function formatDateOnly(dateString) {
        if (!dateString) return 'Не указано';

        try {
            const date = new Date(dateString);
            date.setHours(date.getHours() + 3);

            return date.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            console.error('Error formatting date:', e);
            return 'Ошибка даты';
        }
    }

    // Форматирование даты в относительное время с учетом часового пояса
    function formatRelativeTime(dateString) {
        if (!dateString) return null;

        try {
            const date = new Date(dateString);
            date.setHours(date.getHours() + 3); // Корректировка часового пояса

            const now = new Date();
            const diffMs = date - now;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));

            if (diffDays > 0) {
                return `через ${diffDays} ${getDaysWord(diffDays)}`;
            } else if (diffDays < 0) {
                return `${Math.abs(diffDays)} ${getDaysWord(Math.abs(diffDays))} назад`;
            } else if (diffHours > 0) {
                return `через ${diffHours} ${getHoursWord(diffHours)}`;
            } else if (diffHours < 0) {
                return `${Math.abs(diffHours)} ${getHoursWord(Math.abs(diffHours))} назад`;
            } else {
                return 'сегодня';
            }
        } catch (e) {
            console.error('Error formatting relative time:', e);
            return null;
        }
    }

    function getDaysWord(days) {
        if (days % 10 === 1 && days % 100 !== 11) return 'день';
        if (days % 10 >= 2 && days % 10 <= 4 && (days % 100 < 10 || days % 100 >= 20)) return 'дня';
        return 'дней';
    }

    function getHoursWord(hours) {
        if (hours % 10 === 1 && hours % 100 !== 11) return 'час';
        if (hours % 10 >= 2 && hours % 10 <= 4 && (hours % 100 < 10 || hours % 100 >= 20)) return 'часа';
        return 'часов';
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

    // Проверка статуса бана с учетом часового пояса
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
                `,
                reason: null,
                expirationDate: null,
                isPermanent: false,
                activeBan: null
            };
        }

        // Находим активные баны с учетом часового пояса
        const now = new Date();
        now.setHours(now.getHours() + 3); // Корректировка часового пояса

        const activeBan = bans.find(ban => {
            // Если expiration null - перманентный бан
            if (ban.expiration === null) return true;

            try {
                // Если expiration в будущем - временный бан еще активен
                const expirationDate = new Date(ban.expiration);
                expirationDate.setHours(expirationDate.getHours() + 3); // Корректировка часового пояса
                return expirationDate > now;
            } catch (e) {
                console.error('Error parsing ban expiration date:', e);
                return false;
            }
        });

        if (!activeBan) {
            // Все баны истекли
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
                `,
                reason: null,
                expirationDate: null,
                isPermanent: false,
                activeBan: null
            };
        }

        const isPermanent = activeBan.expiration === null;
        let expirationText = '';
        let relativeTime = '';

        if (isPermanent) {
            expirationText = 'Перманентный бан';
        } else {
            try {
                const expirationDate = new Date(activeBan.expiration);
                expirationDate.setHours(expirationDate.getHours() + 3); // Корректировка часового пояса
                expirationText = `Бан до: ${expirationDate.toLocaleDateString('ru-RU')} ${expirationDate.toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'})}`;

                relativeTime = formatRelativeTime(activeBan.expiration);
                if (relativeTime && relativeTime.startsWith('через')) {
                    expirationText += ` (${relativeTime})`;
                }
            } catch (e) {
                console.error('Error formatting ban expiration:', e);
                expirationText = 'Ошибка формата даты';
            }
        }

        return {
            isBanned: true,
            text: isPermanent ? 'Забанен (перманентно)' : 'Забанен (временно)',
            color: 'red',
            bgColor: 'red-50',
            borderColor: 'red-200',
            icon: `
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            `,
            reason: activeBan.reason,
            expirationDate: activeBan.expiration,
            isPermanent: isPermanent,
            expirationText: expirationText,
            activeBan: activeBan
        };
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

            // Пытаемся получить данные пользователя через API
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
                displayProfile(data.user);
            } else if (response.status === 401) {
                // Токен недействителен
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/user/login';
            } else if (response.status === 403) {
                // Пользователь забанен
                const errorData = await response.json();
                showBanError(errorData.message || 'Ваш аккаунт забанен');
            } else {
                showError();
            }
        } catch (error) {
            console.error('Error loading profile:', error);
            // Используем данные из localStorage, если API недоступен
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

        // Получаем статус верификации
        const verification = getVerificationStatus(user.email_verified_at);

        // Получаем статус бана
        const banStatus = getBanStatus(user.bans);

        // Получаем информацию о роли
        const roleInfo = getRoleInfo(user.role || 'user');

        // Определяем инициалы для аватара
        const initials = user.name ? user.name.charAt(0).toUpperCase() : 'U';

        // Если пользователь забанен, показываем предупреждение
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
                            ${banStatus.expirationText ? `<p class="mt-1">${banStatus.expirationText}</p>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        profileContent.innerHTML = banWarning + `
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Заголовок профиля -->
                <div class="${banStatus.isBanned ? 'bg-gradient-to-r from-red-500 to-red-700' : roleInfo.color === 'purple' ? 'bg-gradient-to-r from-purple-500 to-pink-600' : roleInfo.color === 'blue' ? 'bg-gradient-to-r from-blue-500 to-cyan-600' : 'bg-gradient-to-r from-blue-500 to-purple-600'} p-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-2xl font-bold ${banStatus.isBanned ? 'text-red-500' : roleInfo.color === 'purple' ? 'text-purple-500' : roleInfo.color === 'blue' ? 'text-blue-500' : 'text-blue-500'} shadow-lg">
                                ${initials}
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
                                    ${banStatus.icon}
                                    <span>Забанен</span>
                                </div>
                            ` : ''}
                            <button onclick="logout()"
                                    class="bg-white ${banStatus.isBanned ? 'text-red-500 hover:bg-red-50' : roleInfo.color === 'purple' ? 'text-purple-500 hover:bg-purple-50' : roleInfo.color === 'blue' ? 'text-blue-500 hover:bg-blue-50' : 'text-blue-500 hover:bg-blue-50'} px-4 py-2 rounded-lg transition font-semibold">
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
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <p class="text-sm text-gray-500 mb-1">Имя</p>
                                <p class="text-lg font-semibold text-gray-800">${user.name || 'Не указано'}</p>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <p class="text-sm text-gray-500 mb-1">Email</p>
                                <p class="text-lg font-semibold text-gray-800">${user.email || 'Не указан'}</p>
                            </div>

                            <div class="border border-${verification.borderColor} bg-${verification.bgColor} rounded-lg p-4 hover:shadow-md transition">
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
                        </div>

                        <!-- Правая колонка -->
                        <div class="space-y-4">
                            <!-- Роль пользователя -->
                            <div class="border border-${roleInfo.color}-200 bg-${roleInfo.bgColor} rounded-lg p-4 hover:shadow-md transition">
                                <p class="text-sm text-gray-500 mb-1">Роль</p>
                                <div class="flex items-center gap-2">
                                    ${roleInfo.icon}
                                    <p class="text-lg font-semibold text-${roleInfo.color}-600">
                                        ${roleInfo.text}
                                    </p>
                                </div>
                                ${user.role === 'admin' ? `
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
                            <div class="border border-${banStatus.borderColor} bg-${banStatus.bgColor} rounded-lg p-4 hover:shadow-md transition">
                                <p class="text-sm text-gray-500 mb-1">Статус аккаунта</p>
                                <div class="flex items-center gap-2">
                                    ${banStatus.icon}
                                    <p class="text-lg font-semibold text-${banStatus.color}-600">
                                        ${banStatus.text}
                                    </p>
                                </div>
                                ${banStatus.isBanned ? `
                                    <div class="mt-3 space-y-2">
                                        ${banStatus.reason ? `
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Причина бана:</p>
                                            <p class="text-sm font-medium text-${banStatus.color}-700">${banStatus.reason}</p>
                                        </div>
                                        ` : ''}
                                        ${banStatus.expirationText ? `
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Срок бана:</p>
                                            <p class="text-sm font-medium text-${banStatus.color}-700">${banStatus.expirationText}</p>
                                        </div>
                                        ` : ''}
                                        ${banStatus.isPermanent ? `
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Перманентный бан
                                            </span>
                                        </div>
                                        ` : ''}
                                    </div>
                                ` : ''}
                            </div>

                            ${user.created_at ? `
                            <div class="border border-blue-200 bg-blue-50 rounded-lg p-4 hover:shadow-md transition">
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
                            <div class="border border-green-200 bg-green-50 rounded-lg p-4 hover:shadow-md transition">
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
                               class="px-6 py-2 ${user.role === 'admin' ? 'bg-purple-500 hover:bg-purple-600' : 'bg-blue-500 hover:bg-blue-600'} text-white rounded-lg transition duration-200 font-medium flex items-center gap-2">
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
                        ` : ''}
                    </div>
                </div>
            </div>

            <!-- История банов (если есть) -->
            ${user.bans && user.bans.length > 0 ? `
            <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">История банов</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Причина</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата бана</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Истекает</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${user.bans.map(ban => {
            let expirationDate = null;
            let isActive = false;
            let statusText = '';
            let statusColor = '';

            if (ban.expiration === null) {
                isActive = true;
                statusText = 'Перманентный';
                statusColor = 'bg-red-100 text-red-800';
            } else {
                try {
                    expirationDate = new Date(ban.expiration);
                    expirationDate.setHours(expirationDate.getHours() + 3); // Корректировка часового пояса
                    const now = new Date();
                    now.setHours(now.getHours() + 3);

                    if (expirationDate > now) {
                        isActive = true;
                        statusText = 'Активен';
                        statusColor = 'bg-red-100 text-red-800';
                    } else {
                        isActive = false;
                        statusText = 'Истёк';
                        statusColor = 'bg-green-100 text-green-800';
                    }
                } catch (e) {
                    console.error('Error parsing ban expiration:', e);
                    statusText = 'Ошибка';
                    statusColor = 'bg-gray-100 text-gray-800';
                }
            }

            return `
                                        <tr class="${isActive ? 'bg-red-50' : ''}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${ban.reason || 'Не указана'}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(ban.created_at)}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${ban.expiration === null ? 'Перманентно' : (expirationDate ? formatDate(ban.expiration) : 'Ошибка формата')}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">
                                                    ${statusText}
                                                </span>
                                            </td>
                                        </tr>
                                    `;
        }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            ` : ''}
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

    // Функция для обжалования бана
    async function appealBan() {
        const token = localStorage.getItem('auth_token');
        const user = JSON.parse(localStorage.getItem('user'));

        if (!token || !user) return;

        try {
            const reason = prompt('Укажите причину обжалования бана:');
            if (!reason) return;

            alert('Заявка на обжалование бана отправлена администраторам!');
        } catch (error) {
            console.error('Error appealing ban:', error);
            alert('Ошибка при отправке заявки');
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
            // Всегда очищаем localStorage и перенаправляем
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/user/login';
        }
    }

    // Загружаем профиль при загрузке страницы
    window.addEventListener('load', loadProfile);
</script>
</body>
</html>
