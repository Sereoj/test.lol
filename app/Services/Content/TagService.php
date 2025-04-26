<?php

namespace App\Services\Content;

use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Utils\TextUtil;

class TagService
{
    public function getAllTags()
    {
        return Tag::all();
    }

    public function getPopularTags()
    {
        $tagCounts = Tag::query()
            ->select('tags.*')
            ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
            ->join('posts', 'posts.id', '=', 'post_tag.post_id')
            ->whereNull('posts.deleted_at') // если soft deletes есть
            ->orderBy('posts.created_at', 'desc')
            ->limit(15)
            ->get()
            ->groupBy('slug')
            ->map(function ($tags) {
                $firstTag = $tags->first();
                $firstTag->count = $tags->count();
                return $firstTag;
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        return $tagCounts;
    }

    public function getTagById($id)
    {
        return Tag::find($id);
    }

    public function createTag(array $data)
    {
        $slugPreview = str()->slug($data['name']['en']);

        $existingTag = Tag::query()->where('name->en', $data['name']['en'])->first();

        if ($existingTag) {
            return $existingTag;
        }

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

        if (!$tag) {
            return null;
        }

        if ($tag->name['en'] !== $data['name']['en']) {
            $slugPreview = str()->slug($data['name']['en']);
            $count = Tag::query()->where('slug', 'like', '%'.$slugPreview.'%')
                ->where('id', '!=', $id)
                ->count();
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
        $tag?->delete();
    }
}
