<?php

namespace BeerCollector\Algorithm;

use Fhaculty\Graph\Exception\UnexpectedValueException;
use Fhaculty\Graph\Exception\UnderflowException;
use Fhaculty\Graph\Vertex;
use Fhaculty\Graph\Set\Edges;

/**
 * Class Bruteforce.
 */
class Bruteforce extends Base
{
    /**
     * reference to start vertex.
     *
     * @var Vertex
     */
    private $startVertex;

    /**
     * total number of edges needed.
     *
     * @var int
     */
    private $numEdges;

    /**
     * upper limit to use for branch-and-bound (BNB).
     *
     * @var float|null
     *
     * @see AlgorithmTspBruteforce::setUpperLimit()
     */
    private $upperLimit;

    /**
     * Attribute to check on maximization problem.
     *
     * @var string|null
     */
    private $maxAttribute;

    /**
     * Best maximization problem value.
     *
     * @var int|float|null
     */
    private $bestAttributeValue;

    /**
     * @var Vertex
     */
    private $vertex;

    /**
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
     * Sets upper limit.
     *
     * @param float $limit
     *
     * @return Bruteforce
     */
    public function setUpperLimit($limit)
    {
        $this->upperLimit = $limit;

        return $this;
    }

    /**
     * Sets maxx attribute name.
     *
     * @param null|string $maxAttribute
     */
    public function setMaxAttribute($maxAttribute)
    {
        $this->maxAttribute = $maxAttribute;
    }

    /**
     * Returns best circle of edges connecting all vertices.
     *
     * @throws \Exception on error
     *
     * @return Edges
     */
    public function getEdges()
    {
        $this->numEdges = count($this->getGraph()->getVertices());
        if ($this->numEdges < 3) {
            throw new UnderflowException('Needs at least 3 vertices');
        }

        if (null === $this->maxAttribute) {
            throw new \Exception('Must set maxAttribute value for maximization problem');
        }

        $this->bestAttributeValue = 0;
        $this->startVertex = $this->getVertexStart();

        $result = $this->step(
            $this->startVertex,
            0,
            0,
            array(),
            array()
        );

        if (null === $result) {
            throw new UnexpectedValueException('No resulting solution for TSP found');
        }

        $startVertex = $this->startVertex;
        foreach ($result as $edge) {
            /** @var \Fhaculty\Graph\Edge\Base $edge */
            $startVertex = $edge->getVertexToFrom($startVertex);
        }
        // add edge from last vertex to start vertex (create cycle)
        $result[] = $startVertex->getEdgesTo($this->startVertex)->getEdgeFirst();

        return new Edges($result);
    }

    /**
     * @param Vertex $vertex          current point-of-view
     * @param number $totalWeight     total weight (so far)
     * @param number $totalAttribute  total attribute value (so far)
     * @param bool[] $visitedVertices
     * @param Edge[] $visitedEdges
     *
     * @return Edge[]
     */
    private function step(Vertex $vertex, $totalWeight, $totalAttribute, array $visitedVertices, array $visitedEdges)
    {
        // only visit each vertex once
        if (isset($visitedVertices[$vertex->getId()])) {
            return null;
        }

        // stop recursion if distance to home exceeds limit.
        if ($vertex->hasEdgeTo($this->vertex)) {
            $weightToStart = $totalWeight + $vertex->getEdgesTo($this->vertex)->getEdgeFirst()->getWeight();
            if ($weightToStart > $this->upperLimit) {
                return null;
            }
        }

        // if we have better result - remember it
        if (null !== $this->bestAttributeValue && $totalAttribute > $this->bestAttributeValue) {
            $this->bestAttributeValue = $totalAttribute;
            $bestResult = $visitedEdges;
        } else {
            $bestResult = null;
        }

        // Visited all edges, return
        if (count($visitedEdges) === $this->numEdges) {
            return $bestResult;
        }

        $visitedVertices[$vertex->getId()] = true;

        foreach ($vertex->getEdgesOut() as $edge) {
            /* @var \Fhaculty\Graph\Edge\Base $edge */

            /* @var Vertex $target */
            $target = $edge->getVertexToFrom($vertex);

            $weight = $edge->getWeight();
            if ($weight < 0) {
                throw new UnexpectedValueException('Edge with negative weight "'.$weight.'" not supported');
            }

            $result = $this->step(
                $target,
                $totalWeight + $weight,
                $totalAttribute + $target->getAttribute($this->maxAttribute),
                $visitedVertices,
                array_merge($visitedEdges, array($edge))
            );

            if (null !== $result) {
                $bestResult = $result;
            }
        }

        return $bestResult;
    }
}
