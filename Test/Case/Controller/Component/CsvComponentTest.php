<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('CsvComponent', 'CakeCSV.Controller/Component');

// A fake controller to test against
class TestExportController extends Controller {

	public $paginate = null;
}

class ExportComponentTest extends CakeTestCase {

	public $ExportComponent = null;

	public $Controller = null;

	// Note: I think really we should use fixtures for this; make a find call to the model with fixture data.
	// Imagine 3 tables/models: City belongsTo State, belongsTo Country.
	// And this code within Cities Controller:
	// $this->City->contain(array('State' => array('Country')));
	// $this->City->find(all);
	// Then one single row of the result would look like this:
	private $exampleNested = [
		'City' => [
			'name' => 'Sydney',
			'population' => '4.6m',
		],
		'State' => [
			'name' => 'NSW',
			'Country' => [
				'name' => 'Australia',
			],
		],
	];

	private $exampleFlattened = [
		'City.name' => 'Sydney',
		'City.population' => '4.6m',
		'State.name' => 'NSW',
		'State.Country.name' => 'Australia',
	];

	private $rowsWithInconsistentKeys = [
		0 => [
			'Name' => 'John',
			'Age' => '20',
		],
		1 => [
			'Name' => 'Fred',
			'Height' => '6ft',
		],
	];

	private $headerRow = [
		0 => 'Name',
		1 => 'Age',
		2 => 'Height',
	];

	private $rowsWithMissingKeysAdded = [
		0 => [
			'Name' => 'John',
			'Age' => '20',
			'Height' => '',
		],
		1 => [
			'Name' => 'Fred',
			'Age' => '',
			'Height' => '6ft',
		],
	];

	public function setUp() {
		parent::setUp();
		// Setup our component and fake test controller
		$Collection = new ComponentCollection();
		$this->ExportComponent = new ExportComponent($Collection);
		$CakeRequest = new CakeRequest();
		$CakeResponse = new CakeResponse();
		$this->Controller = new TestExportController($CakeRequest, $CakeResponse);
		$this->ExportComponent->startup($this->Controller);
	}

	public function testFlattenArray() {
		$resultArray = [];
		$this->ExportComponent->flattenArray($this->exampleNested, $resultArray);
		$this->assertEqual($this->exampleFlattened, $resultArray);
	}

	public function testGetKeysForHeaderRow() {
		$dedupedKeys = $this->ExportComponent->getKeysForHeaderRow($this->rowsWithInconsistentKeys);
		$this->assertEqual($this->headerRow, $dedupedKeys);
	}

	public function testMapAllRowsToHeaderRow() {
		$result = $this->ExportComponent->mapAllRowsToHeaderRow($this->headerRow, $this->rowsWithInconsistentKeys);
		$this->assertEqual($this->rowsWithMissingKeysAdded, $result);
	}

	public function tearDown() {
		parent::tearDown();
		// Clean up after we're done
		unset($this->ExportComponent);
		unset($this->Controller);
	}
}