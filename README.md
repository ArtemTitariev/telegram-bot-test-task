# Тестове завдання

## Реєстрація telegram бота

### Завдання

Зареєструвати telegram бота, щоб далі з ним працювати.

## Налаштування сервера

### Завдання

Налаштувати веб-сервер, який прийматиме через webhook запити від бота і зможе надсилати відповіді користувачу через бота. На веб-сервері має бути налаштована база даних, в яку бот зможе зберігати дані для користувачів.

## Створення групового чату

### Завдання

Створити груповий чат в telegram, до якого потрібно додати PM.

## Ініціалізація чату бота

### Завдання

PM має запустити команду Start.
- Бот повинен записати PM до бази даних.
- Бот повинен вітати PM на ім'я.

## Створення дошки у Trello

### Завдання

- Створити нову дошку у Trello.
- Створити колонки "InProgress" та "Done".

## Налаштування API

### Завдання

- Зробити API для отримання Webhook від Trello.
- Trello повинен передавати через Webhook інформацію про переміщення картки з колонки "In Progress" до "Done" і навпаки на сервер.
- Інформація повинна передаватися через telegram бота до групи в telegram.

## Звіт з учасників групи в telegram

### Завдання

- Забезпечити можливість користувачеві telegram об'єднати його обліковий запис з обліковим записом Trello.
- При натисканні на кнопку "Звіт" бот повинен показати звіт по учасникам групи telegram - скільки завдань у роботі.
