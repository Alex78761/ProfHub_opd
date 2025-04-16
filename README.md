<<<<<<< HEAD
# ProfHub - Платформа для онлайн-консультаций

## Описание
ProfHub - это веб-приложение для организации онлайн-консультаций между пользователями и консультантами. 
Платформа обеспечивает удобный интерфейс для общения в реальном времени.

## Основные функции
- Регистрация пользователей и консультантов
- Система аутентификации и авторизации
- Чат в реальном времени
- Управление статусами консультаций
- Просмотр истории консультаций

## Технологии
- Spring Boot
- Spring Security
- Thymeleaf
- H2 Database
- Bootstrap 5
- JavaScript

## Установка и запуск
1. Клонируйте репозиторий
```bash
git clone https://github.com/Alex78761/ProfHub_opd.git
```

2. Перейдите в директорию проекта
```bash
cd ProfHub_opd
```

3. Запустите приложение
```bash
./mvnw spring-boot:run
```

4. Откройте браузер и перейдите по адресу `http://localhost:8080`

## Учетные записи по умолчанию
- Администратор: 
  - Логин: admin
  - Пароль: admin123
- Консультант:
  - Логин: consultant
  - Пароль: consultant123

## Структура проекта
```
/
├── assets/           # Статические файлы (CSS, JS, изображения)
├── config/           # Конфигурационные файлы
├── includes/         # PHP включаемые файлы
├── modules/          # Модули системы
├── templates/        # Шаблоны страниц
└── public/           # Публичная директория
``` 