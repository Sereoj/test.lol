<?php

namespace App\Services\Content;

use App\Models\Content\Tag;
use App\Models\Posts\Post;
use App\Services\Base\SimpleService;
use App\Utils\TextUtil;
use Exception;

class TagService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'tag';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('TagService');
    }

    /**
     * Получить все теги
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags()
    {
        $cacheKey = $this->buildCacheKey('all_tags');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех тегов");
            return Tag::all();
        });
    }

    /**
     * Получить популярные теги
     *
     * @return array
     */
    public function getPopularTags(): array
    {
        $cacheKey = $this->buildCacheKey('popular_tags');
        
        return $this->getFromCacheOrStore($cacheKey, 30, function () {
            $this->logInfo("Получение популярных тегов");
            
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
        });
    }

    /**
     * Получить тег по ID
     *
     * @param int $id ID тега
     * @return Tag|null
     */
    public function getTagById($id)
    {
        $cacheKey = $this->buildCacheKey('tag', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение тега по ID", ['tag_id' => $id]);
            return Tag::find($id);
        });
    }

    /**
     * Создать новый тег
     *
     * @param array $data Данные тега
     * @return Tag
     */
    public function createTag(array $data)
    {
        $this->logInfo("Создание нового тега", ['name' => $data['name']['en']]);
        
        return $this->transaction(function () use ($data) {
            $slugPreview = str()->slug($data['name']['en']);

            $existingTag = Tag::query()->where('name->en', $data['name']['en'])->first();

            if ($existingTag) {
                $this->logInfo("Найден существующий тег с таким именем", [
                    'name' => $data['name']['en'],
                    'id' => $existingTag->id
                ]);
                return $existingTag;
            }

            $count = Tag::query()->where('slug', 'like', '%'.$slugPreview.'%')->count();
            $slug = TextUtil::generateUniqueSlug($slugPreview, $count);

            $tag = Tag::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'meta' => TextUtil::defaultMeta(),
            ]);
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('all_tags'));
            $this->forgetCache($this->buildCacheKey('popular_tags'));
            
            $this->logInfo("Тег успешно создан", [
                'tag_id' => $tag->id,
                'slug' => $slug
            ]);
            
            return $tag;
        });
    }

    /**
     * Обновить тег
     *
     * @param int $id ID тега
     * @param array $data Данные для обновления
     * @return Tag|null
     */
    public function updateTag($id, array $data)
    {
        $this->logInfo("Обновление тега", ['tag_id' => $id]);
        
        return $this->transaction(function () use ($id, $data) {
            $tag = Tag::query()->find($id);

            if (!$tag) {
                $this->logWarning("Тег не найден при обновлении", ['tag_id' => $id]);
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
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('tag', [$id]));
            $this->forgetCache($this->buildCacheKey('all_tags'));
            $this->forgetCache($this->buildCacheKey('popular_tags'));
            
            $this->logInfo("Тег успешно обновлен", [
                'tag_id' => $tag->id,
                'slug' => $slug
            ]);

            return $tag;
        });
    }

    /**
     * Удалить тег
     *
     * @param int $id ID тега
     * @return bool
     */
    public function deleteTag($id)
    {
        $this->logInfo("Удаление тега", ['tag_id' => $id]);
        
        return $this->transaction(function () use ($id) {
            $tag = Tag::find($id);
            
            if ($tag) {
                $tag->delete();
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('tag', [$id]));
                $this->forgetCache($this->buildCacheKey('all_tags'));
                $this->forgetCache($this->buildCacheKey('popular_tags'));
                
                $this->logInfo("Тег успешно удален", ['tag_id' => $id]);
                return true;
            }
            
            $this->logWarning("Тег не найден при удалении", ['tag_id' => $id]);
            return false;
        });
    }
    
    /**
     * Поиск тегов по имени
     *
     * @param string $query Поисковый запрос
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchTags(string $query)
    {
        $cacheKey = $this->buildCacheKey('search_tags', [$query]);
        
        return $this->getFromCacheOrStore($cacheKey, 15, function () use ($query) {
            $this->logInfo("Поиск тегов", ['query' => $query]);
            
            return Tag::query()
                ->where('name->en', 'like', "%{$query}%")
                ->orWhere('name->ru', 'like', "%{$query}%")
                ->take(10)
                ->get();
        });
    }
    
    /**
     * Получить теги для поста
     *
     * @param int $postId ID поста
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTagsForPost(int $postId)
    {
        $cacheKey = $this->buildCacheKey('post_tags', [$postId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($postId) {
            $this->logInfo("Получение тегов для поста", ['post_id' => $postId]);
            
            $post = Post::with('tags')->find($postId);
            
            if (!$post) {
                $this->logWarning("Пост не найден при получении тегов", ['post_id' => $postId]);
                return collect();
            }
            
            return $post->tags;
        });
    }
}
