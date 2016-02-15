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
class CsvComponent2 extends Component
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
        $this->_defaultConfig = Hash::merge($this->_defaultConfig, $config);
        parent::initialize($this->_defaultConfig);
    }

    /**
     * @param array  $data
     * @param string $file
     * @param array  $options
     * @return bool
     */
    public function save(array $data, $file = '', array $options = [])
    {
        $this->setUpOptions($options);
        if (empty($file)) {
            $file = $this->_getDefaultFileName();
        }
        $file = $this->getAsStream($file, 'r+', $options);
        foreach ($data as $row) {
            $file->writeRow($row);
        }
        $file->close();
        return true;
    }

    public function download(array $data, $name = '', $file = '', array $options = [])
    {
        if (empty($file)) {
            $file = 'php://temp';
        }

        $csvStream = $this->getAsStream($file, 'r+', $options);
        foreach ($data as $row) {
            $csvStream->writeRow($row);
        }

        $csvStream->rewind();
        $output = $csvStream->getContents();
        $csvStream->close();

        if (empty($name)) {
            $name = $this->_getDefaultFileName();
        }

        $output = $this->fixEncodings($output, $options);
        $this->response->type('csv');
        $this->response->download($name);
        $this->response->body($output);
    }

    /**
     * @param string $file
     * @param array  $options
     * @return array
     */
    public function getData($file, array $options = [])
    {
        $file = $this->getAsStream($file, 'r', $options);
        $data = [];
        while ($row = $file->readRow()) {
            $data[] = $row;
        }
        $file->close();
        return $data;
    }

    /**
     * Returns the filename as a CsvStream object
     *
     * @param CsvStream|string $file
     * @param string           $mode
     * @param array            $options
     * @return CsvStream
     */
    protected function getAsStream($file, $mode, array $options = [])
    {
        if ($file instanceof CsvStream) {
            return $file;
        }

        $options = Hash::merge($this->_config, $options);
        $file = CsvStream::openFile($file, $mode, $options['delimiter'], $options['enclosure']);
        return $file;
    }

    /**
     * @param string $content
     * @param array  $options
     * @return string
     */
    protected function fixEncodings($content, array $options)
    {
        if (empty($options['csvEncoding'])) {
            return $content;
        }

        if ($options['dataEncoding'] == $options['csvEncoding']) {
            return $content;
        }

        return iconv($options['dataEncoding'], $options['csvEncoding'], $content);
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

    private function setUpOptions(array $options)
    {
        $this->_config = Hash::merge($this->_defaultConfig, $options);
        return $this->_config;
    }
}
