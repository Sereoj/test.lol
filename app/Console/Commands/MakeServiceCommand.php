<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name} {path?}';

    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->argument('path') ?? 'app/Services';

        if (! str_ends_with($name, 'Service')) {
            $name .= 'Service';
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
            $this->error('Service already exists!');

            return;
        }

        File::put($filePath, $template);

        $this->info('Service created successfully: '.$filePath);
    }
}
