<?php

namespace App\Http\Controllers;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

            /**
     * @OA\Delete(
     *     path="/api/v1/notifications/{notification_id}",
     *     tags={"Notifications"},
     *     summary="Delete notification",
     *     description="Delete notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="notification_id",
     *         in="path",
     *         required=true,
     *         description="Notification id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function delete(string $id): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notification = $this->notificationService->getById($id);

            if (!$notification || $notification->user_id !== auth()->id()) {
                return $this->errorResponse('Notification not found', 404);
            }

            $result = $this->notificationService->delete($id, auth()->id());

            if ($result) {
                event(new NotificationDeleted($notification));
                return $this->successResponse('Notification deleted successfully');
            }

            return $this->errorResponse('Error deleting notification');*/
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage(), [
                'notification_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting notification');
        }
    }
}
