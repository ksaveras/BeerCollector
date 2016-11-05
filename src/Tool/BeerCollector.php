<?php

namespace BeerCollector\Tool;

use BeerCollector\Algorithm\Bruteforce;
use BeerCollector\Algorithm\NearestNeighbor;
use BeerCollector\Entity\Brewery;
use BeerCollector\Entity\Geocode;
use BeerCollector\Filter\DistanceFilter;
use BeerCollector\Loader\BeerLoader;
use BeerCollector\Loader\BreweryLoader;
use CrEOF\Geo\String\Parser;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Set\Edges;
use Fhaculty\Graph\Vertex;

/**
 * Class BeerCollector.
 */
class BeerCollector
{
    /**
     * todo separate dependencies.
     *
     * @var \CrEOF\Geo\String\Parser
     */
    private $geoParser;

    /**
     * @var Brewery[]
     */
    private $breweries;

    /**
     * @var array
     */
    private $beers;

    /**
     * @var float
     */
    private $startLatitude;

    /**
     * @var float
     */
    private $startLongitude;

    /**
     * @var float|int
     */
    private $distanceLimit;

    /**
     * @var string
     */
    private $dumpsDirectory;

    /**
     * @var Graph
     */
    private $graph;

    /**
     * @var Vertex
     */
    private $startVertex;

    /**
     * @var bool
     */
    private $bruteForce = false;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->geoParser = new Parser();
        $this->graph = new Graph();
    }

    /**
     * Sets start point latitude.
     *
     * @param $latitude
     *
     * @throws \CrEOF\Geo\String\Exception\UnexpectedValueException
     */
    public function setLatitude($latitude)
    {
        $this->startLatitude = $this->geoParser->parse($latitude);
    }

    /**
     * Sets start point longitude.
     *
     * @param $longitude
     *
     * @throws \CrEOF\Geo\String\Exception\UnexpectedValueException
     */
    public function setLongitude($longitude)
    {
        $this->startLongitude = $this->geoParser->parse($longitude);
    }

    /**
     * @return mixed
     */
    public function getDistanceLimit()
    {
        return $this->distanceLimit;
    }

    /**
     * @param mixed $distanceLimit
     */
    public function setDistanceLimit($distanceLimit)
    {
        if (!is_numeric($distanceLimit)) {
            throw new \InvalidArgumentException('Distance limit must be numeric');
        }
        if ($distanceLimit <= 0) {
            throw new \InvalidArgumentException('Distance limit must positive and greater than 0');
        }

        $this->distanceLimit = $distanceLimit;
    }

    /**
     * @return Vertex
     */
    public function getStartVertex()
    {
        return $this->startVertex;
    }

    /**
     * @param string $dumpsDirectory
     */
    public function setDumpsDirectory($dumpsDirectory)
    {
        if (!is_dir($dumpsDirectory)) {
            throw new \InvalidArgumentException('This directory does not exists');
        }

        $this->dumpsDirectory = realpath($dumpsDirectory);
    }

    /**
     * @param bool $bruteForce
     */
    public function setBruteForce($bruteForce)
    {
        $this->bruteForce = (bool) $bruteForce;
    }

    /**
     * Runs application.
     *
     * @return Edges
     */
    public function run()
    {
        $this->loadBreweries('breweries.csv');
        $this->loadBeers('beers.csv');

        $this->createStartVertex();

        if ($this->distanceLimit) {
            $filter = new DistanceFilter($this->startLatitude, $this->startLongitude, $this->distanceLimit / 2);
            $filterCallback = [$filter, 'filter'];
        } else {
            $filterCallback = null;
        }

        $this->loadGeocodeVertices($filterCallback);

        return $this->findPath();
    }

    private function loadBreweries($filename)
    {
        $this->breweries = BreweryLoader::fromCsv($this->dumpsDirectory.'/'.$filename);
    }

    private function loadBeers($filename)
    {
        $this->beers = BeerLoader::fromCsv($this->dumpsDirectory.'/'.$filename);
    }

    /**
     * Creates start (home) vertex with defined geopoint and required attributes.
     */
    private function createStartVertex()
    {
        $geocode = new Geocode();
        $geocode->setId(0);
        $geocode->setLatitude($this->startLatitude);
        $geocode->setLongitude($this->startLongitude);

        $this->startVertex = $this->graph->createVertex(0);
        $this->startVertex->setAttribute('geocode', $geocode);
        $this->startVertex->setAttribute('location_name', 'HOME');
        $this->startVertex->setAttribute('beer_count', 0);
    }

    /**
     * @param $filterCallback
     */
    private function loadGeocodeVertices($filterCallback = null)
    {
        if (false !== ($handle = fopen($this->dumpsDirectory.'/geocodes.csv', 'r'))) {
            if (false !== ($header = fgetcsv($handle, 0, ','))) {
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    $data = array_combine($header, $data);

                    if ($filterCallback && (false === call_user_func($filterCallback, $data))) {
                        continue;
                    }

                    if (!isset($this->breweries[$data['brewery_id']])) {
                        continue;
                    }

                    if (!isset($this->beers[$data['brewery_id']])) {
                        continue;
                    }

                    $vertex = $this->graph->createVertex((int) $data['id']);

                    $geocode = new Geocode();
                    $geocode->setId((int) $data['id']);
                    $geocode->setLatitude((float) $data['latitude']);
                    $geocode->setLongitude((float) $data['longitude']);

                    $vertex->setAttribute('geocode', $geocode);
                    $vertex->setAttribute('brewery_id', $data['brewery_id']);
                    $vertex->setAttribute('location_name', $this->breweries[$data['brewery_id']]->getName());
                    $vertex->setAttribute('beer_count', count($this->beers[$data['brewery_id']]));
                    $vertex->setAttribute('beers', $this->beers[$data['brewery_id']]);

                    foreach ($this->graph->getVertices() as $graphVertex) {
                        /** @var \Fhaculty\Graph\Vertex $graphVertex */
                        // Do not calculate distance to self and don't add cycle edge
                        if ($graphVertex->getId() == $vertex->getId()) {
                            continue;
                        }

                        $distance = $geocode->getObjectDistance($graphVertex->getAttribute('geocode'));
                        $graphVertex->createEdge($vertex)->setWeight($distance);
                    }
                }
            }
            fclose($handle);
        }
    }

    /**
     * @return \Fhaculty\Graph\Set\Edges
     */
    private function findPath()
    {
        if ($this->bruteForce) {
            return $this->findPathBruteForce();
        }

        return $this->findPathFast();
    }

    /**
     * @return Edges
     */
    private function findPathFast()
    {
        $nearestNeighbor = new NearestNeighbor($this->startVertex);
        $nearestNeighbor->setWeightLimit($this->distanceLimit);

        return $nearestNeighbor->getEdges();
    }

    /**
     * @return Edges
     */
    private function findPathBruteForce()
    {
        $bruteForce = new Bruteforce($this->startVertex);
        $bruteForce->setUpperLimit($this->distanceLimit);
        $bruteForce->setMaxAttribute('beer_count');

        return $bruteForce->getEdges();
    }
}
