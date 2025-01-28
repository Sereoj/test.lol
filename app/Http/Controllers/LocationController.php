<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Services\LocationService;
use Exception;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Display a listing of the locations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $locations = $this->locationService->getAllLocations();
            Log::info('Locations retrieved successfully');

            return response()->json($locations, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving locations: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified location.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $location = $this->locationService->getLocationById($id);
            Log::info('Location retrieved successfully', ['id' => $id]);

            return response()->json($location, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving location: '.$e->getMessage(), ['id' => $id]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created location in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreLocationRequest $request)
    {
        try {
            $location = $this->locationService->storeLocation($request->all());
            Log::info('Location stored successfully', ['location_id' => $location->id, 'data' => $request->all()]);

            return response()->json(['message' => 'Location stored successfully', 'location' => $location], 201);
        } catch (Exception $e) {
            Log::error('Error storing location: '.$e->getMessage(), ['data' => $request->all()]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified location.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreLocationRequest $request, int $id)
    {
        try {
            $location = $this->locationService->updateLocation($id, $request->all());
            Log::info('Location updated successfully', ['id' => $id, 'data' => $request->all()]);

            return response()->json(['message' => 'Location updated successfully', 'location' => $location], 200);
        } catch (Exception $e) {
            Log::error('Error updating location: '.$e->getMessage(), ['id' => $id, 'data' => $request->all()]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified location.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->locationService->deleteLocation($id);
            Log::info('Location deleted successfully', ['id' => $id]);

            return response()->json(['message' => 'Location deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting location: '.$e->getMessage(), ['id' => $id]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
