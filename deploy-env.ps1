# =============================================================================
# Скрипт для безопасного деплоя .env файла на production сервер (PowerShell)
# =============================================================================

param(
    [switch]$CheckOnly,
    [switch]$NoRestart,
    [switch]$Rollback,
    [switch]$Help
)

# Цвета для вывода
function Write-Info { Write-Host "ℹ $args" -ForegroundColor Blue }
function Write-Success { Write-Host "✓ $args" -ForegroundColor Green }
function Write-Warning { Write-Host "⚠ $args" -ForegroundColor Yellow }
function Write-Error { Write-Host "✗ $args" -ForegroundColor Red }

# =============================================================================
# КОНФИГУРАЦИЯ - ИЗМЕНИТЕ ПОД ВАШИ ДАННЫЕ
# =============================================================================

$SERVER_USER = "your_username"                    # Имя пользователя на сервере
$SERVER_HOST = "your_server_ip"                   # IP адрес или домен сервера
$SERVER_PORT = "22"                                # SSH порт
$SSH_KEY_PATH = "$env:USERPROFILE\.ssh\id_rsa"   # Путь к SSH ключу

# Пути на сервере
$REMOTE_PROJECT_PATH = "/var/www/wallone"
$REMOTE_ENV_PATH = "$REMOTE_PROJECT_PATH/.env"
$REMOTE_BACKUP_PATH = "$REMOTE_PROJECT_PATH/.env.backup"

# Локальный путь к .env
$LOCAL_ENV_PATH = ".env"

# Docker Compose команды
$DOCKER_COMPOSE_FILE = "docker-compose.prod.yml"

# =============================================================================
# ФУНКЦИИ
# =============================================================================

function Show-Help {
    @"
Использование: .\deploy-env.ps1 [ОПЦИИ]

Скрипт для безопасного деплоя .env файла на production сервер

ОПЦИИ:
    -Help               Показать эту справку
    -Rollback           Откатить к предыдущей версии .env
    -CheckOnly          Только проверить подключение, без загрузки
    -NoRestart          Не перезапускать контейнеры после загрузки

КОНФИГУРАЦИЯ:
    Отредактируйте переменные в начале скрипта:
    - SERVER_USER       Имя пользователя на сервере
    - SERVER_HOST       IP адрес или домен сервера
    - SERVER_PORT       SSH порт
    - SSH_KEY_PATH      Путь к SSH ключу

ПРИМЕРЫ:
    .\deploy-env.ps1                  # Обычный деплой
    .\deploy-env.ps1 -CheckOnly       # Проверка без загрузки
    .\deploy-env.ps1 -Rollback        # Откат к предыдущей версии

ТРЕБОВАНИЯ:
    - PowerShell 5.0 или выше
    - Posh-SSH модуль (установка: Install-Module -Name Posh-SSH)
    - SSH доступ к серверу с ключом

"@
}

function Test-PoshSSH {
    if (-not (Get-Module -ListAvailable -Name Posh-SSH)) {
        Write-Error "Модуль Posh-SSH не установлен!"
        Write-Info "Установите модуль командой:"
        Write-Host "  Install-Module -Name Posh-SSH -Scope CurrentUser" -ForegroundColor Cyan
        exit 1
    }
    Import-Module Posh-SSH -ErrorAction Stop
}

function Test-LocalEnv {
    Write-Info "Проверка локального .env файла..."

    if (-not (Test-Path $LOCAL_ENV_PATH)) {
        Write-Error "Файл $LOCAL_ENV_PATH не найден!"
        exit 1
    }

    # Проверка наличия критичных переменных
    $content = Get-Content $LOCAL_ENV_PATH -Raw
    $requiredVars = @("APP_KEY", "DB_HOST", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD")
    $missingVars = @()

    foreach ($var in $requiredVars) {
        if ($content -notmatch "^$var=") {
            $missingVars += $var
        }
    }

    if ($missingVars.Count -gt 0) {
        Write-Error "Отсутствуют критичные переменные в .env:"
        $missingVars | ForEach-Object { Write-Host "  - $_" }
        exit 1
    }

    Write-Success "Локальный .env файл корректен"
}

function Test-SSHConnection {
    Write-Info "Проверка SSH подключения к серверу..."

    try {
        $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
        $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential -ErrorAction Stop

        if ($session) {
            Remove-SSHSession -SessionId $session.SessionId | Out-Null
            Write-Success "SSH подключение успешно"
            return $true
        }
    }
    catch {
        Write-Error "Не удалось подключиться к серверу!"
        Write-Info "Проверьте параметры SSH:"
        Write-Host "  User: $SERVER_USER"
        Write-Host "  Host: $SERVER_HOST"
        Write-Host "  Port: $SERVER_PORT"
        Write-Host "  Key:  $SSH_KEY_PATH"
        Write-Host "  Ошибка: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }

    return $false
}

function Backup-RemoteEnv {
    Write-Info "Создание бэкапа текущего .env на сервере..."

    $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
    $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

    $command = @"
if [ -f "$REMOTE_ENV_PATH" ]; then
    cp "$REMOTE_ENV_PATH" "$REMOTE_BACKUP_PATH"
    echo "Бэкап создан: $REMOTE_BACKUP_PATH"
else
    echo "Текущий .env не найден, бэкап не создан"
fi
"@

    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $command
    Write-Host $result.Output

    Remove-SSHSession -SessionId $session.SessionId | Out-Null
    Write-Success "Бэкап создан"
}

function Upload-Env {
    Write-Info "Загрузка .env на сервер..."

    try {
        $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
        $session = New-SFTPSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

        Set-SFTPFile -SessionId $session.SessionId -LocalFile $LOCAL_ENV_PATH -RemotePath $REMOTE_ENV_PATH -Overwrite

        Remove-SFTPSession -SessionId $session.SessionId | Out-Null
        Write-Success "Файл .env успешно загружен"
    }
    catch {
        Write-Error "Ошибка при загрузке .env: $($_.Exception.Message)"
        exit 1
    }
}

function Set-RemotePermissions {
    Write-Info "Установка прав доступа..."

    $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
    $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

    $command = @"
chmod 600 "$REMOTE_ENV_PATH"
chown www-data:www-data "$REMOTE_ENV_PATH" 2>/dev/null || true
"@

    Invoke-SSHCommand -SessionId $session.SessionId -Command $command | Out-Null

    Remove-SSHSession -SessionId $session.SessionId | Out-Null
    Write-Success "Права доступа установлены (600)"
}

function Test-RemoteEnv {
    Write-Info "Проверка .env на сервере..."

    $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
    $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

    $command = @"
if [ -f "$REMOTE_ENV_PATH" ]; then
    echo "✓ Файл существует"
    echo "Размер: `$(stat -c%s "$REMOTE_ENV_PATH") байт"
    echo "Права: `$(stat -c%a "$REMOTE_ENV_PATH")"
else
    echo "✗ Файл не найден!"
    exit 1
fi
"@

    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $command
    Write-Host $result.Output

    Remove-SSHSession -SessionId $session.SessionId | Out-Null

    if ($result.ExitStatus -eq 0) {
        Write-Success "Файл .env успешно загружен и проверен"
    }
    else {
        Write-Error "Ошибка проверки файла"
        exit 1
    }
}

function Restart-Containers {
    Write-Warning "Требуется перезапуск Docker контейнеров для применения изменений"

    $response = Read-Host "Перезапустить контейнеры сейчас? (y/n)"

    if ($response -eq 'y' -or $response -eq 'Y') {
        Write-Info "Перезапуск контейнеров..."

        $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
        $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

        $command = @"
cd "$REMOTE_PROJECT_PATH"
docker-compose -f $DOCKER_COMPOSE_FILE down
docker-compose -f $DOCKER_COMPOSE_FILE up -d
echo "Контейнеры перезапущены"
"@

        $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $command
        Write-Host $result.Output

        Remove-SSHSession -SessionId $session.SessionId | Out-Null
        Write-Success "Контейнеры успешно перезапущены"

        # Показать логи
        $showLogs = Read-Host "Показать логи контейнеров? (y/n)"
        if ($showLogs -eq 'y' -or $showLogs -eq 'Y') {
            $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential
            $command = "cd $REMOTE_PROJECT_PATH && docker-compose -f $DOCKER_COMPOSE_FILE logs --tail=50"
            $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $command
            Write-Host $result.Output
            Remove-SSHSession -SessionId $session.SessionId | Out-Null
        }
    }
    else {
        Write-Warning "Не забудьте перезапустить контейнеры вручную:"
        Write-Host "  ssh $SERVER_USER@$SERVER_HOST"
        Write-Host "  cd $REMOTE_PROJECT_PATH"
        Write-Host "  docker-compose -f $DOCKER_COMPOSE_FILE down"
        Write-Host "  docker-compose -f $DOCKER_COMPOSE_FILE up -d"
    }
}

function Invoke-Rollback {
    Write-Warning "Откат к предыдущей версии .env..."

    $credential = New-Object System.Management.Automation.PSCredential($SERVER_USER, (new-object System.Security.SecureString))
    $session = New-SSHSession -ComputerName $SERVER_HOST -Port $SERVER_PORT -KeyFile $SSH_KEY_PATH -Credential $credential

    $command = @"
if [ -f "$REMOTE_BACKUP_PATH" ]; then
    cp "$REMOTE_BACKUP_PATH" "$REMOTE_ENV_PATH"
    echo "✓ Откат выполнен"
else
    echo "✗ Файл бэкапа не найден!"
    exit 1
fi
"@

    $result = Invoke-SSHCommand -SessionId $session.SessionId -Command $command
    Write-Host $result.Output

    Remove-SSHSession -SessionId $session.SessionId | Out-Null

    if ($result.ExitStatus -eq 0) {
        Write-Success "Откат выполнен успешно"
        Restart-Containers
    }
    else {
        Write-Error "Ошибка при откате"
        exit 1
    }
}

# =============================================================================
# ОСНОВНАЯ ЛОГИКА
# =============================================================================

if ($Help) {
    Show-Help
    exit 0
}

Write-Host "═══════════════════════════════════════════════════"
Write-Host "  🚀 Деплой .env на production сервер"
Write-Host "═══════════════════════════════════════════════════"
Write-Host ""

# Проверка модуля Posh-SSH
Test-PoshSSH

# Откат
if ($Rollback) {
    Test-SSHConnection
    Invoke-Rollback
    exit 0
}

# Основной процесс
Test-LocalEnv
Test-SSHConnection

if ($CheckOnly) {
    Write-Success "Проверка завершена успешно"
    exit 0
}

# Подтверждение
Write-Warning "Вы собираетесь загрузить .env на production сервер:"
Write-Host "  Сервер: $SERVER_USER@$SERVER_HOST"
Write-Host "  Путь:   $REMOTE_ENV_PATH"
Write-Host ""
$confirmation = Read-Host "Продолжить? (yes/no)"

if ($confirmation -ne "yes") {
    Write-Info "Отменено пользователем"
    exit 0
}

# Деплой
Backup-RemoteEnv
Upload-Env
Set-RemotePermissions
Test-RemoteEnv

if (-not $NoRestart) {
    Restart-Containers
}
else {
    Write-Warning "Контейнеры не перезапущены (флаг -NoRestart)"
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════"
Write-Success "Деплой .env завершен успешно!"
Write-Host "═══════════════════════════════════════════════════"
