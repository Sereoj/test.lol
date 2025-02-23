<?php

namespace App\Repositories;

use App\Events\PostPublished;
use App\Helpers\FileHelper;
use App\Models\Interactions\Interaction;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Services\Media\MediaService;
use App\Services\Posts\Assistants\MediaTypeFilterService;
use App\Services\Posts\Assistants\SortingService;
use App\Services\Posts\Assistants\TimeFrameFilterService;
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
        // Базовый запрос
        $query = Post::published()
            ->with(['media', 'user'])
            ->select([
                'posts.id',
                'posts.title',
                'posts.slug',
                'posts.is_adult_content',
                'posts.is_nsfl_content',
                'posts.is_free',
                'posts.has_copyright',
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

        // Учет предпочтений пользователя
        if ($userId) {
            $user = User::find($userId);
            $userPreferences = optional($user->userSettings)->preferences_feed ?? 'default';
            $recentInteractions = Interaction::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->pluck('post_id')
                ->toArray();

            $query->when(!empty($recentInteractions), function ($query) use ($recentInteractions) {
                $query->whereNotIn('posts.id', $recentInteractions);
            });

            // Применение стратегии сортировки через сервис
            app(SortingService::class)->apply($query, $userPreferences);
        } else {
            // Применение стандартной сортировки
            app(SortingService::class)->apply($query, 'default');
        }

        // Применение фильтрации по временному диапазону
        app(TimeFrameFilterService::class)->apply($query, $filters['time_frame'] ?? null);

        // Фильтрация по типам медиа
        $mediaTypes = isset($filters['media_type']) && is_string($filters['media_type'])
            ? [$filters['media_type']] // Преобразуем строку в массив
            : ($filters['media_type'] ?? []);

        app(MediaTypeFilterService::class)->apply(
            $query,
            $mediaTypes,
            $filters['filter_mode'] ?? 'or'
        );

        // Пагинация
        $perPage = $filters['per_page'] ?? 40;
        $pageOffset = $filters['page_offset'] ?? 0;

        return $query->groupBy([
            'posts.id',
            'posts.title',
            'posts.slug',
            'posts.is_adult_content',
            'posts.is_nsfl_content',
            'posts.is_free',
            'posts.has_copyright',
            'posts.user_id',
            'posts.created_at',
            'posts.updated_at',
            DB::raw('(COALESCE(post_statistics.likes_count, 0) * 3 +
                     COALESCE(post_statistics.downloads_count, 0) * 5 +
                     COALESCE(post_statistics.views_count, 0) * 1)'),
            'post_statistics.likes_count',
            'post_statistics.downloads_count',
            'post_statistics.views_count',
        ])->paginate($perPage, ['*'], 'page', $pageOffset + 1);
    }

    public function getPost(int $id)
    {
        $post = Post::with(['user', 'category', 'media', 'tags', 'apps', 'statistics'])->findOrFail($id);

        return $post;
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
                'status' => Post::STATUS_DRAFT,
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
                if (! empty($mediaTypes['images'])) {
                    event(new PostPublished($post));
                }
                if (! empty($mediaTypes['gifs'])) {
                    event(new PostPublished($post));
                }
                if (! empty($mediaTypes['videos'])) {
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
