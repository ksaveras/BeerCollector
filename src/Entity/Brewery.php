<?php

namespace BeerCollector\Entity;

/**
 * Class Brewery.
 */
class Brewery
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $beers;

    /**
     * Brewery constructor.
     */
    public function __construct()
    {
        $this->beers = array();
    }

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
     * @return Beer[]
     */
    public function getBeers()
    {
        return $this->beers;
    }

    /**
     * @param array $beers
     */
    public function setBeers($beers)
    {
        $this->beers = $beers;
    }

    /**
     * @param Beer $beer
     */
    public function addBeer(Beer $beer)
    {
        $this->beers[] = $beer;
    }
}
