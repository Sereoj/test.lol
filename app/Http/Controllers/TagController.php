<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Services\TagService;

class TagController extends Controller
{
    protected TagService $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function index()
    {
        $tags = $this->tagService->getAllTags();

        return response()->json($tags);
    }

    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag($request->all());

        return response()->json($tag, 201);
    }

    public function show($id)
    {
        $tag = $this->tagService->getTagById($id);

        return response()->json($tag);
    }

    public function update(UpdateTagRequest $request, $id)
    {
        $tag = $this->tagService->updateTag($id, $request->all());

        return response()->json($tag);
    }

    public function destroy($id)
    {
        $this->tagService->deleteTag($id);

        return response()->json(null, 204);
    }
}
