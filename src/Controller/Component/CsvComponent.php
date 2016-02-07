<?php

namespace CakeCsv\Controller\Component;

use Cake\Controller\Component;
use Cake\Utility\Hash;
use CakeCsv\Libraries\CsvStream;

/**
 * Csv Component
 *
 * @author      Joshua Paling <http://www.bbldigital.com.au/>
 * @author      George Mponos <gmponos@gmail.com>
 * @description A component for CakePHP 3.x to export data as CSV
 * @licence     MIT
 */
class CsvComponent extends Component
{

    /**
     * @var array
     */
    protected $defaults;

    protected $options;

    /**
     * @param array $config
     */
    public function initialize(array $config)
    {
        $this->defaults = Hash::merge([
            'enclosure' => "'",
            'delimiter' => ';',
            'csvEncoding' => 'UTF-8',
            'dataEncoding' => 'UTF-8',
        ], $config);
        parent::initialize($this->defaults);
    }

    /**
     * @param array $options
     */
    protected function setUpOptions(array $options)
    {
        $this->options = Hash::merge($this->defaults, $options);
    }

    /**
     * Exports a Csv
     *
     * @param array  $data
     * @param string $filename
     * @param array  $options
     */
    public function download($data, $filename = '', array $options = [])
    {
        $this->setUpOptions($options);

        // Flatten each row of the data array
        $flatData = $values = [];
        foreach ($data as $numericKey => $row) {
            $flatRow = [];
            $this->flattenArray($row, $flatRow);
            $flatData[$numericKey] = $flatRow;
        }

        $headers = $this->getKeysForHeaders($flatData);
        $csv = $this->_getCsvOutput($flatData, $headers);

        if (empty($filename)) {
            $filename = $this->_getDefaultFileName();
        }

        $this->response->type('csv');
        $this->response->download($filename);
        $this->response->body($csv);
    }

    /**
     * @param string $filename
     * @param array  $options
     * @return array
     */
    public function getContentsFromCsv($filename, array $options = [])
    {
        $this->setUpOptions($options);
        $csvStream = CsvStream::openFile($filename, 'r', $options['delimiter'], $options['enclosure']);
        $csvStream->readRow();

        $data = [];
        // read the 1st row as headings
        $fields = $csvStream->readRow();

        $r = 0;
        // read each data row in the file
        while ($row = $csvStream->readRow()) {
            // for each header field
            foreach ($fields as $f => $field) {
                if (!isset($row[$f])) {
                    $row[$f] = null;
                }
                $row[$f] = trim($row[$f]);
                $data[$r][$field] = $row[$f];
            }
            $r++;
        }

        return $data;
    }

    /**
     * Get the output to save as CSV
     *
     * @param array $data    The data used for the CSV
     * @param array $headers The headers used for the CSV
     * @return string
     */
    protected function _getCsvOutput($data, $headers)
    {
        $csvStream = CsvStream::openFile('php://temp', 'r+');

        $csvStream->writeRow($headers);
        foreach ($data as $row) {
            $csvStream->writeRow($row);
        }
        $csvStream->rewind();
        $output = $csvStream->getContents();
        return $output;
    }

    /**
     * @param $content
     * @return string
     */
    protected function fixEncodings($content)
    {
        if (empty($this->options['csvEncoding'])) {
            return $content;
        }

        if ($this->options['dataEncoding'] == $this->options['csvEncoding']) {
            return $content;
        }

        return iconv($this->options['dataEncoding'], $this->options['csvEncoding'], $content);
    }

    /**
     * Flatten the array to be display it in the CSV file
     *
     * @param array  $array
     * @param array  $flatArray
     * @param string $parentKeys
     */
    public function flattenArray($array, &$flatArray, $parentKeys = '')
    {
        foreach ($array as $key => $value) {
            $chainedKey = ($parentKeys !== '') ? $parentKeys . '.' . $key : $key;
            if (is_array($value)) {
                $this->flattenArray($value, $flatArray, $chainedKey);
            } else {
                $flatArray[$chainedKey] = $value;
            }
        }
    }

    /**
     * Get the headers of a row
     *
     * @param array $data
     * @return array
     */
    public function getKeysForHeaders($data)
    {
        $headerRow = [];
        foreach ($data as $key => $value) {
            foreach ($value as $fieldName => $fieldValue) {
                if (array_search($fieldName, $headerRow) === false) {
                    $headerRow[] = $fieldName;
                }
            }
        }

        return $headerRow;
    }

    /**
     * Retrieve the default filename for the csv
     *
     * @return string
     */
    protected function _getDefaultFileName()
    {
        $name = $this->_registry->getController()->name;
        return "export_" . $name . "_" . date("Y_m_d") . ".csv";
    }
}
