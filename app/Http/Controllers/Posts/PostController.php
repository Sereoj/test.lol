<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Posts\PostResource;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @group Посты
 *
 * API для управления постами (публикациями) пользователей
 */
class PostController extends Controller
{
    protected PostService $postService;
    
    private const CACHE_SECONDS = 30;
    private const CACHE_SECONDS_POST = 2;
    private const CACHE_KEY_POSTS = 'posts_';
    private const CACHE_KEY_POST = 'post_';

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Получение ленты постов
     * 
     * Возвращает список постов с пагинацией, который можно фильтровать по различным параметрам.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @queryParam page integer Номер страницы для пагинации. Example: 1
     * @queryParam per_page integer Количество элементов на странице. Example: 10
     * @queryParam category_id integer ID категории для фильтрации постов. Example: 1
     * @queryParam tag_id integer ID тега для фильтрации постов. Example: 2
     * @queryParam sort string Поле для сортировки (created_at, likes_count, comments_count). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * 
     * @response {
     *  "success": true,
     *  "data": [
     *    {
     *      "id": 1,
     *      "title": "Заголовок поста",
     *      "slug": "zagolovok-posta",
     *      "content": "Содержание поста...",
     *      "media": [
     *        {
     *          "type": "image",
     *          "src": "uploads/posts/image1.jpg"
     *        }
     *      ],
     *      "user": {
     *        "username": "johndoe",
     *        "slug": "johndoe",
     *        "verification": false,
     *        "avatar": {
     *          "path": "avatars/default.png"
     *        }
     *      },
     *      "is_adult_content": false,
     *      "is_nsfl_content": false,
     *      "is_free": true,
     *      "has_copyright": false,
     *      "created_at": "2023-03-23T12:00:00Z",
     *      "updated_at": "2023-03-23T12:00:00Z"
     *    }
     *  ],
     *  "pagination": {
     *    "total": 100,
     *    "per_page": 10,
     *    "current_page": 1,
     *    "last_page": 10
     *  }
     * }
     */
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $cacheKey = self::CACHE_KEY_POSTS . md5(json_encode($request->all()).'_'.$userId);

        $posts = $this->getFromCacheOrStore($cacheKey, self::CACHE_SECONDS / 60, function () use ($request, $userId) {
            return $this->postService->getPosts($request->all(), $userId);
        });

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $total = $posts->total();
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage
            ]
        ]);
    }

    /**
     * Создание нового поста
     * 
     * Создание новой публикации с возможностью загрузки медиафайлов.
     *
     * @param StorePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @bodyParam content string required Содержимое поста. Example: Это мой новый пост!
     * @bodyParam media file[] Медиафайлы для поста (изображения/видео). Максимум 10 файлов.
     * @bodyParam is_adult_content boolean Флаг содержимого для взрослых. Example: false
     * @bodyParam is_nsfl_content boolean Флаг шокирующего содержимого. Example: false
     * @bodyParam has_copyright boolean Флаг наличия авторских прав. Example: false
     * 
     * @response 201 {
     *  "success": true,
     *  "data": {
     *    "id": 1,
     *    "title": null,
     *    "slug": "post-1679554801",
     *    "content": "Это мой новый пост!",
     *    "media": [
     *      {
     *        "type": "image",
     *        "src": "uploads/posts/image1.jpg"
     *      }
     *    ],
     *    "user": {
     *      "username": "johndoe",
     *      "slug": "johndoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "is_adult_content": false,
     *    "is_nsfl_content": false,
     *    "is_free": true,
     *    "has_copyright": false,
     *    "created_at": "2023-03-23T12:00:00Z",
     *    "updated_at": "2023-03-23T12:00:00Z"
     *  },
     *  "message": "Пост успешно создан"
     * }
     * 
     * @response 422 {
     *  "success": false,
     *  "message": "Ошибка валидации",
     *  "errors": {
     *    "content": ["Содержимое поста обязательно к заполнению."]
     *  }
     * }
     */
    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());
        $this->forgetCache(self::CACHE_KEY_POSTS . md5(json_encode($request->all())));

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
            'message' => 'Пост успешно создан'
        ], 201);
    }

    /**
     * Получение отдельного поста
     * 
     * Получение детальной информации о конкретном посте по его ID.
     *
     * @param int $id ID поста
     * @return \Illuminate\Http\JsonResponse
     * 
     * @urlParam id required ID поста. Example: 1
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "id": 1,
     *    "title": "Заголовок поста",
     *    "slug": "zagolovok-posta",
     *    "content": "Содержание поста...",
     *    "media": [
     *      {
     *        "type": "image",
     *        "src": "uploads/posts/image1.jpg"
     *      }
     *    ],
     *    "user": {
     *      "username": "johndoe",
     *      "slug": "johndoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "is_adult_content": false,
     *    "is_nsfl_content": false,
     *    "is_free": true,
     *    "has_copyright": false,
     *    "created_at": "2023-03-23T12:00:00Z",
     *    "updated_at": "2023-03-23T12:00:00Z"
     *  }
     * }
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Пост не найден"
     * }
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_POST . $id;

        $post = $this->getFromCacheOrStore($cacheKey, self::CACHE_SECONDS_POST / 60, function () use ($id) {
            return new PostResource($this->postService->getPost($id));
        });

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Обновление поста
     * 
     * Обновление существующего поста.
     *
     * @param UpdatePostRequest $request
     * @param int $id ID поста
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @urlParam id required ID поста. Example: 1
     * @bodyParam content string Новое содержимое поста. Example: Обновленное содержание поста!
     * @bodyParam is_adult_content boolean Флаг содержимого для взрослых. Example: true
     * @bodyParam is_nsfl_content boolean Флаг шокирующего содержимого. Example: false
     * @bodyParam has_copyright boolean Флаг наличия авторских прав. Example: true
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "id": 1,
     *    "title": "Заголовок поста",
     *    "slug": "zagolovok-posta",
     *    "content": "Обновленное содержание поста!",
     *    "media": [
     *      {
     *        "type": "image",
     *        "src": "uploads/posts/image1.jpg"
     *      }
     *    ],
     *    "user": {
     *      "username": "johndoe",
     *      "slug": "johndoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "is_adult_content": true,
     *    "is_nsfl_content": false,
     *    "is_free": true,
     *    "has_copyright": true,
     *    "created_at": "2023-03-23T12:00:00Z",
     *    "updated_at": "2023-03-24T10:30:00Z"
     *  },
     *  "message": "Пост успешно обновлен"
     * }
     * 
     * @response 403 {
     *  "success": false,
     *  "message": "У вас нет прав для редактирования этого поста"
     * }
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Пост не найден"
     * }
     */
    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
            'message' => 'Пост успешно обновлен'
        ]);
    }

    /**
     * Удаление поста
     * 
     * Удаление существующего поста.
     *
     * @param int $id ID поста
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @urlParam id required ID поста. Example: 1
     * 
     * @response {
     *  "success": true,
     *  "message": "Пост успешно удален"
     * }
     * 
     * @response 403 {
     *  "success": false,
     *  "message": "У вас нет прав для удаления этого поста"
     * }
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Пост не найден"
     * }
     */
    public function destroy(int $id)
    {
        $this->postService->deletePost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'message' => 'Пост успешно удален'
        ]);
    }

    /**
     * Установка/снятие лайка с поста
     * 
     * Добавление или удаление лайка с поста.
     *
     * @param int $id ID поста
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @urlParam id required ID поста. Example: 1
     * @bodyParam action string required Действие - like или dislike. Example: like
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "likes_count": 42
     *  },
     *  "message": "Лайк успешно добавлен"
     * }
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Пост не найден"
     * }
     */
    public function toggleLike(int $id, Request $request)
    {
        $post = [];
        $action = $request->input('action', 'like');
        $message = '';

        if ($action === 'like') {
            $post = $this->postService->likePost($id);
            $message = 'Лайк успешно добавлен';
        } elseif ($action === 'dislike') {
            $post = $this->postService->unlikePost($id);
            $message = 'Лайк успешно удален';
        }
        
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'data' => [
                'likes_count' => $post->statistics->likes_count ?? 0
            ],
            'message' => $message
        ]);
    }

    /**
     * Репост публикации
     * 
     * Создание репоста существующей публикации.
     *
     * @param int $id ID оригинального поста
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @urlParam id required ID оригинального поста. Example: 1
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "id": 2,
     *    "title": "Заголовок поста",
     *    "slug": "repost-zagolovok-posta",
     *    "content": "Содержание поста...",
     *    "media": [
     *      {
     *        "type": "image",
     *        "src": "uploads/posts/image1.jpg"
     *      }
     *    ],
     *    "user": {
     *      "username": "currentuser",
     *      "slug": "currentuser",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "original_post": {
     *      "id": 1,
     *      "user": {
     *        "username": "johndoe",
     *        "slug": "johndoe"
     *      }
     *    },
     *    "is_adult_content": false,
     *    "is_nsfl_content": false,
     *    "is_free": true,
     *    "has_copyright": false,
     *    "created_at": "2023-03-24T15:00:00Z",
     *    "updated_at": "2023-03-24T15:00:00Z"
     *  },
     *  "message": "Репост успешно создан"
     * }
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Пост не найден"
     * }
     */
    public function repost(int $id)
    {
        $post = $this->postService->repostPost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
            'message' => 'Репост успешно создан'
        ]);
    }

    /**
     * Скачивание медиафайлов поста
     * 
     * Скачивание медиафайла, прикрепленного к посту.
     *
     * @param Request $request
     * @param int $id ID поста
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     * 
     * @urlParam id required ID поста. Example: 1
     * @queryParam media string ID или индекс медиафайла для скачивания. Example: 0
     * 
     * @response 404 {
     *  "success": false,
     *  "message": "Нет доступных медиа"
     * }
     */
    public function download(Request $request, int $id)
    {
        $fileResponse = $this->postService->download($id, $request->input('media'));

        if (!$fileResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступных медиа'
            ], 404);
        }

        return $fileResponse;
    }
}
