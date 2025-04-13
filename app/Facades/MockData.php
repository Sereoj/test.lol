<?php

namespace App\Facades;

use App\Models\Challenge;
use App\Models\Comment;
use App\Models\Posts\Post;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\Users\User;
use Illuminate\Support\Facades\Facade;

/**
 * Фасад для удобного получения моковых данных в режиме разработки.
 * 
 * @method static User getRandomUser()
 * @method static User getRandomAdmin()
 * @method static User getRandomVerifiedUser()
 * @method static Post getRandomPost()
 * @method static Post getRandomPublishedPost()
 * @method static Post getRandomPaidPost()
 * @method static Post[] getRandomPosts(int $count)
 * @method static Comment getRandomComment()
 * @method static Tag[] getRandomTags(int $count)
 * @method static Challenge getRandomActiveChallenge()
 * @method static Transaction getRandomTransaction()
 * @method static array getUsersWithPosts()
 * @method static bool isMockData()
 *
 * @see \App\Services\MockDataService
 */
class MockData extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mock-data';
    }
} 