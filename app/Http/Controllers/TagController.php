<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Services\TagService;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller
{
    protected TagService $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Display a listing of the tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Попытка получить данные из кеша
        $tags = Cache::get('tags');

        // Если кеш пуст, извлекаем данные из базы и сохраняем их в кеш
        if (!$tags) {
            $tags = $this->tagService->getAllTags();
            Cache::put('tags', $tags, now()->addMinutes(10)); // Кешируем на 10 минут
        }

        return response()->json($tags);
    }

    /**
     * Store a newly created tag in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag($request->all());

        // Очистка кеша после добавления нового тега
        Cache::forget('tags');

        return response()->json($tag, 201);
    }

    /**
     * Display the specified tag.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Попытка получить данные из кеша
        $cacheKey = 'tag_' . $id;
        $tag = Cache::get($cacheKey);

        if (!$tag) {
            // Если кеш пуст, извлекаем данные из базы и сохраняем в кеш
            $tag = $this->tagService->getTagById($id);
            Cache::put($cacheKey, $tag, now()->addMinutes(10)); // Кешируем на 10 минут
        }

        return response()->json($tag);
    }

    /**
     * Update the specified tag in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTagRequest $request, $id)
    {
        $tag = $this->tagService->updateTag($id, $request->all());

        // Очистка кеша после обновления тега
        Cache::forget('tags');
        Cache::forget('tag_' . $id);

        return response()->json($tag);
    }

    /**
     * Remove the specified tag from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $this->tagService->deleteTag($id);

        // Очистка кеша после удаления тега
        Cache::forget('tags');
        Cache::forget('tag_' . $id);

        return response()->json(null, 204);
    }
}
