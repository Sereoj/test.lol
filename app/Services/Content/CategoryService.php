<?php

namespace App\Services\Content;

use App\Models\Categories\Category;
use App\Services\BaseService;
use App\Utils\TextUtil;

class CategoryService extends BaseService
{
    public function getAll()
    {
        return Category::all();
    }

    public function create(array $data)
    {
        $count = Category::query()->where('name', $data['name'])->count();

        $slug = TextUtil::generateUniqueSlug($data['name']['en'], $count);

        return Category::query()->create([
            'meta' => TextUtil::defaultMeta(),
            'name' => json_encode($data['name']),
            'description' => $data['description'] ?? null,
            'slug' => $slug,
        ]);
    }

    public function getById($id)
    {
        return Category::query()->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $artwork = Category::query()->findOrFail($id);
        $artwork->update($data);

        return $artwork;
    }

    public function delete($id)
    {
        $artwork = Category::query()->findOrFail($id);

        return $artwork->delete();
    }
}
