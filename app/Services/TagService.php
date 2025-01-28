<?php

namespace App\Services;

use App\Models\Tag;
use App\Utils\TextUtil;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }

    public function getTagById($id)
    {
        return Tag::find($id);
    }

    public function createTag(array $data)
    {
        $slugPreview = str()->slug($data['name']['en']);
        $count = Tag::query()->where('slug', 'like', '%'.$slugPreview.'%')->count();
        $slug = TextUtil::generateUniqueSlug($slugPreview, $count);

        return Tag::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'meta' => TextUtil::defaultMeta(),
        ]);
    }

    public function updateTag($id, array $data)
    {
        $tag = Tag::query()->find($id);

        if (! $tag) {
            return null;
        }

        if ($tag->name !== $data['name']) {
            $slugPreview = str()->slug($data['name']);
            $count = Tag::query()->where('name', 'like', '%'.$slugPreview.'%')->count();
            $slug = TextUtil::generateUniqueSlug($slugPreview, $count);
        } else {
            $slug = $tag->slug;
        }

        $tag->update([
            'name' => $data['name'],
            'slug' => $slug,
            'meta' => $data['meta'] ?? $tag->meta,
        ]);

        return $tag;
    }

    public function deleteTag($id)
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->delete();
        }
    }
}
