<?php

namespace Database\Seeders;

use App\Models\Comments\Comment;
use App\Models\Challenge;
use App\Models\Posts\Post;
use App\Models\Content\Tag;
use App\Models\Billing\Transaction;
use App\Models\Billing\Subscription;
use App\Models\Media\Media;
use App\Models\Categories\Category;
use App\Models\Users\User;
use App\Models\Users\UserBalance;
use App\Models\Users\UserSetting;
use App\Models\Content\Achievement;
use App\Models\NotificationSetting;
use App\Models\Media\Avatar;
use App\Models\PostMedia;
use App\Models\Comments\CommentLike;
use App\Models\Interactions\Interaction;
use App\Models\Posts\PostStatistic;
use App\Models\Follow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database with mock data for development.
     */
    public function run(): void
    {
        // Отключаем внешние ключи на время сидинга
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Очищаем таблицы от предыдущих моковых данных
        if (app()->environment('local', 'development')) {
            $this->truncateDevelopmentTables();
        }

        // Создаем настройки пользователей
        $this->command->info('Creating user settings...');
        UserSetting::factory()->count(5)->create();
        UserSetting::factory()->private()->count(2)->create();
        UserSetting::factory()->online()->count(3)->create();

        // Создаем достижения
        $this->command->info('Creating achievements...');
        Achievement::factory()->count(10)->create();
        Achievement::factory()->highValue()->count(3)->create();

        // Создаем пользователей
        $this->command->info('Creating mock users...');
        User::factory()->count(20)->create();

        // Создаем админа
        User::factory()->admin()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'slug' => 'admin',
        ]);

        // Балансы пользователей
        $this->command->info('Creating user balances...');
        UserBalance::factory()->count(15)->create();
        UserBalance::factory()->highBalance()->count(5)->create();

        // Создаем настройки уведомлений
        $this->command->info('Creating notification settings...');
        NotificationSetting::factory()->count(10)->create();
        NotificationSetting::factory()->allDisabled()->count(5)->create();
        NotificationSetting::factory()->importantOnly()->count(5)->create();

        // Создаем аватары
        $this->command->info('Creating avatars...');
        Avatar::factory()->count(15)->create();

        // Создаем категории
        $this->command->info('Creating mock categories...');
        Category::factory()->count(5)->root()->create();
        Category::factory()->count(10)->subcategory()->create();

        // Создаем посты
        $this->command->info('Creating mock posts...');
        Post::factory()->count(30)->published()->create();
        Post::factory()->count(10)->draft()->create();
        Post::factory()->count(10)->paid()->published()->create();

        // Создаем медиафайлы
        $this->command->info('Creating mock media...');
        Media::factory()->count(40)->image()->create();
        Media::factory()->count(10)->video()->create();
        Media::factory()->count(5)->resized()->create();
        Media::factory()->count(5)->compressed()->create();

        // Связываем посты с медиафайлами
        $this->command->info('Creating post media links...');
        PostMedia::factory()->count(50)->create();

        // Создаем комментарии
        $this->command->info('Creating mock comments...');
        Comment::factory()->count(100)->create();
        Comment::factory()->count(30)->asReply()->create();

        // Создаем лайки комментариев
        $this->command->info('Creating comment likes...');
        CommentLike::factory()->count(50)->like()->create();
        CommentLike::factory()->count(20)->dislike()->create();

        // Создаем взаимодействия с постами
        $this->command->info('Creating post interactions...');
        Interaction::factory()->count(300)->view()->create();
        Interaction::factory()->count(100)->like()->create();
        Interaction::factory()->count(50)->download()->create();

        // Создаем статистику постов
        $this->command->info('Creating post statistics...');
        PostStatistic::factory()->count(30)->create();
        PostStatistic::factory()->count(10)->highEngagement()->create();
        PostStatistic::factory()->count(5)->viral()->create();
        PostStatistic::factory()->count(5)->lowEngagement()->create();

        // Создаем подписки
        $this->command->info('Creating mock follows...');
        Follow::factory()->count(50)->create();

        // Создаем теги и связываем с постами
        $this->command->info('Creating mock tags...');
        $tags = Tag::factory()->count(20)->create();
        $styleTags = Tag::factory()->count(5)->style()->create();
        $techniqueTags = Tag::factory()->count(5)->technique()->create();

        // Связываем теги с постами
        $posts = Post::all();
        foreach ($posts as $post) {
            $post->tags()->attach(
                $tags->random(rand(2, 5))->pluck('id')->toArray()
            );

            // Добавляем по одному стилю и технике
            $post->tags()->attach(
                $styleTags->random(1)->pluck('id')->toArray()
            );
            $post->tags()->attach(
                $techniqueTags->random(1)->pluck('id')->toArray()
            );
        }

        // Создаем транзакции
        $this->command->info('Creating mock transactions...');
        Transaction::factory()->count(20)->purchase()->create();
        Transaction::factory()->count(15)->deposit()->create();
        Transaction::factory()->count(10)->withdrawal()->create();

        // Создаем подписки
        $this->command->info('Creating mock subscriptions...');
        Subscription::factory()->count(10)->active()->create();
        Subscription::factory()->count(5)->expired()->create();
        Subscription::factory()->count(3)->canceled()->create();

        // Создаем челленджи
        $this->command->info('Creating mock challenges...');
        Challenge::factory()->count(3)->active()->create();
        Challenge::factory()->count(2)->upcoming()->create();
        Challenge::factory()->count(5)->completed()->create();

        // Включаем внешние ключи
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Mock data seeding completed successfully!');
    }

    /**
     * Очистка таблиц от предыдущих моковых данных.
     */
    private function truncateDevelopmentTables(): void
    {
        DB::table('post_tag')->truncate();
        DB::table('post_media')->truncate();
        DB::table('comment_likes')->truncate();
        DB::table('comment_reports')->truncate();
        DB::table('comment_reposts')->truncate();
        Post::truncate();
        Comment::truncate();
        Media::truncate();
        Tag::truncate();
        Transaction::truncate();
        Subscription::truncate();
        Challenge::truncate();
        UserBalance::truncate();
        Category::truncate();
        UserSetting::truncate();
        Achievement::truncate();
        NotificationSetting::truncate();
        Avatar::truncate();
        PostMedia::truncate();
        CommentLike::truncate();
        Interaction::truncate();
        PostStatistic::truncate();
        Follow::truncate();

        // Оставляем пользователей из основного сидера и удаляем только моковых
        User::where('id', '>', 10)->delete();
    }
}
