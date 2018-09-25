# Nanidub

### Установка

Выполните команду:

```bash
composer update
```

После этого создайте файл `.env` в корневой директории и копируйте в него содержимое файла `.env.example`

Выполните команду:

```bash
php artisan key:genarate
```

Установите свои настройки отправки почты и соединения с базой данных

Настройки `ANIDUB_LOGIN` и `ANIDUB_PASSWORD` необходимы для парсинга данных с сайта https://online.anidub.com

Для заполнения БД выполните команду:

```bash
php artisan migrate --seed
```
Все ошибки парсинга будут отражены в файле `\storage\logs\parsing.log`