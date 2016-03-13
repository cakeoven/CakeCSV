<?php

App::uses('Component', 'Controller');

/**
 * Csv Component
 *
 * @author      Joshua Paling <http://www.bbldigital.com.au/>
 * @author      George Mponos <gmponos@gmail.com>
 * @description A component for CakePHP 2.x to export data as CSV
 * @licence     MIT
 */
class CsvComponent extends Component
{

    /**
     * The calling Controller
     *
     * @var Controller
     */
    public $Controller;

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
     * Starts up ExportComponent for use in the controller
     *
     * @param Controller $controller A reference to the instantiating controller object
     * @return void
     */
    public function startup(Controller $controller)
    {
        $this->Controller = $controller;

        if (empty($this->dataEncoding)) {
            $this->dataEncoding = 'UTF-8';
        }

        if (empty($this->csvEncoding)) {
            $this->csvEncoding = $this->dataEncoding;
        }
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
            $filename = $this->getDefaultFileName();
        }

        // Flatten each row of the data array
        $flatData = $values = [];
        foreach ($data as $numericKey => $row) {
            $flatRow = [];
            $this->flattenArray($row, $flatRow);
            $flatData[$numericKey] = $flatRow;
        }

        $headers = $this->getKeysForHeaders($flatData);
        $csv = $this->getCsvOutput($flatData, $headers);

        $this->Controller->autoRender = false;
        $this->Controller->response->type('csv');
        $this->Controller->response->download($filename);
        $this->Controller->response->body($csv);
    }

    /**
     * Get the output to save as CSV
     *
     * @param array $data    The data used for the CSV
     * @param array $headers The headers used for the CSV
     * @return string
     */
    protected function getCsvOutput($data, $headers)
    {
        $file = CsvFile::openFile('php://temp', 'r+', $this->delimiter, $this->enclosure);
        $file->write($headers);
        foreach ($data as $row) {
            $file->write($row);
        }

        return $this->decode($file->getContents());
    }

    protected function decode($output)
    {
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
    protected function getDefaultFileName()
    {
        return "export_" . $this->Controller->name . "_" . date("Y_m_d") . ".csv";
    }
}
