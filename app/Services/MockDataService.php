<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Posts\Post;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\Users\User;

/**
 * Сервис для работы с моковыми данными в режиме разработки.
 */
class MockDataService
{
    /**
     * Получить случайного пользователя.
     */
    public function getRandomUser(): User
    {
        return User::inRandomOrder()->first();
    }

    /**
     * Получить случайного администратора.
     */
    public function getRandomAdmin(): User
    {
        return User::where('role_id', 1)->inRandomOrder()->first();
    }

    /**
     * Получить случайного верифицированного пользователя.
     */
    public function getRandomVerifiedUser(): User
    {
        return User::where('verification', true)->inRandomOrder()->first();
    }

    /**
     * Получить случайный пост.
     */
    public function getRandomPost(): Post
    {
        return Post::inRandomOrder()->first();
    }

    /**
     * Получить случайный опубликованный пост.
     */
    public function getRandomPublishedPost(): Post
    {
        return Post::where('status', 'published')->inRandomOrder()->first();
    }

    /**
     * Получить случайный платный пост.
     */
    public function getRandomPaidPost(): Post
    {
        return Post::where('is_free', false)->inRandomOrder()->first();
    }

    /**
     * Получить несколько случайных постов.
     */
    public function getRandomPosts(int $count = 5): array
    {
        return Post::inRandomOrder()->limit($count)->get()->all();
    }

    /**
     * Получить случайный комментарий.
     */
    public function getRandomComment(): Comment
    {
        return Comment::inRandomOrder()->first();
    }

    /**
     * Получить несколько случайных тегов.
     */
    public function getRandomTags(int $count = 3): array
    {
        return Tag::inRandomOrder()->limit($count)->get()->all();
    }

    /**
     * Получить случайный активный челлендж.
     */
    public function getRandomActiveChallenge(): Challenge
    {
        return Challenge::where('status', 'active')->inRandomOrder()->first();
    }

    /**
     * Получить случайную транзакцию.
     */
    public function getRandomTransaction(): Transaction
    {
        return Transaction::inRandomOrder()->first();
    }

    /**
     * Получить пользователей с их постами.
     */
    public function getUsersWithPosts(int $userCount = 5, int $postCount = 3): array
    {
        $users = User::inRandomOrder()->limit($userCount)->get();
        $result = [];
        
        foreach ($users as $user) {
            $posts = Post::where('user_id', $user->id)
                ->inRandomOrder()
                ->limit($postCount)
                ->get();
                
            $result[] = [
                'user' => $user,
                'posts' => $posts
            ];
        }
        
        return $result;
    }

    /**
     * Проверить, являются ли данные моковыми (для режима разработки).
     */
    public function isMockData(): bool
    {
        return app()->environment('local', 'development');
    }
} 