<?php

namespace App\Services;

use App\Models\Category;
use App\Utils\TextUtil;

class CategoryService
{
    public function getAllCategory()
    {
        return Category::all();
    }

    public function createCategory(array $data)
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

    public function getCategoryById($id)
    {
        return Category::query()->findOrFail($id);
    }

    public function updateCategory($id, array $data)
    {
        $artwork = Category::query()->findOrFail($id);
        $artwork->update($data);

        return $artwork;
    }

    public function deleteCategory($id)
    {
        $artwork = Category::query()->findOrFail($id);
        $artwork->delete();
    }
}
