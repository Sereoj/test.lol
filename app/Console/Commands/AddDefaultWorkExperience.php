<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use App\Models\Users\UserWorkExperience;
use Illuminate\Console\Command;

class AddDefaultWorkExperience extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:add-default-work-experience';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить запись "Нет опыта работы" всем пользователям без опыта';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем обработку пользователей...');

        // Получаем всех пользователей без записей опыта работы
        $usersWithoutExperience = User::query()
            ->whereDoesntHave('workExperiences')
            ->get();

        $count = $usersWithoutExperience->count();

        if ($count === 0) {
            $this->info('Все пользователи уже имеют записи опыта работы.');
            return 0;
        }

        $this->info("Найдено пользователей без опыта работы: {$count}");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created = 0;

        foreach ($usersWithoutExperience as $user) {
            try {
                UserWorkExperience::create([
                    'user_id' => $user->id,
                    'company' => 'Нет опыта работы',
                    'position' => 'Нет опыта',
                    'start_date' => $user->created_at ?? now(),
                    'end_date' => null,
                    'description' => null,
                    'is_current' => true,
                ]);

                $created++;
            } catch (\Exception $e) {
                $this->error("\nОшибка при создании опыта для пользователя {$user->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("Готово! Создано записей: {$created}");

        return 0;
    }
}
