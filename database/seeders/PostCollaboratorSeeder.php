<?php

namespace Database\Seeders;

use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostCollaboratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем опубликованные посты
        $posts = Post::where('status', 'published')
            ->inRandomOrder()
            ->limit(30)
            ->get();

        foreach ($posts as $post) {
            // Не все посты будут иметь соавторов (примерно 30% постов)
            if (fake()->boolean(30)) {
                // Получаем случайных пользователей (исключая автора поста)
                $collaborators = User::where('id', '!=', $post->user_id)
                    ->inRandomOrder()
                    ->limit(rand(1, 3))
                    ->get();

                $sortOrder = 1;
                foreach ($collaborators as $collaborator) {
                    // Проверяем, что такой соавтор еще не добавлен
                    $exists = DB::table('post_collaborators')
                        ->where('post_id', $post->id)
                        ->where('user_id', $collaborator->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('post_collaborators')->insert([
                            'post_id' => $post->id,
                            'user_id' => $collaborator->id,
                            'sort_order' => $sortOrder,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $sortOrder++;
                    }
                }
            }
        }

        $this->command->info('Post collaborators created successfully!');
    }
}
