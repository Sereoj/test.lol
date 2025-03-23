<?php

namespace App\Repositories;

use App\Models\Content\Source;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SourceRepository
{


    /**
     * Найти запись по ID с указанными отношениями
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?\Illuminate\Database\Eloquent\Model
    {
        $this->logInfo("Поиск источника с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Get all sources.
     *
     * @return Collection|Builder[]
     */
    public function getAllSources(): Collection|array
    {
        return $this->getAll();
    }

    /**
     * Get a source by ID.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     *
     */
    public function getSourceById($id): \Illuminate\Database\Eloquent\Model
    {
        return $this->findById($id);
    }

    /**
     * Create a new source.
     *
     * @return Source
     */
    public function createSource(array $data)
    {
        return $this->create([
            'name' => json_encode($data['name']),
            'iconUrl' => $data['iconUrl'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Update an existing source.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateSource($id, array $data)
    {
        $source = $this->findById($id);
        return $this->update($source, [
            'name' => json_encode($data['name']),
            'iconUrl' => $data['iconUrl'],
            'updated_at' => now(),
        ]);
    }

    /**
     * Delete a source.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteSource($id)
    {
        $source = $this->findById($id);
        return $this->delete($source);
    }
}
