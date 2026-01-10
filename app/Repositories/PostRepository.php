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
use function App\Helpers\sanitizeText;
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
                'post_statistics.likes_count',
                'post_statistics.downloads_count',
                'post_statistics.views_count',
                'post_statistics.comments_count',
                'post_statistics.reposts_count',
                'post_statistics.purchases_count',
                DB::raw('
                    (COALESCE(post_statistics.likes_count, 0) * 3 +
                     COALESCE(post_statistics.downloads_count, 0) * 5 +
                     COALESCE(post_statistics.views_count, 0) * 1) as relevance_score
                '),
            ])
            ->leftJoin('post_statistics', 'posts.id', '=', 'post_statistics.post_id');

        // Определение стратегии сортировки с учетом приоритета
        // Приоритет 1: параметр sort из запроса
        $sortStrategy = $filters['sort'] ?? null;

        // Учет предпочтений пользователя
        if ($userId) {
            $user = User::find($userId);
            $userPreferences = optional($user->userSettings)->preferences_feed ?? null;
            $recentInteractions = Interaction::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->pluck('post_id')
                ->toArray();

            $query->when(!empty($recentInteractions), function ($query) use ($recentInteractions) {
                $query->whereNotIn('posts.id', $recentInteractions);
            });

            // Приоритет 2: настройки пользователя (если не передан sort в запросе)
            if (!$sortStrategy) {
                $sortStrategy = $userPreferences;
            }
        }

        // Приоритет 3: popularity по умолчанию
        $sortStrategy = $sortStrategy ?? 'popularity';

        // Применение стратегии сортировки через сервис
        app(SortingService::class)->apply($query, $sortStrategy);

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
            'post_statistics.comments_count',
            'post_statistics.reposts_count',
            'post_statistics.purchases_count',
        ])->paginate($perPage, ['*'], 'page', $pageOffset + 1);
    }

    public function getPost($id)
    {
        $query = Post::with(\App\Store\PostRelations::getPostWithCollaborators());

        if (is_numeric($id)) {
            return $query->where('id', $id)->firstOrFail();
        }

        return $query->where('slug', $id)->firstOrFail();
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
                'content' => sanitizeText($data['content']),
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

            // Синхронизация соавторов
            if (isset($data['collaborator_ids']) && is_array($data['collaborator_ids'])) {
                $collaboratorData = collect($data['collaborator_ids'])->mapWithKeys(function ($userId, $index) {
                    return [$userId => ['sort_order' => $index]];
                });
                $post->collaborators()->sync($collaboratorData);
            }

            $post->statistics()->create(['post_id' => $post->id]);

            event(new PostPublished($post));

            return $post->load(['user', 'category', 'media', 'tags', 'apps', 'statistics']);
        });
    }

    public function updatePost($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $query = Post::query();
            $post = is_numeric($id) ? $query->findOrFail($id) : $query->where('slug', $id)->firstOrFail();

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

            // Синхронизация соавторов
            if (isset($data['collaborator_ids']) && is_array($data['collaborator_ids'])) {
                $collaboratorData = collect($data['collaborator_ids'])->mapWithKeys(function ($userId, $index) {
                    return [$userId => ['sort_order' => $index]];
                });
                $post->collaborators()->sync($collaboratorData);
            }

            return $post->load(['user', 'category', 'media', 'tags', 'apps', 'statistics']);
        });
    }

    public function deletePost($id): void
    {
        $query = Post::query();
        $post = is_numeric($id) ? $query->findOrFail($id) : $query->where('slug', $id)->firstOrFail();
        $post->delete();
    }
}
