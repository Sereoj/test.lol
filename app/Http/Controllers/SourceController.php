<?php

namespace App\Http\Controllers;

use App\Http\Requests\Source\CreateSourceRequest;
use App\Http\Requests\Source\UpdateSourceRequest;
use App\Services\SourceService;
use Exception;

class SourceController extends Controller
{
    protected SourceService $sourceService;

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

    /**
     * Display a listing of the sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $sources = $this->sourceService->getAllSources();

            return response()->json($sources, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified source.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $source = $this->sourceService->getSourceById($id);

            return response()->json($source, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created source in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateSourceRequest $request)
    {
        try {
            $source = $this->sourceService->createSource($request->all());

            return response()->json($source, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified source in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSourceRequest $request, int $id)
    {
        try {
            $source = $this->sourceService->updateSource($id, $request->only('name', 'iconUrl'));

            return response()->json($source, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified source from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->sourceService->deleteSource($id);

            return response()->json(['message' => 'Source deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
