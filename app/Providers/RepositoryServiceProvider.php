<?php

namespace App\Providers;

use App\Repositories\Avatar\AvatarRepository;
use App\Repositories\Comments\CommentRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Posts\PostRepository;
use App\Repositories\RepositoryInterface;
use App\Repositories\SourceRepository;
use App\Repositories\Users\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов приложения.
     */
    public function register()
    {
        // Регистрация репозиториев
        $this->app->bind(RepositoryInterface::class . 'User', UserRepository::class);
        $this->app->bind(RepositoryInterface::class . 'Post', PostRepository::class);
        $this->app->bind(RepositoryInterface::class . 'Comment', CommentRepository::class);
        $this->app->bind(RepositoryInterface::class . 'Media', MediaRepository::class);
        $this->app->bind(RepositoryInterface::class . 'Avatar', AvatarRepository::class);
        $this->app->bind(RepositoryInterface::class . 'Source', SourceRepository::class);
        $this->app->bind(SourceRepository::class, SourceRepository::class);
        $this->app->bind(PostRepository::class, PostRepository::class);
        $this->app->bind(UserRepository::class, UserRepository::class);
        $this->app->bind(CommentRepository::class, CommentRepository::class);
        $this->app->bind(MediaRepository::class, MediaRepository::class);
        $this->app->bind(AvatarRepository::class, AvatarRepository::class);
    }

    /**
     * Загрузка сервисов для приложения.
     */
    public function boot()
    {
        //
    }
}
