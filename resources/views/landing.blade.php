<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Komunalka - Облік комунальних послуг</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
</head>
<body class="antialiased bg-gradient-to-br from-amber-50 via-white to-amber-50 min-h-screen">
    <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-amber-500 selection:text-white">
        <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
            <header class="py-10">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Komunalka</h1>
                    </div>
                    <nav class="flex gap-4">
                        <a href="{{ route('filament.dashboard.auth.login') }}" class="px-6 py-2.5 text-sm font-semibold text-gray-700 hover:text-amber-600 transition-colors">
                            Вхід
                        </a>
                        <a href="{{ route('filament.dashboard.auth.register') }}" class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 rounded-lg shadow-lg hover:shadow-xl transition-all">
                            Реєстрація
                        </a>
                    </nav>
                </div>
            </header>

            <main class="mt-6">
                <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                    <div class="flex flex-col justify-center">
                        <h2 class="text-5xl font-bold text-gray-900 leading-tight mb-6">
                            Простий облік комунальних послуг
                        </h2>
                        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                            Веб-застосунок для зручного відстеження показань лічильників, аналізу споживання та контролю за термінами подання показань.
                        </p>
                        <div class="flex gap-4">
                            <a href="{{ route('filament.dashboard.auth.register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 rounded-xl shadow-lg hover:shadow-xl transition-all">
                                Почати безкоштовно
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                            <a href="{{ route('filament.dashboard.auth.login') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-amber-300 transition-all">
                                Увійти
                            </a>
                        </div>
                    </div>

                    <div class="lg:pl-12">
                        <div class="grid gap-6">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Облік показань</h3>
                                <p class="text-gray-600">Зручне внесення та зберігання показань лічильників з можливістю додавати фото та коментарі.</p>
                            </div>

                            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Аналіз споживання</h3>
                                <p class="text-gray-600">Детальна статистика, графіки та тренди споживання для кращого контролю витрат.</p>
                            </div>

                            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Нагадування</h3>
                                <p class="text-gray-600">Автоматичні нагадування про терміни подання показань, щоб ніколи не пропустити дедлайн.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Підтримка різних типів лічильників</h3>
                    <div class="flex flex-wrap justify-center gap-3 mt-6">
                        <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium text-sm">💧 Холодна вода</span>
                        <span class="px-4 py-2 bg-red-100 text-red-700 rounded-lg font-medium text-sm">🔥 Гаряча вода</span>
                        <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg font-medium text-sm">⚡ Газ</span>
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg font-medium text-sm">💡 Електроенергія</span>
                        <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm">🏠 Опалення</span>
                    </div>
                </div>
            </main>

            <footer class="py-16 text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Komunalka. Всі права захищені.</p>
            </footer>
        </div>
    </div>
</body>
</html>
