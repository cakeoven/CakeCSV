<?php

namespace CakeCsv\Controller\Component;

use Cake\Controller\Component;

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
     * the delimiter to use for the CSV file
     *
     * @var string
     */
    public $delimiter;

    /**
     * The character each cell will be enclosed
     *
     * @var string
     */
    public $enclosure;

    /**
     * Contains the encoding the data where fetched
     *
     * @var string
     */
    public $dataEncoding;

    /**
     * Contains the encoding to convert the data for the csv file
     *
     * @var string
     */
    public $csvEncoding;

    /**
     * @param array $config
     */
    public function initialize(array $config)
    {
        if (empty($this->dataEncoding)) {
            $this->dataEncoding = 'UTF-8';
        }

        if (empty($this->csvEncoding)) {
            $this->csvEncoding = $this->dataEncoding;
        }
        parent::initialize($config);
    }

    /**
     * Exports a Csv
     *
     * @param array  $data
     * @param string $filename
     */
    public function export($data, $filename = '')
    {
        if (empty($filename)) {
            $filename = $this->_getDefaultFileName();
        }

        // Flatten each row of the data array
        $flatData = $values = [];
        foreach ($data as $numericKey => $row) {
            $flatRow = [];
            $this->flattenArray($row, $flatRow);
            $flatData[$numericKey] = $flatRow;
        }

        $headers = $this->getKeysForHeaders($flatData);
        $csv = $this->_getCsvOutput($flatData, $headers);

        $this->response->type('csv');
        $this->response->download($filename);
        $this->response->body($csv);
    }

    /**
     * @param $filename
     * @return array|bool
     */
    public function import($filename)
    {
        $file = fopen($filename, 'r');
        // open the file
        if (!empty($file)) {
            return [];
        }

        $data = [];
        // read the 1st row as headings
        $fields = fgetcsv($file, null, $this->delimiter, $this->enclosure);
        foreach ($fields as $key => $field) {
            $field = trim($field);
            if (empty($field)) {
                continue;
            }
            $fields[$key] = strtolower($field);
        }

        // Row counter
        $r = 0;
        // read each data row in the file
        while ($row = fgetcsv($file, null, $this->delimiter, $this->enclosure)) {
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
        // close the file
        fclose($file);
        // return the messages
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
        $csvFp = fopen('php://temp', 'r+');
        fputcsv($csvFp, $headers, $this->delimiter, $this->enclosure);
        foreach ($data as $row) {
            fputcsv($csvFp, $row, $this->delimiter, $this->enclosure);
        }
        rewind($csvFp);
        $output = '';
        while (($buffer = fgets($csvFp, 4096)) !== false) {
            $output .= $buffer;
        }
        fclose($csvFp);

        if (!empty($this->csvEncoding) && $this->dataEncoding != $this->csvEncoding) {
            $output = iconv($this->dataEncoding, $this->csvEncoding, $output);
        }
        return $output;
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
