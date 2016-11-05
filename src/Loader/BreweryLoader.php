<?php

namespace BeerCollector\Loader;

use BeerCollector\Entity\Brewery;

/**
 * Class BreweryLoader.
 */
class BreweryLoader
{
    /**
     * Loads breweries from CSV file indexed by id attribute.
     *
     * @param string $filename
     *
     * @return Brewery[]
     */
    public static function fromCsv($filename)
    {
        $breweries = array();

        if (false !== ($handle = fopen($filename, 'rb'))) {
            if (false !== ($header = fgetcsv($handle, 0, ','))) {
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    $data = array_combine($header, $data);

                    $brewery = new Brewery();
                    $brewery->setId((int) $data['id']);
                    $brewery->setName($data['name']);
                    $breweries[$brewery->getId()] = $brewery;
                }
            }
            fclose($handle);
        }

        return $breweries;
    }
}
