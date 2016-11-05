<?php

namespace BeerCollector\Command;

use BeerCollector\Entity\Beer;
use BeerCollector\Tool\BeerCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApplicationCommand.
 */
class ApplicationCommand extends Command
{
    /**
     * Configure command options description and help.
     */
    protected function configure()
    {
        $this->setName('beer_collector')
            ->setDescription('Finds path to breweries for collecting unique beers for my party.')
            ->addOption('lat', null, InputOption::VALUE_REQUIRED, 'Start point latitude value')
            ->addOption('lon', null, InputOption::VALUE_REQUIRED, 'Start point longitude value')
            ->addOption('distance', null, InputOption::VALUE_REQUIRED, 'Distance limit to travel', 2000)
            ->addOption(
                'bruteforce',
                null,
                InputOption::VALUE_NONE,
                'Try to find best path with max beers (takes lot of time)'
            )
            ->setHelp(<<<EOT
This application tries to find best path to travel between several breweries and collect beers and return to home.
This looks like traveling salesman problem solver with limitations. It must travel not more than defined maximum
distance (with return to home).

User must define latitude and longitude of start point via parameters --lat and --lon
Location point parameters accepts several formats (use quotes to define as a parameter value):
* 51.1548597
* 40.23°
* -5.234°
* 56.242 E
* 40° 26.222'
* 65° 32.22' S
* +40:26:46
* 40:26:46 S

Distance parameter defines limit of total travel distance with return to home. Must be non negative numeric value.

If You have time - You can try bruteforce option. This option generates all possible routes (with respect to max
distance) and calculates number of beers could be collected. Maximum number of beers returns best route.

Example usage:
  beer_collector --lat=54.6858453 --lon=25.2865201
EOT
            );
    }

    /**
     * Executes command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);

        $beerCollector = new BeerCollector();

        if (null === $input->getOption('lat')) {
            throw new InvalidArgumentException('Missing latitude option');
        }

        if (null === $input->getOption('lon')) {
            throw new InvalidArgumentException('Missing longitude option');
        }

        try {
            $beerCollector->setLatitude($input->getOption('lat'));
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid latitude value.');
        }

        try {
            $beerCollector->setLongitude($input->getOption('lon'));
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid longitude value.');
        }

        if (null !== $input->getOption('bruteforce')) {
            $beerCollector->setBruteForce($input->getOption('bruteforce'));
        }

        $beerCollector->setDistanceLimit($input->getOption('distance'));
        $beerCollector->setDumpsDirectory(__DIR__.'/../../dumps');

        $edges = $beerCollector->run();

        if (0 === $edges->count()) {
            $output->writeln('No path found. Increase distance limit or set start point closer to breweries.');

            $output->writeln('');
            $timeSpent = microtime(true) - $startTime;
            $output->writeln('Time took for all actions: '.round($timeSpent * 1000).' ms');

            return 0;
        }

        $output->writeln('Found '.($edges->count() - 1).' breweries to visit.');

        $totalDistance = 0;
        $collectedBeers = array();
        $startVertex = $beerCollector->getStartVertex();

        $output->writeln(
            sprintf(
                "[%' 4s] %s (%s)",
                $startVertex->getId(),
                $startVertex->getAttribute('location_name'),
                $startVertex->getAttribute('geocode')->locationToString()
            )
        );

        foreach ($edges as $edge) {
            /** @var \Fhaculty\Graph\Edge\Base $edge */
            $toVertex = $edge->getVertexToFrom($startVertex);
            $totalDistance += $edge->getWeight();

            if (null !== $toVertex->getAttribute('beers')) {
                $collectedBeers = array_merge($collectedBeers, $toVertex->getAttribute('beers'));
            }

            $output->writeln(
                sprintf(
                    "[%' 4s] %s (%s) distance %.3f km",
                    $toVertex->getId(),
                    $toVertex->getAttribute('location_name'),
                    $toVertex->getAttribute('geocode')->locationToString(),
                    $edge->getWeight()
                )
            );

            $startVertex = $toVertex;
        }

        $output->writeln('');
        $output->writeln('Total distance traveled: '.round($totalDistance, 3).' km');
        $output->writeln('');

        $output->writeln('Collected '.count($collectedBeers).' beers:');
        foreach ($collectedBeers as $beer) {
            /* @var Beer $beer */
            $output->writeln(' * '.$beer->getName());
        }

        $output->writeln('');
        $timeSpent = microtime(true) - $startTime;
        $output->writeln('Time took for all actions: '.round($timeSpent * 1000).' ms');

        return 0;
    }
}
