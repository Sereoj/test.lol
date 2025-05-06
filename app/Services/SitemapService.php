<?php

namespace App\Services;

use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Support\Facades\URL;

class SitemapService
{
    public function generateUrls(): array
    {
        $generalPages = [
            '/' => '1.0',
            '/about' => '0.8',
            '/contact' => '0.7',
        ];

        $contentPages = $this->getContentPages();
        $profilePages = $this->getProfilePages();

        $urls = [];

        foreach ($generalPages as $path => $priority) {
            $urls[] = [
                'loc' => URL::to(getenv('APP_FRONT_URL').$path),
                'lastmod' => now()->format('c'),
                'changefreq' => 'weekly',
                'priority' => $priority,
            ];
        }

        foreach ($contentPages as $page) {
            $urls[] = [
                'loc' => URL::to(getenv('APP_FRONT_URL')."/posts/{$page->slug}"),
                'lastmod' => $page->updated_at->format('c'),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        foreach ($profilePages as $user) {
            $urls[] = [
                'loc' => URL::to(getenv('APP_FRONT_URL')."/profile/{$user->slug}"),
                'lastmod' => $user->updated_at->format('c'),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return $urls;
    }

    protected function getContentPages()
    {
        return Post::withoutTrashed()->published()->get();
    }

    protected function getProfilePages()
    {
        return User::get();
    }
}
