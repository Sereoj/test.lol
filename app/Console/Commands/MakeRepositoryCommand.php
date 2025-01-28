<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name} {path?}';

    protected $description = 'Create a new repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->argument('path') ?? 'app/Repositories';

        if (! str_ends_with($name, 'Repository')) {
            $name .= 'Repository';
        }

        $fullPath = base_path($path);
        $filePath = $fullPath.'/'.$name.'.php';

        if (! File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $namespace = str_replace('/', '\\', ucfirst($path));

        // Шаблон сервисного класса
        $template = <<<CLASS
<?php

namespace {$namespace};

class {$name}
{
}
CLASS;

        // Создаем файл сервиса
        if (File::exists($filePath)) {
            $this->error('Repository already exists!');

            return;
        }

        File::put($filePath, $template);

        $this->info('Repository created successfully: '.$filePath);
    }
}
