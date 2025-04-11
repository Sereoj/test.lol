<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Настройки
 * @description Управление настройками системы
 */
class SettingsController extends Controller
{
    /**
     * Получение настроек системы
     * 
     * Возвращает текущие настройки системы.
     *
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "general": {
     *             "site_name": "Название сайта",
     *             "site_description": "Описание сайта",
     *             "site_url": "https://example.com",
     *             "admin_email": "admin@example.com",
     *             "timezone": "Europe/Moscow",
     *             "date_format": "d.m.Y",
     *             "time_format": "H:i"
     *         },
     *         "seo": {
     *             "meta_title": "Мета-заголовок сайта",
     *             "meta_description": "Мета-описание сайта",
     *             "meta_keywords": "ключевые, слова, сайта",
     *             "robots_txt": "User-agent: *\nDisallow: /admin\nSitemap: https://example.com/sitemap.xml",
     *             "google_analytics_id": "UA-XXXXX-Y",
     *             "yandex_metrika_id": "12345678"
     *         },
     *         "mail": {
     *             "mail_driver": "smtp",
     *             "mail_host": "smtp.example.com",
     *             "mail_port": 587,
     *             "mail_username": "noreply@example.com",
     *             "mail_encryption": "tls"
     *         },
     *         "security": {
     *             "enable_recaptcha": true,
     *             "recaptcha_site_key": "XXXXXXXXXXXXXXXX",
     *             "max_login_attempts": 5,
     *             "password_min_length": 8,
     *             "password_require_special_chars": true,
     *             "password_require_numbers": true,
     *             "password_require_uppercase": true
     *         },
     *         "content": {
     *             "enable_comments": true,
     *             "moderate_comments": true,
     *             "posts_per_page": 10,
     *             "comments_per_page": 20,
     *             "enable_user_registration": true,
     *             "require_email_verification": true
     *         },
     *         "social": {
     *             "facebook_url": "https://facebook.com/example",
     *             "twitter_url": "https://twitter.com/example",
     *             "instagram_url": "https://instagram.com/example",
     *             "enable_social_login": true,
     *             "enable_facebook_login": true,
     *             "enable_google_login": true,
     *             "enable_twitter_login": false
     *         },
     *         "system": {
     *             "maintenance_mode": false,
     *             "debug_mode": false,
     *             "log_level": "error",
     *             "enable_caching": true,
     *             "cache_driver": "redis",
     *             "session_driver": "redis",
     *             "queue_driver": "redis"
     *         }
     *     }
     * }
     */
    public function index()
    {
        // Здесь должен быть код для получения настроек системы
        return response()->json([
            'success' => true,
            'data' => [
                'general' => [
                    'site_name' => 'Название сайта',
                    'site_description' => 'Описание сайта',
                    'site_url' => 'https://example.com',
                    'admin_email' => 'admin@example.com',
                    'timezone' => 'Europe/Moscow',
                    'date_format' => 'd.m.Y',
                    'time_format' => 'H:i'
                ],
                'seo' => [
                    'meta_title' => 'Мета-заголовок сайта',
                    'meta_description' => 'Мета-описание сайта',
                    'meta_keywords' => 'ключевые, слова, сайта',
                    'robots_txt' => "User-agent: *\nDisallow: /admin\nSitemap: https://example.com/sitemap.xml",
                    'google_analytics_id' => 'UA-XXXXX-Y',
                    'yandex_metrika_id' => '12345678'
                ],
                'mail' => [
                    'mail_driver' => 'smtp',
                    'mail_host' => 'smtp.example.com',
                    'mail_port' => 587,
                    'mail_username' => 'noreply@example.com',
                    'mail_encryption' => 'tls'
                ],
                'security' => [
                    'enable_recaptcha' => true,
                    'recaptcha_site_key' => 'XXXXXXXXXXXXXXXX',
                    'max_login_attempts' => 5,
                    'password_min_length' => 8,
                    'password_require_special_chars' => true,
                    'password_require_numbers' => true,
                    'password_require_uppercase' => true
                ],
                'content' => [
                    'enable_comments' => true,
                    'moderate_comments' => true,
                    'posts_per_page' => 10,
                    'comments_per_page' => 20,
                    'enable_user_registration' => true,
                    'require_email_verification' => true
                ],
                'social' => [
                    'facebook_url' => 'https://facebook.com/example',
                    'twitter_url' => 'https://twitter.com/example',
                    'instagram_url' => 'https://instagram.com/example',
                    'enable_social_login' => true,
                    'enable_facebook_login' => true,
                    'enable_google_login' => true,
                    'enable_twitter_login' => false
                ],
                'system' => [
                    'maintenance_mode' => false,
                    'debug_mode' => false,
                    'log_level' => 'error',
                    'enable_caching' => true,
                    'cache_driver' => 'redis',
                    'session_driver' => 'redis',
                    'queue_driver' => 'redis'
                ]
            ]
        ]);
    }

    /**
     * Обновление настроек системы
     * 
     * Обновляет настройки системы.
     *
     * @bodyParam general object Общие настройки сайта
     * @bodyParam general.site_name string Название сайта. Example: Новое название сайта
     * @bodyParam general.site_description string Описание сайта. Example: Новое описание сайта
     * @bodyParam general.site_url string URL сайта. Example: https://new-example.com
     * @bodyParam general.admin_email string Email администратора. Example: admin@new-example.com
     * @bodyParam general.timezone string Временная зона. Example: Europe/Moscow
     * @bodyParam general.date_format string Формат даты. Example: d.m.Y
     * @bodyParam general.time_format string Формат времени. Example: H:i
     * 
     * @bodyParam seo object SEO настройки
     * @bodyParam seo.meta_title string Мета-заголовок сайта. Example: Новый мета-заголовок
     * @bodyParam seo.meta_description string Мета-описание сайта. Example: Новое мета-описание
     * @bodyParam seo.meta_keywords string Ключевые слова. Example: новые, ключевые, слова
     * @bodyParam seo.robots_txt string Содержимое robots.txt. Example: User-agent: *\nDisallow: /admin\nSitemap: https://new-example.com/sitemap.xml
     * @bodyParam seo.google_analytics_id string ID Google Analytics. Example: UA-ZZZZZ-Y
     * @bodyParam seo.yandex_metrika_id string ID Яндекс Метрики. Example: 87654321
     * 
     * @bodyParam mail object Настройки почты
     * @bodyParam mail.mail_driver string Драйвер почты. Example: mailgun
     * @bodyParam mail.mail_host string Хост почтового сервера. Example: smtp.mailgun.org
     * @bodyParam mail.mail_port integer Порт почтового сервера. Example: 587
     * @bodyParam mail.mail_username string Имя пользователя. Example: postmaster@mailgun.example.com
     * @bodyParam mail.mail_encryption string Тип шифрования. Example: tls
     * 
     * @bodyParam security object Настройки безопасности
     * @bodyParam security.enable_recaptcha boolean Включить reCAPTCHA. Example: true
     * @bodyParam security.recaptcha_site_key string Ключ сайта reCAPTCHA. Example: YYYYYYYYYYYY
     * @bodyParam security.max_login_attempts integer Максимальное количество попыток входа. Example: 3
     * @bodyParam security.password_min_length integer Минимальная длина пароля. Example: 10
     * @bodyParam security.password_require_special_chars boolean Требовать специальные символы в пароле. Example: true
     * @bodyParam security.password_require_numbers boolean Требовать цифры в пароле. Example: true
     * @bodyParam security.password_require_uppercase boolean Требовать заглавные буквы в пароле. Example: true
     * 
     * @bodyParam content object Настройки контента
     * @bodyParam content.enable_comments boolean Разрешить комментарии. Example: true
     * @bodyParam content.moderate_comments boolean Модерировать комментарии. Example: false
     * @bodyParam content.posts_per_page integer Количество постов на странице. Example: 15
     * @bodyParam content.comments_per_page integer Количество комментариев на странице. Example: 30
     * @bodyParam content.enable_user_registration boolean Разрешить регистрацию пользователей. Example: true
     * @bodyParam content.require_email_verification boolean Требовать подтверждение email. Example: true
     * 
     * @bodyParam social object Настройки социальных сетей
     * @bodyParam social.facebook_url string URL Facebook. Example: https://facebook.com/new-example
     * @bodyParam social.twitter_url string URL Twitter. Example: https://twitter.com/new-example
     * @bodyParam social.instagram_url string URL Instagram. Example: https://instagram.com/new-example
     * @bodyParam social.enable_social_login boolean Разрешить вход через соцсети. Example: true
     * @bodyParam social.enable_facebook_login boolean Разрешить вход через Facebook. Example: true
     * @bodyParam social.enable_google_login boolean Разрешить вход через Google. Example: true
     * @bodyParam social.enable_twitter_login boolean Разрешить вход через Twitter. Example: true
     * 
     * @bodyParam system object Системные настройки
     * @bodyParam system.maintenance_mode boolean Режим обслуживания. Example: false
     * @bodyParam system.debug_mode boolean Режим отладки. Example: false
     * @bodyParam system.log_level string Уровень логирования. Example: error
     * @bodyParam system.enable_caching boolean Включить кеширование. Example: true
     * @bodyParam system.cache_driver string Драйвер кеширования. Example: redis
     * @bodyParam system.session_driver string Драйвер сессий. Example: redis
     * @bodyParam system.queue_driver string Драйвер очередей. Example: redis
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Настройки успешно обновлены"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "general.admin_email": ["Некорректный email"],
     *         "security.max_login_attempts": ["Значение должно быть целым числом"]
     *     }
     * }
     */
    public function update(Request $request)
    {
        // Здесь должен быть код для обновления настроек системы
        return response()->json([
            'success' => true,
            'message' => 'Настройки успешно обновлены'
        ]);
    }

    /**
     * Очистка кеша
     * 
     * Очищает различные типы кеша в системе.
     *
     * @authenticated
     * 
     * @response status=200 scenario="успешная очистка" {
     *     "success": true,
     *     "message": "Кеш успешно очищен",
     *     "data": {
     *         "cleared": [
     *             "application",
     *             "views",
     *             "routes",
     *             "config"
     *         ]
     *     }
     * }
     */
    public function clearCache()
    {
        // Здесь должен быть код для очистки кеша
        return response()->json([
            'success' => true,
            'message' => 'Кеш успешно очищен',
            'data' => [
                'cleared' => [
                    'application',
                    'views',
                    'routes',
                    'config'
                ]
            ]
        ]);
    }

    /**
     * Управление режимом обслуживания
     * 
     * Включает или выключает режим обслуживания сайта.
     *
     * @urlParam status string required Статус режима обслуживания (on, off). Example: on
     * 
     * @bodyParam message string Сообщение для пользователей в режиме обслуживания. Example: Сайт временно недоступен из-за технических работ
     * @bodyParam allowed_ips array Список IP-адресов, которым разрешен доступ в режиме обслуживания. Example: ["127.0.0.1", "192.168.1.1"]
     * 
     * @authenticated
     * 
     * @response status=200 scenario="включение режима" {
     *     "success": true,
     *     "message": "Режим обслуживания включен",
     *     "data": {
     *         "maintenance_mode": true,
     *         "allowed_ips": ["127.0.0.1", "192.168.1.1"],
     *         "message": "Сайт временно недоступен из-за технических работ"
     *     }
     * }
     * 
     * @response status=200 scenario="выключение режима" {
     *     "success": true,
     *     "message": "Режим обслуживания выключен",
     *     "data": {
     *         "maintenance_mode": false
     *     }
     * }
     * 
     * @response status=422 scenario="неверный параметр" {
     *     "success": false,
     *     "message": "Неверный параметр статуса",
     *     "errors": {
     *         "status": ["Параметр status должен быть 'on' или 'off'"]
     *     }
     * }
     */
    public function setMaintenanceMode(Request $request, $status)
    {
        // Здесь должен быть код для включения/выключения режима обслуживания
        if ($status == 'on') {
            return response()->json([
                'success' => true,
                'message' => 'Режим обслуживания включен',
                'data' => [
                    'maintenance_mode' => true,
                    'allowed_ips' => ['127.0.0.1', '192.168.1.1'],
                    'message' => 'Сайт временно недоступен из-за технических работ'
                ]
            ]);
        } else if ($status == 'off') {
            return response()->json([
                'success' => true,
                'message' => 'Режим обслуживания выключен',
                'data' => [
                    'maintenance_mode' => false
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Неверный параметр статуса',
                'errors' => [
                    'status' => ["Параметр status должен быть 'on' или 'off'"]
                ]
            ], 422);
        }
    }
} 