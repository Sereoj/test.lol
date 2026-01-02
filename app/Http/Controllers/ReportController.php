<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Services\Comments\CommentService;
use App\Services\Posts\PostReportService;
use Illuminate\Support\Facades\Auth;

/**
 * @group Жалобы
 *
 * API для управления жалобами на контент
 */
class ReportController extends Controller
{
    protected PostReportService $postReportService;
    protected CommentService $commentService;

    public function __construct(PostReportService $postReportService, CommentService $commentService)
    {
        $this->postReportService = $postReportService;
        $this->commentService = $commentService;
    }

    /**
     * Отправка жалобы на контент
     *
     * Универсальный endpoint для создания жалоб на посты и комментарии
     *
     * @param ReportRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ReportRequest $request)
    {
        try {
            $contentType = $request->input('content_type');
            $contentId = $request->input('content_id');
            $category = $request->input('category');
            $reason = $request->input('reason');

            $report = match ($contentType) {
                'post' => $this->postReportService->reportPost($contentId, $category, $reason),
                'comment' => $this->commentService->reportComment($contentId, $category, $reason),
                default => throw new \Exception('Invalid content type', 400)
            };

            return $this->successResponse($report);
        } catch (\Exception $e) {
            $this->logError('Failed to create report', [
                'content_type' => $request->input('content_type'),
                'content_id' => $request->input('content_id'),
                'error' => $e->getMessage()
            ], $e);

            return $this->errorResponse($e->getMessage(), $e->getCode() != 0 ? $e->getCode() : 400);
        }
    }
}
