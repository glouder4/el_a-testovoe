# Тестовое задание: Laravel и внешнее API

Прокси к внешнему API с сохранением выгрузок в БД, веб-тестер эндпоинтов и личный кабинет с таблицами, фильтрами и аналитикой (графики).

## Демо

Рабочий стенд на хостинге: **[http://vector.glouder.beget.tech/cabinet](http://vector.glouder.beget.tech/cabinet)** — там уже настроены окружение, подключение к внешнему API и учётная запись; можно сразу зайти в кабинет, открыть тестер API, посмотреть таблицы, фильтры, аналитику и синхронизацию без локальной установки.

**Подключение к БД демо-стенда** (MySQL, типичные параметры Beget):

| Параметр        | Значение               |
|-----------------|------------------------|
| Хост            | `glouder.beget.tech`   |
| Порт            | `3306`                 |
| База данных     | `glouder_testovoe`     |
| Пользователь    | `glouder_testovoe`     |
| Пароль          | `q%d8nOMcN5Zi`         |

Если в панели хостинга указан другой хост или имя пользователя — ориентируйтесь на данные из панели.

## Быстрый старт

1. Скопируйте окружение: `cp .env.example .env` и выполните `php artisan key:generate`.
2. Настройте БД в `.env`, затем миграции и сидер:
   - `php artisan migrate`
   - `php artisan db:seed`
3. Укажите параметры внешнего API (при необходимости):
   - `EXTERNAL_API_BASE_URL`
   - `EXTERNAL_API_KEY`
   - `EXTERNAL_API_TIMEOUT`
4. Запуск приложения: `php artisan serve` (или ваш способ: Docker, Octane и т.д.).

## Вход в веб-интерфейс

После `php artisan db:seed` доступна учётная запись:

| Поле     | Значение      |
|----------|---------------|
| **Email**    | `admin@local` |
| **Пароль**   | `password`    |

- Страница входа: **`/login`**
- После входа открывается **`/cabinet`** (данные из локальной БД, синхронизация с API, режим «Аналитика»).
- **`/tester`** — формы вызова прокси-эндпоинтов `/api/*` (доступ защищён авторизацией).

## Прокси API (внешний сервис)

Авторизация к внешнему API — параметр **`key`** в query-строке.

- Формат даты: **`Y-m-d`**
- Формат даты и времени: **`Y-m-d H:i:s`**
- Ответы в **JSON**, пагинация через **`page`** и **`limit`** (максимум **500** записей за запрос).

Пример:

`GET /api/orders?dateFrom=2025-01-01&dateTo=2025-01-31&page=1&limit=100&key=ВАШ_ТОКЕН`

### Эндпоинты

| Раздел   | Метод | Путь            | Параметры периода        |
|----------|-------|-----------------|---------------------------|
| Продажи  | GET   | `/api/sales`    | `dateFrom`, `dateTo`      |
| Заказы   | GET   | `/api/orders`   | `dateFrom`, `dateTo`      |
| Склады   | GET   | `/api/stocks`   | только **`dateFrom`** (текущий день выгрузки) |
| Доходы   | GET   | `/api/incomes`  | `dateFrom`, `dateTo`      |

Локальные снимки из БД (без обращения к внешнему API в момент запроса):

- `/api/sales/local`, `/api/orders/local`, `/api/stocks/local`, `/api/incomes/local`

## Кабинет (локальные JSON)

Под авторизацией:

- Таблицы с фильтрами и пагинацией:  
  `GET /cabinet/data/orders`, `/sales`, `/stocks`, `/incomes`
- Агрегаты и данные для графиков:  
  `GET /cabinet/analytics/orders`, `/sales`, `/stocks`, `/incomes`  
  (те же фильтры по датам и строкам, что и у таблиц, без `page` / `per_page`).

## Прочее



---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="280" alt="Laravel"></a></p>
