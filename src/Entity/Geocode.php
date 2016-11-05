<?php

namespace BeerCollector\Entity;

use BeerCollector\Traits\Geolocatable;

/**
 * Class Geocode.
 */
class Geocode
{
    use Geolocatable;

    /**
     * @var int
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
