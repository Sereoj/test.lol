<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\Tag\TagShortResource;
use App\Services\Content\TagService;

// Контроллер для работы с тегами
class TagController extends Controller
{
    protected TagService $tagService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_TAGS = 'tags';
    private const CACHE_KEY_TAG = 'tag_';

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Получение списка всех тегов
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tags = $this->getFromCacheOrStore(self::CACHE_KEY_TAGS, self::CACHE_MINUTES, function () {
            return TagShortResource::collection($this->tagService->getAllTags());
        });

        return $this->successResponse($tags);
    }

    public function popularTags()
    {
        $popular = $this->getFromCacheOrStore('popular_tags', self::CACHE_MINUTES, function () {
           return TagShortResource::collection($this->tagService->getPopularTags());
        });

        return $this->successResponse($popular);
    }

    /**
     * Создание нового тега
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag($request->all());

        $this->forgetCache(self::CACHE_KEY_TAGS);

        return $this->successResponse($tag, [], 201);
    }

    /**
     * Получение конкретного тега
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_TAG . $id;

        $tag = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
            return $this->tagService->getTagById($id);
        });

        return $this->successResponse($tag);
    }

    /**
     * Обновление конкретного тега
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTagRequest $request, $id)
    {
        $tag = $this->tagService->updateTag($id, $request->all());

        $this->forgetCache([
            self::CACHE_KEY_TAGS,
            self::CACHE_KEY_TAG . $id
        ]);

        return $this->successResponse($tag);
    }

    /**
     * Удаление конкретного тега
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->tagService->deleteTag($id);

        $this->forgetCache([
            self::CACHE_KEY_TAGS,
            self::CACHE_KEY_TAG . $id
        ]);

        return $this->successResponse(null, [], 204);
    }
}
