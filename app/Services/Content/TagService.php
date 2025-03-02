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

    public function getPopularTags(): array
    {
        $recentPosts = Post::query()
            ->latest()
            ->take(15)
            ->with('tags')
            ->get();

        $tagCounts = [];

        foreach ($recentPosts as $post) {
            foreach ($post->tags as $tag) {
                $tagSlug = $tag->slug;

                if (!isset($tagCounts[$tagSlug])) {
                    $tagCounts[$tagSlug] = [
                        'name' => $tag->name,
                        'slug' => $tagSlug,
                        'count' => 0,
                    ];
                }

                $tagCounts[$tagSlug]['count']++;
            }
        }

        uasort($tagCounts, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return array_values(array_slice($tagCounts, 0, 10));
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
        if ($tag) {
            $tag->delete();
        }
    }
}
