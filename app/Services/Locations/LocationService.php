<?php

namespace App\Services\Locations;

use App\Models\Locations\Location;

class LocationService
{
    public function getAllLocations()
    {
        return Location::all();
    }

    public function getLocationById(int $id)
    {
        return Location::findOrFail($id);
    }

    public function storeLocation(array $data)
    {
        return Location::create($data);
    }

    public function updateLocation(int $id, array $data)
    {
        $location = Location::findOrFail($id);
        $location->update($data);

        return $location;
    }

    public function deleteLocation(int $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
    }
}
