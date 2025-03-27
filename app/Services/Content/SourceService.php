<?php

namespace App\Services\Content;

use App\Models\Content\Source;
use App\Repositories\SourceRepository;
use App\Services\BaseService;
use Exception;

class SourceService extends BaseService
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
    public function getAll()
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
    public function getById($id)
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
    public function create(array $data)
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
    public function update(int $id, array $data)
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
    public function delete($id)
    {
        try {
            return $this->sourceRepository->deleteSource($id);
        } catch (Exception $e) {
            throw new Exception('An error occurred while deleting the source.');
        }
    }
}
