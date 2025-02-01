<?php

namespace App\Repositories;

use App\Events\GifPublished;
use App\Events\ImagePublished;
use App\Events\PostPublished;
use App\Events\VideoPublished;
use App\Helpers\FileHelper;
use App\Http\Resources\PostResource;
use App\Models\Interaction;
use App\Models\Post;
use App\Models\User;
use App\Services\MediaService;
use App\Utils\TextUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostRepository
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
     {
         $this->mediaService = $mediaService;
     }

    public function getPosts(array $filters, $userId = null)
    {
        $query = Post::query()
            ->with(['media', 'user'])
            ->select([
                'posts.id',
                'posts.title',
                'posts.content',
                'posts.category_id',
                'posts.user_id',
                'posts.created_at',
                'posts.updated_at',
                DB::raw('
                    (COALESCE(post_statistics.likes_count, 0) * 3 +
                     COALESCE(post_statistics.downloads_count, 0) * 5 +
                     COALESCE(post_statistics.views_count, 0) * 1) as relevance_score
                '),
            ])
            ->leftJoin('post_statistics', 'posts.id', '=', 'post_statistics.post_id');

        if ($userId) {
            $userPreferences = optional(User::query()->find($userId)->userSettings)->preferences_feed;

            $recentInteractions = Interaction::query()
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->pluck('post_id')
                ->toArray();

            $query->when(! empty($recentInteractions), function ($query) use ($recentInteractions) {
                $query->whereNotIn('posts.id', $recentInteractions);
            });

            switch ($userPreferences) {
                case 'popularity':
                    $query->orderByRaw('
                        (
                            COALESCE(post_statistics.likes_count, 0) * 2 +
                            COALESCE(post_statistics.reposts_count, 0) * 3 +
                            COALESCE(post_statistics.comments_count, 0) * 1.5 +
                            COALESCE(post_statistics.downloads_count, 0) * 4 +
                            COALESCE(post_statistics.purchases_count, 0) * 5 +
                            COALESCE(post_statistics.views_count, 0) * 0.5
                        ) / POW(TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 1, 0.5) DESC
                    ');
                    break;
                case 'downloads':
                    $query->orderByRaw('
                        (
                            (COALESCE(post_statistics.downloads_count, 0) * 4) +
                            (COALESCE(post_statistics.likes_count, 0) * 2) +
                            (COALESCE(post_statistics.comments_count, 0) * 2) +
                            (COALESCE(post_statistics.downloads_count, 0) /
                                GREATEST(COALESCE(post_statistics.likes_count, 1), 1) * 3) +
                            (COALESCE(post_statistics.views_count, 0) * 0.5)
                        ) *
                        (1 + 1 / (TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 1)) DESC
                    ');
                    break;
                case 'likes':
                    $query->orderByRaw('
                        (
                            (COALESCE(post_statistics.likes_count, 0) * 3) +
                            (COALESCE(post_statistics.downloads_count, 0) * 2) +
                            (COALESCE(post_statistics.comments_count, 0) * 2) +
                            (COALESCE(post_statistics.likes_count, 0) /
                                GREATEST(COALESCE(post_statistics.views_count, 1), 1) * 3) +
                            (COALESCE(post_statistics.views_count, 0) * 0.5)
                        ) *
                        (1 + 1 / (TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 1)) DESC
                    ');
                    break;
                default:
                    $query->orderByDesc('relevance_score');
                    break;
            }

            $userCategories = Interaction::query()
                ->where('interactions.user_id', $userId)
                ->join('posts', 'interactions.post_id', '=', 'posts.id')
                ->distinct()
                ->pluck('posts.category_id')
                ->toArray();

            $query->when(! empty($userCategories), function ($query) use ($userCategories) {
                $query->orWhereIn('posts.category_id', $userCategories);
            });
        } else {
            $query->orderByDesc('relevance_score');
        }

        $query->when(isset($filters['time_frame']), function ($query) use ($filters) {
            $timeFrameMap = [
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            ];

            if (isset($timeFrameMap[$filters['time_frame']])) {
                $query->where('posts.created_at', '>=', $timeFrameMap[$filters['time_frame']]);
            }
        });

        $perPage = $filters['per_page'] ?? 40;
        $pageOffset = $filters['page_offset'] ?? 0;

        $query->groupBy([
            'posts.id',
            'posts.title',
            'posts.content',
            'posts.category_id',
            'posts.user_id',
            'posts.created_at',
            'posts.updated_at',
            'post_statistics.likes_count',
            'post_statistics.downloads_count',
            'post_statistics.views_count',
        ]);

        return $query->paginate($perPage, ['*'], 'page', $pageOffset + 1);
    }

    public function getPost(int $id)
    {
        $post = Post::with(['user', 'category', 'media', 'tags', 'apps', 'statistics'])->findOrFail($id);

        return new PostResource($post);
    }

    public function createPost(array $data)
    {
        $count_posts = Post::query()->where('slug', 'like', str($data['title'])->slug().'%')->count();

        return DB::transaction(function () use ($data, $count_posts) {
            $post = Post::query()->create([
                'meta' => TextUtil::defaultMeta(),
                'title' => $data['title'],
                'slug' => TextUtil::generateUniqueSlug($data['title'], $count_posts),
                'user_id' => Auth::id(),
                'content' => $data['content'],
                'status' => $data['status'] ?? Post::STATUS_DRAFT,
                'is_adult_content' => $data['is_adult_content'] ?? false,
                'is_nsfl_content' => $data['is_nsfl_content'] ?? false,
                'has_copyright' => $data['has_copyright'] ?? false,
                'price' => $data['price'],
                'is_free' => $data['is_free'] ?? true,
                'category_id' => $data['category_id'],
                'settings' => json_encode($data['settings']),
            ]);

            if (isset($data['tags_id'])) {
                $post->tags()->sync($data['tags_id']);
            }
            if (isset($data['apps_id'])) {
                $post->apps()->sync($data['apps_id']);
            }

            // Обработка медиафайлов
            if (isset($data['media'])) {
                $mediaTypes = [
                    'images' => [],
                    'gifs' => [],
                    'videos' => [],
                ];

                \Log::info($data['media']);

                foreach ($data['media'] as $mediaId) {
                    $media = $this->mediaService->getMediaById($mediaId); //FindOrFail
                    $fileType = FileHelper::determineFileType($media->mime_type); // image

                    switch ($fileType) {
                        case 'image':
                            $mediaTypes['images'][] = $media['id'];
                            break;
                        case 'gif':
                            $mediaTypes['gifs'][] = $media['id'];
                            break;
                        case 'video':
                            $mediaTypes['videos'][] = $media['id'];
                            break;
                    }
                }

                //Временное решение
                if (!empty($mediaTypes['images'])) {
                    event(new PostPublished($post));
                }
                if (!empty($mediaTypes['gifs'])) {
                    event(new PostPublished($post));
                }
                if (!empty($mediaTypes['videos'])) {
                    event(new PostPublished($post));
                }

                $post->media()->sync($data['media']);
            }

            $post->statistics()->create(['post_id' => $post->id]);

            event(new PostPublished($post));

            return $post->load(['user', 'category', 'media', 'tags', 'apps', 'statistics']);
        });
    }

    public function updatePost(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $post = Post::query()->findOrFail($id);

            $post->update(array_filter([
                'title' => $data['title'] ?? $post->title,
                'content' => $data['content'] ?? $post->content,
                'status' => $data['status'] ?? $post->status,
                'is_adult_content' => $data['is_adult_content'] ?? $post->is_adult_content,
                'is_nsfl_content' => $data['is_nsfl_content'] ?? $post->is_nsfl_content,
                'has_copyright' => $data['has_copyright'] ?? $post->has_copyright,
                'price' => $data['price'] ?? $post->price,
                'is_free' => $data['is_free'] ?? $post->is_free,
                'category_id' => $data['category_id'] ?? $post->category_id,
                'settings' => isset($data['settings']) ? json_encode($data['settings']) : $post->settings,
            ], function ($value) {
                return $value !== null;
            }));

            if ($data['title']) {
                $countPosts = Post::query()->where('slug', 'like', $data['title'].'%')->count();
                $post->slug = TextUtil::generateUniqueSlug($data['title'], $countPosts);
                $post->save();
            }

            if (isset($data['tags_id']) && is_array($data['tags_id'])) {
                $post->tags()->sync($data['tags_id']);
            }

            if (isset($data['apps_id']) && is_array($data['apps_id'])) {
                $post->apps()->sync($data['apps_id']);
            }

            if (isset($data['media']) && is_array($data['media'])) {
                $mediaData = collect($data['media'])->mapWithKeys(function ($id, $index) {
                    return [$id => ['sort_order' => $index]];
                });
                $post->media()->sync($mediaData);
            }

            return $post->load(['user', 'category', 'media', 'tags', 'apps', 'statistics']);
        });
    }

    public function deletePost(int $id): void
    {
        $post = Post::query()->findOrFail($id);
        $post->delete();
    }
}
