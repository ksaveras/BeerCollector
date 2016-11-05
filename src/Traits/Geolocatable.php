<?php

namespace BeerCollector\Traits;

use BeerCollector\Exception\InvalidGeolocationException;
use BeerCollector\Geo\GeoCalculator;

/**
 * Class Geolocatable.
 */
trait Geolocatable
{
    /**
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        if (!is_float($latitude)) {
            throw new InvalidGeolocationException('Argument $latitude is not float type');
        }

        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        if (!is_float($longitude)) {
            throw new InvalidGeolocationException('Argument $longitude is not float type');
        }

        $this->longitude = $longitude;
    }

    /**
     * Calculates distance to location.
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return float
     */
    public function getDistance($latitude, $longitude)
    {
        return GeoCalculator::getDistance($this->latitude, $this->longitude, $latitude, $longitude);
    }

    /**
     * Calculates distance to location object that uses this trait.
     *
     * @param $object
     *
     * @return float
     */
    public function getObjectDistance($object)
    {
        if (is_object($object) && in_array('BeerCollector\Traits\Geolocatable', class_uses($object, false))) {
            return GeoCalculator::getDistance(
                $this->latitude,
                $this->longitude,
                $object->getLatitude(),
                $object->getLongitude()
            );
        }

        throw new InvalidGeolocationException(
            'Argument $object does not implement "BeerCollector\Traits\Geolocatable" trait'
        );
    }

    /**
     * Returns location string format.
     *
     * @return string
     */
    public function locationToString()
    {
        return $this->latitude.','.$this->longitude;
    }
}
