<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\ReportCommentRequest;
use App\Services\Comments\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comments\CommentResource;

/**
 * @group Комментарии
 *
 * API для работы с комментариями пользователей к публикациям
 */
class CommentController extends Controller
{
    protected CommentService $commentService;

    private const CACHE_KEY_COMMENTS = 'comments_post_';
    private const CACHE_MINUTES = 60;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Получение комментариев к посту
     *
     * Возвращает список комментариев для указанного поста с пагинацией.
     *
     * @param Request $request
     * @param int $postId ID поста
     * @return \Illuminate\Http\JsonResponse
     *
     * @urlParam postId required ID поста. Example: 1
     * @queryParam page integer Номер страницы для пагинации. Example: 1
     * @queryParam per_page integer Количество элементов на странице. Example: 10
     * @queryParam sort string Поле для сортировки (created_at, likes_count). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     *
     * @response {
     *  "success": true,
     *  "data": [
     *    {
     *      "id": 1,
     *      "content": "Отличный пост!",
     *      "user": {
     *        "id": 2,
     *        "username": "janedoe",
     *        "verification": false,
     *        "avatar": {
     *          "path": "avatars/user2.png"
     *        }
     *      },
     *      "likes_count": 5,
     *      "replies_count": 2,
     *      "created_at": "2023-03-23T14:30:00Z",
     *      "updated_at": "2023-03-23T14:30:00Z",
     *      "is_liked": false
     *    }
     *  ],
     *  "pagination": {
     *    "total": 30,
     *    "per_page": 10,
     *    "current_page": 1,
     *    "last_page": 3
     *  }
     * }
     */
    public function index(Request $request, int $postId)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');

        $comments = $this->commentService->getCommentsForPost($postId, $page, $perPage, $sortBy, $order);

        $total = $comments->total();
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => $comments->items(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage
            ]
        ]);
    }

    /**
     * Получение ответов на комментарий
     *
     * Возвращает список ответов на указанный комментарий с пагинацией.
     *
     * @param Request $request
     * @param int $commentId ID родительского комментария
     * @return \Illuminate\Http\JsonResponse
     *
     * @urlParam commentId required ID комментария. Example: 1
     * @queryParam page integer Номер страницы для пагинации. Example: 1
     * @queryParam per_page integer Количество элементов на странице. Example: 10
     *
     * @response {
     *  "success": true,
     *  "data": [
     *    {
     *      "id": 3,
     *      "content": "Полностью согласен!",
     *      "user": {
     *        "id": 3,
     *        "username": "alexsmith",
     *        "verification": true,
     *        "avatar": {
     *          "path": "avatars/user3.png"
     *        }
     *      },
     *      "parent_id": 1,
     *      "likes_count": 2,
     *      "created_at": "2023-03-23T15:10:00Z",
     *      "updated_at": "2023-03-23T15:10:00Z",
     *      "is_liked": false
     *    }
     *  ],
     *  "pagination": {
     *    "total": 2,
     *    "per_page": 10,
     *    "current_page": 1,
     *    "last_page": 1
     *  }
     * }
     */
    public function getReplies(Request $request, int $commentId)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $replies = $this->commentService->getCommentReplies($commentId, $request->all(), $userId);

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $total = $replies->total();
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => $replies->items(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage
            ]
        ]);
    }

    /**
     * Добавление комментария
     *
     * Создание нового комментария к посту или ответа на существующий комментарий.
     *
     * @param StoreCommentRequest $request
     * @param int $postId ID поста
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam postId required ID поста. Example: 1
     * @bodyParam parent_id integer ID родительского комментария (если это ответ на комментарий). Example: 5
     * @bodyParam content string required Текст комментария. Example: Очень информативный пост, спасибо!
     *
     * @response 201 {
     *  "success": true,
     *  "data": {
     *    "id": 10,
     *    "content": "Очень информативный пост, спасибо!",
     *    "user": {
     *      "id": 1,
     *      "username": "johndoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "post_id": 1,
     *    "parent_id": null,
     *    "likes_count": 0,
     *    "created_at": "2023-03-24T10:15:00Z",
     *    "updated_at": "2023-03-24T10:15:00Z",
     *    "is_liked": false
     *  },
     *  "message": "Комментарий успешно добавлен"
     * }
     *
     * @response 422 {
     *  "success": false,
     *  "message": "Ошибка валидации",
     *  "errors": {
     *    "content": ["Текст комментария обязателен."]
     *  }
     * }
     */
    public function store(StoreCommentRequest $request, int $postId)
    {
        $data = $request->validated();
        $comment = $this->commentService->createComment($postId, $data);

        if (isset($comment['message'])) {
            return response()->json([
                'success' => false,
                'message' => $comment['message']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CommentResource($comment),
            'message' => 'Комментарий успешно добавлен'
        ], 201);
    }

    /**
     * Получение одного комментария
     *
     * @param int $postId ID поста
     * @param int $id ID комментария
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($postId, $id)
    {
        try {
            $comment = $this->commentService->findCommentById($id);

            if (!$comment || $comment->post_id != $postId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Комментарий не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new CommentResource($comment)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении комментария: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Установка/снятие лайка с комментария
     *
     * Добавление или удаление лайка с комментария.
     *
     * @param int $id ID комментария
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID комментария. Example: 10
     * @bodyParam action string required Действие - like или dislike. Example: like
     *
     * @response {
     *  "success": true,
     *  "data": {
     *    "likes_count": 6
     *  },
     *  "message": "Лайк успешно добавлен"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Комментарий не найден"
     * }
     */
    public function toggleLike(int $id, Request $request)
    {
        $action = $request->input('action', 'like');

        $result = $this->commentService->reactToComment($id, $action);

        if (isset($result['message']) && $result['message'] !== 'Comment not found.') {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 404);
        }

        $message = $action === 'like' ? 'Лайк успешно добавлен' : 'Лайк успешно удален';
        $likesCount = $result['likes_count'] ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'likes_count' => $likesCount
            ],
            'message' => $message
        ]);
    }

    /**
     * Отправка жалобы на комментарий
     *
     * @param ReportCommentRequest $request
     * @param int $postId ID поста
     * @param int $commentId ID комментария
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     */
    public function report(ReportCommentRequest $request, $postId, $commentId)
    {
        $result = $this->commentService->reportComment($commentId, $request->input('reason'));

        if (isset($result['message']) && $result['message'] === 'Comment not found.') {
            return response()->json([
                'success' => false,
                'message' => 'Комментарий не найден'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Жалоба успешно отправлена'
        ]);
    }

    /**
     * Репост комментария
     *
     * @param int $postId ID поста
     * @param int $commentId ID комментария
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     */
    public function repost($postId, $commentId)
    {
        $result = $this->commentService->repostComment($commentId);

        if (isset($result['message']) && $result['message'] === 'Comment not found.') {
            return response()->json([
                'success' => false,
                'message' => 'Комментарий не найден'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Комментарий успешно репостнут'
        ]);
    }

    /**
     * Обновление комментария
     *
     * Редактирование существующего комментария.
     *
     * @param UpdateCommentRequest $request
     * @param int $id ID комментария
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID комментария. Example: 10
     * @bodyParam content string required Новый текст комментария. Example: Обновленный текст комментария!
     *
     * @response {
     *  "success": true,
     *  "data": {
     *    "id": 10,
     *    "content": "Обновленный текст комментария!",
     *    "user": {
     *      "id": 1,
     *      "username": "johndoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "post_id": 1,
     *    "parent_id": null,
     *    "likes_count": 0,
     *    "created_at": "2023-03-24T10:15:00Z",
     *    "updated_at": "2023-03-24T10:20:00Z",
     *    "is_liked": false
     *  },
     *  "message": "Комментарий успешно обновлен"
     * }
     *
     * @response 403 {
     *  "success": false,
     *  "message": "У вас нет прав для редактирования этого комментария"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Комментарий не найден"
     * }
     */
    public function update(UpdateCommentRequest $request, int $id)
    {
        $result = $this->commentService->updateComment($id, $request->validated());

        if (isset($result['message'])) {
            $status = $result['message'] === 'Comment not found.' ? 404 : 403;
            $message = $result['message'] === 'Comment not found.' ? 'Комментарий не найден' : 'У вас нет прав для редактирования этого комментария';

            return response()->json([
                'success' => false,
                'message' => $message
            ], $status);
        }

        return response()->json([
            'success' => true,
            'data' => new CommentResource($result),
            'message' => 'Комментарий успешно обновлен'
        ]);
    }

    /**
     * Удаление комментария
     *
     * Удаление существующего комментария.
     *
     * @param int $id ID комментария
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID комментария. Example: 10
     *
     * @response {
     *  "success": true,
     *  "message": "Комментарий успешно удален"
     * }
     *
     * @response 403 {
     *  "success": false,
     *  "message": "У вас нет прав для удаления этого комментария"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Комментарий не найден"
     * }
     */
    public function destroy(int $id)
    {
        $result = $this->commentService->deleteComment($id);

        if (isset($result['message'])) {
            $status = $result['message'] === 'Comment not found.' ? 404 : 403;
            $message = $result['message'] === 'Comment not found.' ? 'Комментарий не найден' : 'У вас нет прав для удаления этого комментария';

            return response()->json([
                'success' => false,
                'message' => $message
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => 'Комментарий успешно удален'
        ]);
    }
}
