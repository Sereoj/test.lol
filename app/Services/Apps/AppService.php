<?php

namespace App\Services\Apps;

use App\Models\Apps\App;

class AppService
{
    public function createApp(array $data): App
    {
        return App::create([
            'name' => json_encode($data['name']),
            'path' => $data['path'],
        ]);
    }

    public function getAllApps()
    {
        return App::all();
    }

    public function getAppById($id)
    {
        return App::query()->findOrFail($id);
    }

    public function updateApp($id, array $data): bool
    {
        $app = App::query()->findOrFail($id);
        return $app->update($data);
    }

    public function deleteApp($id): bool
    {
        $app = App::query()->findOrFail($id);
        return $app->delete();
    }
}
