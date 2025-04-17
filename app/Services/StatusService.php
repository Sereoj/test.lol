<?php

namespace App\Services;

use App\Models\Users\UserStatus;

class StatusService
{
    public function getAll()
    {
        return UserStatus::all();
    }

    public function create(array $data)
    {
        return UserStatus::create($data);
    }

    public function getById($id)
    {
        return UserStatus::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $status = UserStatus::findOrFail($id);
        $status->update($data);
        return $status;
    }

    public function delete($id)
    {
        $status = UserStatus::findOrFail($id);
        $status->delete();
    }
}
