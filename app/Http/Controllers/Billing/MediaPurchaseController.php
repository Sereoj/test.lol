<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\MediaPurchaseService;
use App\Repositories\MediaRepository;
use App\Services\Media\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class MediaPurchaseController extends Controller
{
    protected MediaPurchaseService $mediaPurchaseService;
    protected MediaRepository $mediaRepository;

    public function __construct(
        MediaPurchaseService $mediaPurchaseService,
        MediaRepository $mediaRepository
    ) {
        $this->mediaPurchaseService = $mediaPurchaseService;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * Покупка исходного файла медиа
     */
    public function purchaseMediaSource(Request $request, int $mediaId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'currency' => 'required|string|max:3',
            ]);

            $userId = Auth::id();

            $purchase = $this->mediaPurchaseService->purchaseMediaSource(
                $mediaId,
                $validated['currency']
            );

            $this->logInfo('Успешная покупка исходника медиа', [
                'user_id' => $userId,
                'media_id' => $mediaId,
                'purchase_id' => $purchase->id,
            ]);

            return $this->successResponse($purchase, [], 201);
        } catch (ValidationException $e) {
            $this->logWarning('Ошибка валидации при покупке исходника медиа', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'media_id' => $mediaId,
            ]);
            return $this->errorResponse(json_encode($e->errors()), 422);
        } catch (Exception $e) {
            $this->logError('Ошибка при покупке исходника медиа', [
                'user_id' => Auth::id(),
                'media_id' => $mediaId,
            ], $e);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Получить список покупок медиа пользователя
     */
    public function getUserMediaPurchases(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'page' => 'sometimes|integer|min:1',
                'limit' => 'sometimes|integer|min:1|max:100',
            ]);

            $userId = Auth::id();
            $page = $validated['page'] ?? 1;
            $limit = $validated['limit'] ?? 10;

            $result = $this->mediaPurchaseService->getUserMediaPurchases($userId, $page, $limit);

            return $this->successResponse(
                $result['data'],
                [
                    'total' => $result['total'],
                    'limit' => $result['limit'],
                    'offset' => $result['offset'],
                    'current_page' => $page,
                ],
                200
            );
        } catch (ValidationException $e) {
            $this->logWarning('Ошибка валидации при получении покупок медиа', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);
            return $this->errorResponse(json_encode($e->errors()), 422);
        } catch (Exception $e) {
            $this->logError('Ошибка при получении покупок медиа пользователя', [
                'user_id' => Auth::id(),
            ], $e);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Скачать купленный исходник медиа
     */
    public function downloadSource(int $mediaId): JsonResponse
    {
        try {
            $userId = Auth::id();

            // Проверяем, куплен ли исходник пользователем
            $isPurchased = $this->mediaPurchaseService->isMediaSourcePurchasedByUser($mediaId, $userId);

            if (!$isPurchased) {
                $this->logWarning('Попытка скачать неоплаченный исходник', [
                    'user_id' => $userId,
                    'media_id' => $mediaId,
                ]);
                return $this->errorResponse('Исходник не был куплен.', 403);
            }

            // Получаем медиа
            $media = $this->mediaRepository->getById($mediaId);

            // Проверяем, что исходник существует
            if (!$media->has_source || !$media->source_file_path) {
                $this->logError('Исходник не найден', [
                    'user_id' => $userId,
                    'media_id' => $mediaId,
                ]);
                return $this->errorResponse('Исходник не найден.', 404);
            }

            // Получаем URL исходника
            $sourceUrl = StorageService::getPath($media->source_file_path, $media->disk);

            $this->logInfo('Скачивание исходника медиа', [
                'user_id' => $userId,
                'media_id' => $mediaId,
            ]);

            return $this->successResponse([
                'url' => $sourceUrl,
                'filename' => basename($media->source_file_path),
            ], [], 200);
        } catch (Exception $e) {
            $this->logError('Ошибка при скачивании исходника медиа', [
                'user_id' => Auth::id(),
                'media_id' => $mediaId,
            ], $e);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
