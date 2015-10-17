# CakeCSV

## Thanks 

Many thanks to [joshuapaling](https://github.com/joshuapaling/CakePHP-Export-CSV-Plugin). This is a project forked from his repo. He mentions that his plugin is deprecated but I think it was a nice and simple development and I thought to keep it up.
I didn't do a pull request to the initial repo because this plugin doesn't follow the CakePHP coding standards and also made a lot of changes to the initial plugin.

## Description

A CakePHP 2.x plugin to export/download data as a CSV file. Pass it the result of a `$this->MyModel->find('all');` call, and it'll flatten it and download it as a .csv file.

It handles nested `belongsTo` associations just fine. As for `hasMany` (and other) associations, I don't think they can (or ever need to be) handled gracefully in a single CSV export.
If you think differently, I'm open to suggestions or pull requests.

Tested with CakePHP 2.6. Should work with all CakePHP 2.X.

## Installation

### 1. Install the plugin into your app/Plugin/CakeCsv directory

	git submodule add git@github.com:cakeoven/CakeCSV.git app/Plugin/CakeCSV

or download it from https://github.com/cakeoven/CakeCsv

### 2. Load the Plugin

In app/Config/bootstrap.php, add a line to load the plugin:

    //Loads only the Csv plugin
	CakePlugin::load('CakeCSV'); 

or
    //Loads all plugins at once
	CakePlugin::loadAll(); 

## Usage

### 1. Add the Export Component to your Components array

Add 'CakeCSV.Csv' to your Components array of the relevant controller 

	var $components = array('CakeCsv.Csv');

### 2. Start exporting your data.

Say you had a model/controller for Cities. And say that a City belongsTo a State, which belongsTo a country.
Your export function in your Cities controller might look like this:

	public function export_cities() {
		// It's OK to use containable or recursive in the export data
		$this->City->contain(array(
			'State' => array(
				'Country'
			)
		));
		$data = $this->City->find('all');
		$this->Csv->export($data, 'cities.csv');
		// a CSV file called cities.csv will be downloaded by the browser.
	}

### Settings of component

You can set options when loading the component

    var $components = [
        CakeCsv.Csv => [
            'delimiter' => ';',
            'enclosure' => '"',
            'dataEncoding' => 'UTF-8',
            'csvEncoding' => 'WINDOWS-1253',
        ],
    ];

You can change this options by accessing the public properties of the component like:

    $this->Csv->delimiter = ",";

### Options

The `export()` function has 2 params:

1. `$data` - an array of data to export. This array should be of the format returned by a call to $this->MyModel->find('all');
2. `$fileName` (optional) - the name of the file to download. If blank, it will use a date-stamped name along with the controllers name like export_Cities_2013-09-24.csv

## Example input/output

Lets say City `belongsTo` State, which `belongsTo` country. You might fetch data from the City model looking something like this:

	array(
		0 => array(
			'City' => array(
				'name' => 'Sydney',
				'population' => '4.6m'
			),
			'State' => array(
				'name' => 'NSW',
				'Country' => array(
					'name' => 'Australia',
				)
			)
		),
		1 => array(
			'City' => array(
				'name' => 'Melbourne',
				'population' => '4.1m'
			),
			'State' => array(
				'name' => 'VIC',
				'Country' => array(
					'name' => 'Australia',
				)
			)
		),
	)

And the Csv component will output a CSV like this:

<table cellpadding="7">
	<tr>
		<th>City.name</th>
		<th>City.population</th>
		<th>State.name</th>
		<th>State.Country.name</th>
	</tr>
	<tr>
		<td>Sydney</td>
		<td>4.6m</td>
		<td>NSW</td>
		<td>Australia</td>
	</tr>
	<tr>
		<td>Melbourne</td>
		<td>4.1m</td>
		<td>VIC</td>
		<td>Australia</td>
	</tr>
</table>

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Credits

- George Mponos - http://gmponos.webthink.gr
- Joshua Paling - http://www.bbldigital.com.au/

## License

MIT - http://opensource.org/licenses/MIT

## ToDo

Write Unit tests