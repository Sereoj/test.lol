<?php

namespace App\Services\Content;

use App\Models\Categories\Category;
use App\Services\Base\SimpleService;
use App\Utils\TextUtil;
use Exception;

class CategoryService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'category';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 120;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('CategoryService');
    }

    /**
     * Получить все категории
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategory()
    {
        $cacheKey = $this->buildCacheKey('all_categories');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех категорий");
            return Category::all();
        });
    }

    /**
     * Создать новую категорию
     *
     * @param array $data Данные категории
     * @return Category
     */
    public function createCategory(array $data)
    {
        $this->logInfo("Создание новой категории", ['name' => $data['name']['en'] ?? 'not set']);
        
        return $this->transaction(function () use ($data) {
            try {
                $count = Category::query()->where('name', $data['name'])->count();
                
                $slug = TextUtil::generateUniqueSlug($data['name']['en'], $count);
                
                $category = Category::query()->create([
                    'meta' => TextUtil::defaultMeta(),
                    'name' => json_encode($data['name']),
                    'description' => $data['description'] ?? null,
                    'slug' => $slug,
                ]);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('all_categories'));
                
                $this->logInfo("Категория успешно создана", [
                    'category_id' => $category->id,
                    'slug' => $slug
                ]);
                
                return $category;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании категории", [
                    'name' => $data['name'] ?? 'not set'
                ], $e);
                
                throw new Exception("Не удалось создать категорию: " . $e->getMessage());
            }
        });
    }

    /**
     * Получить категорию по ID
     *
     * @param int $id ID категории
     * @return Category
     * @throws Exception
     */
    public function getCategoryById($id)
    {
        $cacheKey = $this->buildCacheKey('category', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение категории по ID", ['category_id' => $id]);
            
            try {
                return Category::query()->findOrFail($id);
            } catch (Exception $e) {
                $this->logWarning("Категория не найдена", ['category_id' => $id]);
                throw new Exception("Категория с ID {$id} не найдена");
            }
        });
    }

    /**
     * Обновить категорию
     *
     * @param int $id ID категории
     * @param array $data Данные для обновления
     * @return Category
     * @throws Exception
     */
    public function updateCategory($id, array $data)
    {
        $this->logInfo("Обновление категории", ['category_id' => $id]);
        
        return $this->transaction(function () use ($id, $data) {
            try {
                $category = Category::query()->findOrFail($id);
                
                // Если имя изменилось и у нас есть поле name['en'], обновляем slug
                if (isset($data['name']) && 
                    isset($data['name']['en']) && 
                    (!isset($category->name['en']) || $category->name['en'] !== $data['name']['en'])) {
                    
                    $count = Category::query()
                        ->where('id', '!=', $id)
                        ->where('name', 'like', '%' . $data['name']['en'] . '%')
                        ->count();
                    
                    $data['slug'] = TextUtil::generateUniqueSlug($data['name']['en'], $count);
                }
                
                $category->update($data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('category', [$id]));
                $this->forgetCache($this->buildCacheKey('all_categories'));
                
                $this->logInfo("Категория успешно обновлена", ['category_id' => $id]);
                
                return $category;
            } catch (Exception $e) {
                $this->logError("Ошибка при обновлении категории", ['category_id' => $id], $e);
                throw new Exception("Не удалось обновить категорию: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить категорию
     *
     * @param int $id ID категории
     * @return bool
     * @throws Exception
     */
    public function deleteCategory($id)
    {
        $this->logInfo("Удаление категории", ['category_id' => $id]);
        
        return $this->transaction(function () use ($id) {
            try {
                $category = Category::query()->findOrFail($id);
                
                $result = $category->delete();
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('category', [$id]));
                $this->forgetCache($this->buildCacheKey('all_categories'));
                
                $this->logInfo("Категория успешно удалена", ['category_id' => $id]);
                
                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении категории", ['category_id' => $id], $e);
                throw new Exception("Не удалось удалить категорию: " . $e->getMessage());
            }
        });
    }
    
    /**
     * Поиск категорий по имени
     *
     * @param string $query Поисковый запрос
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchCategories(string $query)
    {
        $cacheKey = $this->buildCacheKey('search_categories', [$query]);
        
        return $this->getFromCacheOrStore($cacheKey, 30, function () use ($query) {
            $this->logInfo("Поиск категорий", ['query' => $query]);
            
            return Category::query()
                ->where('name->en', 'like', "%{$query}%")
                ->orWhere('name->ru', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->get();
        });
    }
    
    /**
     * Получить категории с постами
     *
     * @param int $limit Лимит категорий
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesWithPosts(int $limit = 10)
    {
        $cacheKey = $this->buildCacheKey('categories_with_posts', [$limit]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($limit) {
            $this->logInfo("Получение категорий с постами", ['limit' => $limit]);
            
            return Category::query()
                ->withCount('posts')
                ->having('posts_count', '>', 0)
                ->orderBy('posts_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }
}
