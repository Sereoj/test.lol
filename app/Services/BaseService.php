<?php

namespace App\Services;

abstract class BaseService
{
    abstract public function getAll();
    abstract public function create(array $data);

    abstract public function getById(int $id);
    abstract public function update(int $id, array $data);
    abstract public function delete($id);
}
