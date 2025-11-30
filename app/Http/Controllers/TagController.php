<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Http\Resources\Tag\TagShortResource;
use App\Services\Content\TagService;
use OpenApi\Attributes as OA;

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
     * @OA\Put(
     *     path="/api/v1/tags/{tag}",
     *     tags={"Tags"},
     *     summary="Update tag",
     *     description="Update tag",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *         description="Tag",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTagRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Tag")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
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
