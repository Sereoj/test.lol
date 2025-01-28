<?php

namespace App\Services;

use App\Models\Source;
use App\Repositories\SourceRepository;
use Exception;

class SourceService
{
    private SourceRepository $sourceRepository;

    public function __construct(SourceRepository $sourceRepository)
    {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Get all sources.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllSources()
    {
        try {
            return $this->sourceRepository->getAllSources();
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving sources.');
        }
    }

    /**
     * Get a source by ID.
     *
     * @param  int  $id
     * @return Source
     */
    public function getSourceById($id)
    {
        try {
            return $this->sourceRepository->getSourceById($id);
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the source.');
        }
    }

    /**
     * Create a new source.
     *
     * @return Source
     */
    public function createSource(array $data)
    {
        try {
            return $this->sourceRepository->createSource($data);
        } catch (Exception $e) {
            throw new Exception('An error occurred while creating the source.');
        }
    }

    /**
     * Update an existing source.
     *
     * @return Source
     */
    public function updateSource(int $id, array $data)
    {
        try {
            return $this->sourceRepository->updateSource($id, $data);
        } catch (Exception $e) {
            throw new Exception('An error occurred while updating the source.');
        }
    }

    /**
     * Delete a source.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteSource($id)
    {
        try {
            return $this->sourceRepository->deleteSource($id);
        } catch (Exception $e) {
            throw new Exception('An error occurred while deleting the source.');
        }
    }
}
