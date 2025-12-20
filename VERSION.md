# Система версионирования приложения

## Обзор

Приложение использует [Semantic Versioning](https://semver.org/) (SemVer) для управления версиями:
- **MAJOR** (1.0.0 → 2.0.0) - несовместимые изменения API
- **MINOR** (1.0.0 → 1.1.0) - новая функциональность с обратной совместимостью
- **PATCH** (1.0.0 → 1.0.1) - исправления ошибок с обратной совместимостью

## Где хранится версия

Версия приложения хранится в двух местах и синхронизируется автоматически:
- `package.json` - для frontend/Node.js части
- `composer.json` - для backend/PHP части

## Отображение версии

### API endpoint
```bash
GET /api/v1/version
```

Возвращает:
```json
{
  "success": true,
  "data": {
    "version": "1.0.0",
    "name": "laravel/laravel",
    "php_version": "8.2.0",
    "laravel_version": "10.x",
    "environment": "production",
    "package_version": "1.0.0"
  }
}
```

### Docker entrypoint
При запуске Docker контейнера версия автоматически отображается в логах:
```
🚀 Starting Laravel application initialization...
📦 Application Version: 1.0.0
```

## Обновление версии

### Вариант 1: Ручное обновление

Используйте npm скрипты для ручного обновления версии:

```bash
# Patch версия (1.0.0 → 1.0.1) - для исправлений
npm run version:patch

# Minor версия (1.0.0 → 1.1.0) - для новых функций
npm run version:minor

# Major версия (1.0.0 → 2.0.0) - для breaking changes
npm run version:major
```

После выполнения команды:
1. Версия обновится в `package.json` и `composer.json`
2. Создайте коммит с изменениями:
   ```bash
   git add package.json composer.json
   git commit -m "chore: bump version to X.Y.Z"
   git tag vX.Y.Z
   git push && git push --tags
   ```

### Вариант 2: Автоматический релиз (рекомендуется)

Используйте автоматический скрипт создания релиза, который анализирует коммиты с момента последнего тега и определяет тип обновления версии на основе [Conventional Commits](https://www.conventionalcommits.org/):

```bash
npm run release
```

Скрипт автоматически:
1. Анализирует коммиты с последнего тега
2. Определяет тип обновления версии:
   - `BREAKING CHANGE` или `feat!:` → **major**
   - `feat:` → **minor**
   - `fix:`, `docs:`, `style:`, `refactor:`, `perf:`, `test:`, `chore:` → **patch**
3. Обновляет версию в обоих файлах
4. Создает коммит с обновлением версии
5. Создает git тег

После выполнения команды просто запушьте изменения:
```bash
git push && git push --tags
```

## Conventional Commits

Для корректной работы автоматического версионирования используйте формат Conventional Commits:

```bash
# Patch - исправление ошибки
git commit -m "fix: исправлена ошибка входа"

# Minor - новая функция
git commit -m "feat: добавлен экспорт в CSV"

# Major - breaking change
git commit -m "feat!: изменен формат API ответа"
# или
git commit -m "feat: изменен формат API ответа

BREAKING CHANGE: формат ответа теперь включает метаданные"

# Другие типы (patch)
git commit -m "docs: обновлена документация"
git commit -m "chore: обновлены зависимости"
git commit -m "refactor: рефакторинг авторизации"
git commit -m "perf: оптимизация запросов к БД"
git commit -m "test: добавлены тесты для UserController"
```

## Примеры использования

### Пример 1: Релиз после нескольких исправлений

```bash
# Внесли несколько исправлений
git commit -m "fix: исправлена ошибка валидации"
git commit -m "fix: исправлена утечка памяти"
git commit -m "docs: обновлена документация API"

# Создаем релиз (автоматически станет 1.0.1)
npm run release

# Публикуем
git push && git push --tags
```

### Пример 2: Релиз с новой функцией

```bash
# Добавили новую функцию
git commit -m "feat: добавлена поддержка dark mode"
git commit -m "test: добавлены тесты для dark mode"

# Создаем релиз (автоматически станет 1.1.0)
npm run release

# Публикуем
git push && git push --tags
```

### Пример 3: Breaking change

```bash
# Внесли несовместимые изменения
git commit -m "feat!: изменен формат API v2

BREAKING CHANGE: все эндпоинты теперь используют новый формат ответа"

# Создаем релиз (автоматически станет 2.0.0)
npm run release

# Публикуем
git push && git push --tags
```

## Рекомендации

1. **Используйте Conventional Commits** для всех коммитов - это позволит автоматизировать версионирование
2. **Создавайте релизы регулярно** - после накопления исправлений или добавления новых функций
3. **Не забывайте пушить теги** - используйте `git push --tags` после создания релиза
4. **Проверяйте версию перед деплоем** - используйте эндпоинт `/api/v1/version`

## Интеграция с CI/CD

Вы можете настроить автоматический релиз в вашем CI/CD пайплайне:

```yaml
# Пример для GitHub Actions
- name: Create release
  run: npm run release

- name: Push changes
  run: |
    git push
    git push --tags
```

## Troubleshooting

### Версии рассинхронизированы
Если версии в `package.json` и `composer.json` рассинхронизированы, используйте скрипт обновления:
```bash
npm run version:patch
```

### Нет коммитов для релиза
Если команда `npm run release` сообщает об отсутствии коммитов, убедитесь что:
1. Вы используете Conventional Commits формат
2. Есть хотя бы один коммит с префиксом `feat:`, `fix:`, и т.д.
