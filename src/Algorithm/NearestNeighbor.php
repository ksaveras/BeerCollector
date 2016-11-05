<?php

namespace BeerCollector\Algorithm;

use Fhaculty\Graph\Exception\UnexpectedValueException;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Set\Edges;
use SplPriorityQueue;

/**
 * Class NearestNeighbor.
 */
class NearestNeighbor extends Base
{
    /**
     * @var Vertex
     */
    private $vertex;

    /**
     * @var float
     */
    private $weightLimit = 0;

    /**
     * NearestNeighbor constructor.
     *
     * @param Vertex $startVertex
     */
    public function __construct(Vertex $startVertex)
    {
        $this->vertex = $startVertex;
    }

    /**
     * @return Vertex
     */
    protected function getVertexStart()
    {
        return $this->vertex;
    }

    /**
     * @return \Fhaculty\Graph\Graph
     */
    protected function getGraph()
    {
        return $this->vertex->getGraph();
    }

    /**
     * @return mixed
     */
    public function getWeightLimit()
    {
        return $this->weightLimit;
    }

    /**
     * @param mixed $weightLimit
     */
    public function setWeightLimit($weightLimit)
    {
        $this->weightLimit = $weightLimit;
    }

    /**
     * @return Edges
     */
    public function getEdges()
    {
        $returnEdges = array();
        $totalWeight = 0;

        $n = count($this->vertex->getGraph()->getVertices());

        $vertex = $this->vertex;
        $visitedVertices = array($vertex->getId() => true);

        for ($i = 0; $i < $n - 1; ++$i,
            // n-1 steps (spanning tree)
             $vertex = $nextVertex) {
            // get all edges from the aktuel vertex
            $edges = $vertex->getEdgesOut();

            $sortedEdges = new SplPriorityQueue();

            // sort the edges
            foreach ($edges as $edge) {
                /* @var Edge $edge */
                $sortedEdges->insert($edge, -$edge->getWeight());
            }

            // Untill first is found: get cheepest edge
            foreach ($sortedEdges as $edge) {
                // Get EndVertex of this edge
                /** @var Vertex $nextVertex */
                $nextVertex = $edge->getVertexToFrom($vertex);

                // is unvisited
                if (!isset($visitedVertices[$nextVertex->getId()])) {
                    break;
                }
            }

            // check if there is a way i can use
            if (isset($visitedVertices[$nextVertex->getId()])) {
                throw new UnexpectedValueException('Graph is not complete - can\'t find an edge to unconnected vertex');
            }

            if ($this->getWeightLimit() > 0) {
                $weightToStart = $edge->getWeight() + $nextVertex->getEdgesTo($this->vertex)->getEdgeFirst()->getWeight(
                    );
                if (($weightToStart + $totalWeight) >= $this->getWeightLimit()) {
                    break;
                }
            }

            $visitedVertices[$nextVertex->getId()] = true;

            // clone edge in new Graph
            $returnEdges[] = $edge;

            $totalWeight += $edge->getWeight();
        }

        // check if there is a way from end edge to start edge
        // get first connecting edge
        // connect the last vertex with the start vertex
        $returnEdges[] = $vertex->getEdgesTo($this->vertex)->getEdgeFirst();

        return new Edges($returnEdges);
    }
}
