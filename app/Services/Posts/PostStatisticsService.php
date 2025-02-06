<?php

namespace App\Services\Posts;

use App\Models\Interaction;
use App\Models\Posts\PostStatistic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostStatisticsService
{
    public function getSummaryStatistics($userId, array $filters = [], $groupBy = 'day'): array
    {
        if (! $userId) {
            return ['message' => 'User ID is required.'];
        }

        // Определяем формат времени для группировки
        $dateFormat = $groupBy === 'hour'
            ? "DATE_FORMAT(post_statistics.updated_at, '%Y-%m-%d %H:00:00')"
            : 'DATE(post_statistics.updated_at)';

        $query = PostStatistic::query()
            ->join('posts', 'post_statistics.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->selectRaw("
            $dateFormat as time_period,
            COALESCE(SUM(post_statistics.views_count), 0) as total_views,
            COALESCE(SUM(post_statistics.likes_count), 0) as total_likes,
            COALESCE(SUM(post_statistics.downloads_count), 0) as total_downloads,
            COALESCE(SUM(post_statistics.purchases_count), 0) as total_purchases,
            COALESCE(SUM(post_statistics.reposts_count), 0) as total_reposts,
            COALESCE(SUM(post_statistics.comments_count), 0) as total_comments
        ")
            ->groupBy('time_period') // Группируем по временному интервалу
            ->orderBy('time_period', 'asc'); // Сортировка по времени

        // Применяем фильтр по категории постов
        if (! empty($filters['category_id']) && is_numeric($filters['category_id'])) {
            $query->where('posts.category_id', $filters['category_id']);
        }

        // Применяем фильтр по временным рамкам
        if (! empty($filters['date_range'])
            && is_array($filters['date_range'])
            && count($filters['date_range']) === 2
            && ! empty($filters['date_range'][0])
            && ! empty($filters['date_range'][1])) {

            $query->whereBetween('post_statistics.updated_at', [
                $filters['date_range'][0],
                $filters['date_range'][1],
            ]);
        }

        // Получаем данные
        $statistics = $query->get();

        // Преобразуем результат в массив для графиков
        return $statistics->map(function ($stat) {
            return [
                'time_period' => $stat->time_period,
                'total_views' => (int) $stat->total_views,
                'total_likes' => (int) $stat->total_likes,
                'total_downloads' => (int) $stat->total_downloads,
                'total_purchases' => (int) $stat->total_purchases,
                'total_reposts' => (int) $stat->total_reposts,
                'total_comments' => (int) $stat->total_comments,
            ];
        })->toArray();
    }

    public function getPostStatistics(int $postId, array $filters = []): array
    {
        $query = PostStatistic::query()
            ->join('posts', 'post_statistics.post_id', '=', 'posts.id')
            ->where('posts.id', $postId)
            ->selectRaw('
            COUNT(DISTINCT posts.user_id) as unique_users,
            SUM(post_statistics.views_count) as total_views,
            SUM(post_statistics.likes_count) as total_likes,
            SUM(post_statistics.downloads_count) as total_downloads,
            SUM(post_statistics.purchases_count) as total_purchases,
            SUM(post_statistics.reposts_count) as total_reposts,
            SUM(post_statistics.comments_count) as total_comments
        ');

        // Фильтр по времени (например, за последние 30 дней)
        if (! empty($filters['date_range'])) {
            $query->whereBetween('post_statistics.updated_at', $filters['date_range']);
        }

        return $query->first()->toArray();
    }

    public function getRecentPostsStatistics(int $userId, int $limit = 10): array
    {
        if (! $userId) {
            return ['message' => 'User ID is required.'];
        }

        return PostStatistic::query()
            ->join('posts', 'post_statistics.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId) // Фильтр по пользователю
            ->select([
                'posts.id',
                'posts.title',
                'post_statistics.views_count',
                'post_statistics.likes_count',
                'post_statistics.downloads_count',
                'post_statistics.purchases_count',
                'post_statistics.comments_count',
                DB::raw('posts.price * post_statistics.purchases_count as income'), // Расчёт дохода
            ])
            ->orderBy('post_statistics.updated_at', 'desc') // Сортировка по последнему обновлению
            ->limit($limit) // Ограничение количества записей
            ->get()
            ->toArray();
    }

    public function incrementLikes(int $postId)
    {
        $userId = Auth::id();

        // Проверяем, не поставил ли пользователь лайк ранее
        $interaction = Interaction::query()
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('interaction_type', 'like')
            ->first();

        if (! $interaction) {
            // Нет лайка, увеличиваем количество лайков
            $stat = PostStatistic::query()->where('post_id', $postId)->first();
            $stat->increment('likes_count');
            $stat->save();

            // Создаем запись о лайке
            Interaction::create([
                'user_id' => $userId,
                'post_id' => $postId,
                'interaction_type' => 'like',
            ]);

            return $stat;
        }

        return ['message' => 'You have already liked this post.'];
    }

    public function decrementLikes(int $postId)
    {
        $userId = Auth::id();

        $interaction = Interaction::query()
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('interaction_type', 'like')
            ->first();

        if ($interaction) {
            $stat = PostStatistic::query()->where('post_id', $postId)->first();
            if ($stat->likes_count > 0) {
                $stat->decrement('likes_count');
                $stat->save();
            }

            $interaction->delete();

            return $stat;
        }

        return ['message' => 'You have not liked this post yet.'];
    }

    public function incrementComments(int $postId)
    {
        $stat = PostStatistic::query()->where(['post_id' => $postId])->first();
        $stat->increment('comments_count');
        $stat->save();
    }

    public function decrementComments(int $postId)
    {
        $stat = PostStatistic::query()->where(['post_id' => $postId])->first();
        $stat->decrement('comments_count');
        $stat->save();
    }

    public function incrementDownloads(int $postId)
    {
        $userId = Auth::id();

        if (! $this->hasInteraction($postId, $userId, 'view')) {
            $stat = PostStatistic::query()->where(['post_id' => $postId])->first();
            $stat->increment('downloads_count');
            $stat->save();

            Interaction::create([
                'user_id' => $userId,
                'post_id' => $postId,
                'interaction_type' => 'download',
            ]);

            return $stat;
        }

        return false;
    }

    public function incrementViews(int $postId)
    {
        $userId = Auth::id();

        $stat = PostStatistic::query()->where(['post_id' => $postId])->first();
        $stat->increment('views_count');
        $stat->save();

        Interaction::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'interaction_type' => 'view',
        ]);

        return $stat;
    }

    public function hasInteraction(int $postId, int $userId, string $type): bool
    {
        return Interaction::query()->where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('interaction_type', $type)
            ->exists();
    }
}
