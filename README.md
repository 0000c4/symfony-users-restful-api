# REST API Управление пользователями

Чистый и простой RESTful API для управления пользователями, построенный на Symfony 7.

## Обзор

Это приложение предоставляет удобный API для управления пользователями с базовыми CRUD-операциями. Оно следует принципам REST, использует Doctrine ORM для взаимодействия с базой данных и включает полное тестовое покрытие.

## Функции

- **Эндпоинты RESTful API**:
  - Получение всех пользователей
  - Создание нового пользователя
  - Удаление пользователя

- **Валидация данных**:
  - Проверка имени и электронной почты
  - Уникальный адрес электронной почты
  - 
## Требования

- PHP 8.2+
- MySQL
- Composer

## Установка

1. Клонировать репозиторий:
   ```bash
   git clone https://github.com/yourusername/user-management-api.git
   cd user-management-api
   ```

2. Установить зависимости:
   ```bash
   composer install
   ```

3. Настроить базу данных в `.env`:
   ```
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/user_management?serverVersion=8.0"
   ```

4. Создать базу данных и выполнить миграции:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

## Использование

Запустить сервер:
```bash
symfony server:start
```

### Конечные точки API

- **GET /api/users** - Получение всех пользователей
- **POST /api/users** - Создание нового пользователя
  ```json
  {
    "name": "Иван Иванов",
    "email": "ivan@example.com"
  }
  ```
- **DELETE /api/users/{id}** - Удаление пользователя по ID

## Тестирование

Запустить набор тестов:
```bash
php bin/phpunit
```

## Структура проекта

```
src/
├── Controller/
│       └── UserController.php
├── DTO/
│   └── UserDTO.php
├── Entity/
│   └── User.php
├── Repository/
│   └── UserRepository.php
└── Service/
    └── UserService.php
```

## Лицензия

MIT
