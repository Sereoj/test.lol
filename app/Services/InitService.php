<?php
namespace App\Services;
use App\Services\Content\TagService;

class InitService
{
    protected TagService $tagService;
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function getInfo()
    {
        return [
            'tags' => $this->tagService->getPopularTags(),
            'options' => $this->getOptions(),
            'hits' => $this->getHits(),
            'hero' => $this->getHero(),
            'backgrounds' => $this->getBackgrounds(),
            'language' => app()->getLocale(),
            'version' => getenv('APP_VERSION'),
        ];
    }

    private function getOptions(): array
    {
        return [
             [
                 'id' => 1,
                 'name' => [
                     'ru' => 'Видео',
                     'en' => 'Videos'
                 ],
             ],
             [
                'id' => 2,
                'name' => [
                    'ru' => 'Картинки',
                    'en' => 'Images'
                ],
             ],
             [
                'id' => 2,
                'name' => [
                    'ru' => 'Гифки',
                    'en' => 'Gifs'
                ],
             ],
             [
                'id' => 3,
                'name' => [
                    'ru' => 'Гифки123',
                    'en' => 'Gifs123'
                ],
             ],
        ];
    }

    private function getHits(): array
    {
        return ['hit1', 'hit2', 'hit3'];
    }

    private function getHero(): array
    {
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
    }

    private function getBackgrounds(): array
    {
        return ['background1.jpg', 'background2.jpg', 'background3.jpg'];
    }
}
