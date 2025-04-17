<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\Tags\TagShortResource;
use App\Services\Content\TagService;
use Illuminate\Support\Facades\Cache;

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
     * Display a listing of the tags.
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

    /**
     * Store a newly created tag in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag($request->all());

        $this->forgetCache(self::CACHE_KEY_TAGS);

        return $this->successResponse($tag, 201);
    }

    /**
     * Display the specified tag.
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
     * Update the specified tag in storage.
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
     * Remove the specified tag from storage.
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

        return $this->successResponse(null, 204);
    }
}
