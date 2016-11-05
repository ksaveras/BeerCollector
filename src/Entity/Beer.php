<?php

namespace BeerCollector\Entity;

/**
 * Class Beer.
 */
class Beer
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $breweryId;

    /**
     * @var string
     */
    private $name;

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getBreweryId()
    {
        return $this->breweryId;
    }

    /**
     * @param int $breweryId
     */
    public function setBreweryId($breweryId)
    {
        $this->breweryId = $breweryId;
    }
}
