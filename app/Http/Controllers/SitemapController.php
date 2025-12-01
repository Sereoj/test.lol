<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    protected SitemapService $sitemapService;
    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    public function index()
    {
        try {
            return $this->successResponse($this->sitemapService->generateUrls());
        } catch (\Exception $exception)
        {
            Log::error('Sitemap: ' . $exception->getMessage(), [
                'message' => $exception->getMessage(),
            ]);
            return $this->errorResponse($exception->getMessage());
        }
    }
}
