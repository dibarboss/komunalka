# Комуналка – модуль лічильників

Додаток реалізує модуль внесення та аналітики показань побутових лічильників з підтримкою багатоквартирності (
addresses/tenancy). Інтерфейс для роботи з даними побудовано на [Filament](https://filamentphp.com/). REST API для
основних сутностей уже підключений через Laravel Sanctum, але головний фокус поточного етапу — адміністраторська панель.

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F7b410d69-957d-492d-aaaf-4a32202b899e%3Flabel%3D1&style=for-the-badge)](https://forge.laravel.com/dmytro-maksiutenko/summer-oslo/2900340)
<a href="https://laravel.com"><img alt="Laravel v12+" src="https://img.shields.io/badge/Laravel-v12+-FF2D20?style=for-the-badge&logo=laravel"></a>
<a href="https://php.net"><img alt="PHP 8.2+" src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php"></a>

## Вимоги

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (за замовчуванням) або будь-яка підтримувана Laravel СУБД

## Підготовка середовища

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Після `db:seed` доступні демо-користувачі:

- Власник: `demo@komunalka.test` / `password`
- Співвласник (має доступ до спільних адрес): `roommate@komunalka.test` / `password`

Статичні файли зображень зберігаються на диску `public`. За потреби створіть символічне посилання:

```bash
php artisan storage:link
```

## Filament панель

Запуск локального сервера Laravel:

```bash
php artisan serve
```

Адмін-панель буде доступна за адресою `http://localhost:8000/admin`. У панелі можна:

- створювати адреси (tenant) та запрошувати учасників для спільного внесення показників;
- керувати лічильниками у розрізі адрес (додавати/редагувати можуть лише власники адреси, співвласники бачать дані та
  можуть вносити показники);
- додавати показання з перевіркою збільшення, фото та коментарем;
- переглядати історію показань, статистику та графік споживання.
  Редагувати або видаляти можна лише останній запис для конкретного лічильника. Видалення лічильників та адрес дозволено
  тільки власникам.

## REST API

REST API для керування лічильниками, показаннями та статистикою вже підготовлено й працює через Sanctum-токени (запити
потребують заголовка `Authorization: Bearer <token>`). Додаткові ендпоінти для адрес і запрошень розширюватимемо на
наступних етапах після фіналізації інтерфейсу.

## Тести

```bash
php artisan test
```

Покриття включає сценарії роботи REST API з валідацією, зберіганням файлів та статистикою.

🇺🇦 **Зроблено в Україні** з ❤️
