<?php
namespace CakeCsv\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use CakeCsv\Controller\Component\CsvComponent;
use CakeCsv\Test\TestApp\Controller\CakeCsvTestController;

class ExportComponentTest extends TestCase
{

    public $Csv = null;

    public $Controller = null;

    public function setUp()
    {
        parent::setUp();
        // Setup our component and fake test controller
        $this->Controller = new CakeCsvTestController();
        $Collection = new ComponentRegistry();
        $this->Csv = new CsvComponent($Collection);
        $CakeRequest = new Request();
        $CakeResponse = new Response();
    }

    public function testFlattenArray()
    {
        $resultArray = [];
        $this->Csv->flattenArray($this->exampleNested, $resultArray);
        $this->assertEquals($this->exampleFlattened, $resultArray);
    }

    public function testGetKeysForHeaderRow()
    {
        $dedupedKeys = $this->Csv->getKeysForHeaderRow($this->rowsWithInconsistentKeys);
        $this->assertEquals($this->headerRow, $dedupedKeys);
    }

    public function testMapAllRowsToHeaderRow()
    {
        $result = $this->Csv->mapAllRowsToHeaderRow($this->headerRow, $this->rowsWithInconsistentKeys);
        $this->assertEquals($this->rowsWithMissingKeysAdded, $result);
    }
}
