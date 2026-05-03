<?php

namespace App\Services\Posts;

use App\Repositories\PostReportRepository;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\Auth;

class PostReportService
{
    use LoggableTrait;

    protected PostReportRepository $postReportRepository;

    public function __construct(PostReportRepository $postReportRepository)
    {
        $this->postReportRepository = $postReportRepository;
    }

    public function reportPost($postId, string $category, string $reason)
    {
        $userId = Auth::id();

        $this->logInfo('Создание жалобы на пост', [
            'post_id' => $postId,
            'user_id' => $userId,
            'category' => $category
        ]);

        try {
            $report = $this->postReportRepository->createReport($postId, $userId, $category, $reason);

            $this->logInfo('Жалоба на пост успешно создана', [
                'report_id' => $report->id,
                'post_id' => $postId
            ]);

            return $report;
        } catch (\Exception $e) {
            $this->logError('Не удалось создать жалобу на пост', [
                'post_id' => $postId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], $e);

            throw $e;
        }
    }
}
