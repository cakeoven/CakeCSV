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
    protected $_defaultConfig = [
        'delimiter' => ';',
        'enclosure' => "'",
        'csvEncoding' => 'UTF-8',
        'dataEncoding' => 'UTF-8',
    ];

    protected $_config = [];

    /**
     * @param array $config
     */
    public function initialize(array $config)
    {
        $this->_config = Hash::merge($this->_defaultConfig, $config);
        parent::initialize($this->_config);
    }

    /**
     * @param array $options
     */
    protected function setUpOptions(array $options)
    {
        $this->_config = Hash::merge($this->_config, $options);
    }

    /**
     * Exports a Csv
     *
     * @param array  $data
     * @param string $filename
     * @param array  $options
     */
    public function download(array $data, $filename = null, array $options = [])
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
        $output = $this->_getCsvOutput($flatData, $headers);
        $filename = $this->_getFileName($filename);

        $this->response->type('csv');
        $this->response->download($filename);
        $this->response->body($output);
    }

    /**
     * @param string $filename
     * @param array  $options
     * @return array
     */
    public function getContentsFromCsv($filename, array $options = [])
    {
        $this->setUpOptions($options);
        $csvStream = CsvStream::openFile($filename, 'r', $this->_config['delimiter'], $this->_config['delimiter']);
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
        $csvStream = CsvStream::openFile('php://temp', 'r+', ';', "'");

        $csvStream->writeRow($headers);
        foreach ($data as $row) {
            $csvStream->writeRow($row);
        }
        $csvStream->rewind();
        $output = $csvStream->getContents();
        $csvStream->close();
        return $this->fixEncodings($output);
    }

    /**
     * @param $content
     * @return string
     */
    protected function fixEncodings($content)
    {
        if (empty($this->_config['csvEncoding'])) {
            return $content;
        }

        if ($this->_config['dataEncoding'] == $this->_config['csvEncoding']) {
            return $content;
        }

        return iconv($this->_config['dataEncoding'], $this->_config['csvEncoding'], $content);
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
     * @param string $name
     * @return string
     */
    protected function _getFileName($name)
    {
        if (!empty($name)) {
            return $name;
        }
        $name = $this->_registry->getController()->name;
        return "export_" . $name . "_" . date("Y_m_d") . ".csv";
    }
}
