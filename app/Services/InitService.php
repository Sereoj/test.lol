<?php
namespace App\Services;

use App\Services\Base\SimpleService;
use App\Services\Content\TagService;

/**
 * Сервис инициализации приложения
 */
class InitService extends SimpleService
{
    /**
     * Сервис для работы с тегами
     *
     * @var TagService
     */
    protected TagService $tagService;

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     *
     * @param TagService $tagService
     */
    public function __construct(TagService $tagService)
    {
        parent::__construct();
        $this->tagService = $tagService;
        $this->setLogPrefix('InitService');
    }

    /**
     * Получить информацию для инициализации
     *
     * @return array
     */
    public function getInfo()
    {
        $cacheKey = $this->buildCacheKey('app_info');

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo('Получение информации для инициализации приложения');

            return [
                'tags' => $this->tagService->getPopularTags(),
                'options' => $this->getOptions(),
                'hits' => $this->getHits(),
                'hero' => $this->getHero(),
                'backgrounds' => $this->getBackgrounds(),
                'language' => app()->getLocale(),
                'version' => getenv('APP_VERSION'),
            ];
        });
    }

    /**
     * Получить список опций
     *
     * @return array
     */
    private function getOptions(): array
    {
        return array_map(function (int $index) {
            return [
                'label' => [
                    'ru' => 'Метка ' . ($index + 1),
                    'en' => 'Label ' . ($index + 1),
                ],
                'value' => 'value-' . ($index + 1),
            ];
        }, range(0, 3));
    }

    /**
     * Получить список популярных запросов
     *
     * @return array
     */
    private function getHits(): array
    {
        return $this->safeExecute(function () {
            return ['hit1', 'hit2', 'hit3'];
        }, [], []);
    }

    /**
     * Получить данные для блока Hero
     *
     * @return array
     */
    private function getHero(): array
    {
        return $this->safeExecute(function () {
            return [
                [
                    'name' => json_encode(
                        [
                            'ru' => 'Hero 1',
                            'en' => 'Hero 1',
                        ]
                    ),
                    'src' => '/img/hero/hero-1.png',
                ],
                [
                    'name' => json_encode(
                        [
                            'ru' => 'Hero 2',
                            'en' => 'Hero 2',
                        ]
                    ),
                    'src' => '/img/hero/hero-2.png',
                ],
            ];
        }, [], []);
    }

    /**
     * Получить список фоновых изображений
     *
     * @return array
     */
    private function getBackgrounds(): array
    {
        return $this->safeExecute(function () {
            return ['background1.jpg', 'background2.jpg', 'background3.jpg'];
        }, [], []);
    }

    /**
     * Очистить кеш инициализации
     *
     * @return bool
     */
    public function clearInitCache(): bool
    {
        $this->logInfo('Очистка кеша инициализации приложения');
        return $this->forgetCache($this->buildCacheKey('app_info'));
    }
}
