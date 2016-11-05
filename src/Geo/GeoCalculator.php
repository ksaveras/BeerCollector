<?php

namespace BeerCollector\Geo;

/**
 * Class GeoCalculator.
 */
class GeoCalculator
{
    const EARTH_RADIUS = 6371;

    /**
     * Calculates distance between two geo points.
     *
     * @param float $latitudeFrom  Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo    Latitude of target point in [deg decimal]
     * @param float $longitudeTo   Longitude of target point in [deg decimal]
     *
     * @return float Distance between points in [km]
     */
    public static function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        return self::haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo);
    }

    /**
     * Calculates the distance between two points, with the Harversine formula.
     *
     * @param float $latitudeFrom  Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo    Latitude of target point in [deg decimal]
     * @param float $longitudeTo   Longitude of target point in [deg decimal]
     *
     * @return float Distance between points in [km]
     */
    protected static function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $deltaLatitude = deg2rad($latitudeTo - $latitudeFrom);
        $deltaLongitude = deg2rad($longitudeTo - $longitudeFrom);
        $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
            cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) *
            sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }
}
