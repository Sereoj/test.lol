<?php

namespace App\Repositories;

use App\Models\Source;

class SourceRepository
{
    /**
     * Get all sources.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllSources()
    {
        return Source::all();
    }

    /**
     * Get a source by ID.
     *
     * @param  int  $id
     * @return Source
     */
    public function getSourceById($id)
    {
        return Source::findOrFail($id);

    }

    /**
     * Create a new source.
     *
     * @return Source
     */
    public function createSource(array $data)
    {
        return Source::create([
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
     * @return Source
     */
    public function updateSource($id, array $data)
    {
        $source = Source::findOrFail($id);
        $source->update([
            'name' => json_encode($data['name']),
            'iconUrl' => $data['iconUrl'],
            'updated_at' => now(),
        ]);

        return $source;
    }

    /**
     * Delete a source.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteSource($id)
    {
        $source = Source::findOrFail($id);

        return $source->delete();
    }
}
