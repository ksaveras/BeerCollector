<?php

namespace BeerCollector\Loader;

use BeerCollector\Entity\Beer;

/**
 * Class BeerLoader.
 */
class BeerLoader
{
    /**
     * Loads beers from CSV file indexed by brewery_id attribute.
     *
     * @param string $filename
     *
     * @return array Beers indexed by brewery
     */
    public static function fromCsv($filename)
    {
        $beers = array();

        if (false !== ($handle = fopen($filename, 'rb'))) {
            if (false !== ($header = fgetcsv($handle, 0, ','))) {
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    $data = array_combine($header, $data);

                    $beer = new Beer();
                    $beer->setId((int) $data['id']);
                    $beer->setName($data['name']);
                    $beer->setBreweryId((int) $data['brewery_id']);
                    $beers[$beer->getBreweryId()][] = $beer;
                }
            }
            fclose($handle);
        }

        return $beers;
    }
}
