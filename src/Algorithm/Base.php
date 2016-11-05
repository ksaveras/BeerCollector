<?php

namespace BeerCollector\Algorithm;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Walk;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Set\Edges;

/**
 * Class Base.
 */
abstract class Base
{
    /**
     * @throws \Exception on error
     *
     * @return Graph
     */
    public function createGraph()
    {
        return $this->getGraph()->createGraphCloneEdges($this->getEdges());
    }

    /**
     * @return Graph
     */
    abstract protected function getGraph();

    /**
     * @return Vertex
     */
    abstract protected function getVertexStart();

    /**
     * returns best circle connecting all vertices.
     *
     * @return Walk
     */
    public function getCycle()
    {
        return Walk::factoryCycleFromEdges($this->getEdges(), $this->getVertexStart());
    }

    /**
     * Returns total weight of edges.
     *
     * @return float
     */
    public function getWeight()
    {
        $weight = 0;
        foreach ($this->getEdges() as $edge) {
            $weight += $edge->getWeight();
        }

        return $weight;
    }

    /**
     * Returns array of edges connecting all vertices in a circle.
     *
     * @return Edges
     */
    abstract public function getEdges();
}
