<?php
namespace CakeOven\CakeCsv\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use CakeCsv\Libraries\CsvStream;

/**
 * Csv behavior
 */
class CsvBehavior extends Behavior
{

    /**
     * Import public function
     *
     * @param CsvStream|string $file
     * @param array            $fields
     * @param array            $options
     * @return array of all data from the csv file in [Model][field] format
     * @author Dean Sofer
     */
    public function importFromCsv($file, array $fields = [], array $options = [])
    {

    }

    /**
     * return CsvStream
     */
    public function exportForCsv()
    {

    }
}