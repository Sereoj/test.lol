<?php

namespace App\Services;

use App\Models\EmploymentStatus;

class EmploymentStatusService
{
    public function getAllEmploymentStatuses()
    {
        return EmploymentStatus::all();
    }

    public function getEmploymentStatusById($id)
    {
        return EmploymentStatus::find($id);
    }

    public function createEmploymentStatus(array $data)
    {
        return EmploymentStatus::create($data);
    }

    public function updateEmploymentStatus($id, array $data)
    {
        $employmentStatus = EmploymentStatus::find($id);
        if ($employmentStatus) {
            $employmentStatus->update($data);

            return $employmentStatus;
        }

        return null;
    }

    public function deleteEmploymentStatus($id)
    {
        $employmentStatus = EmploymentStatus::find($id);
        if ($employmentStatus) {
            return $employmentStatus->delete();
        }

        return false;
    }
}
