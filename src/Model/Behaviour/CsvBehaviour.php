<?php
namespace CakeOven\CakeCsv\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use CakeCsv\Libraries\CsvStream;

/**
 * Csv behavior
 */
class CsvBehavior extends Behavior
{

    public $_defaultConfig;

    /**
     * return CsvStream
     *
     * @param Query $query
     * @param array $options
     * @return Query
     */
    public function findCsv(Query $query, array $options)
    {
        return $query->where($options);
    }
}