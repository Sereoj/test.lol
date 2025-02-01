<?php

namespace Database\Seeders;

use App\Events\TaskCreated;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
/*        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Очищаем таблицу
        DB::table('tasks')->truncate();
        // Включаем проверку обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $jsonFilePath = database_path('files/tasks.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $tasks = json_decode($jsonContent, true);

        foreach ($tasks['tasks'] as $taskData) {
            $task = Task::create([
                'name' => $taskData['name'],
                'description' => $taskData['description'],
                'target' => $taskData['target'],
                'period' => $taskData['period'],
                'experience_reward' => $taskData['experience_reward'],
                'virtual_balance_reward' => $taskData['virtual_balance_reward'],
                'type' => $taskData['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Вызов события TaskCreated
            event(new TaskCreated($task));
        }

    }
}
