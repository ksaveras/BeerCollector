<?php

namespace BeerCollector\Filter;

use BeerCollector\Geo\GeoCalculator;

/**
 * Class DistanceFilter.
 */
class DistanceFilter
{
    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @var float
     */
    private $limit;

    /**
     * DistanceFilter constructor.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $limit
     */
    public function __construct($latitude, $longitude, $limit)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->limit = $limit;
    }

    /**
     * Checks if data with lat and lon is within defined distance range.
     *
     * @param array $data
     *
     * @return float|bool float if data is within distance range and false otherwise
     */
    public function filter($data)
    {
        if (isset($data['latitude'], $data['longitude'])) {
            $lat = (float) $data['latitude'];
            $lon = (float) $data['longitude'];

            $distance = GeoCalculator::getDistance($this->latitude, $this->longitude, $lat, $lon);

            if ($distance <= $this->limit) {
                return true;
            }
        }

        return false;
    }
}
