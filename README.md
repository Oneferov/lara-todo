Клонировать репозиторий в локальную папку:
git clone https://github.com/Oneferov/lara-todo.git

При установленном локально composer:
composer update

Копируем .env.example;
Переименовываем в .env;

Генерируем ключ приложения:
php artisan key:generate

Запускаем миграции базы данных для заполнения таблицами:
php artisan migrate

Реализуем символическую ссылку для доступа к каталогу storage:
php artisan storage:link

При установленом локаьлно node и npm:
npm run dev

Приложение доступно ао адресу:
http://127.0.0.1:8000