<?php

class CsvFile
{

    protected $stream;

    protected $size = null;

    protected $delimiter;

    protected $enclosure;

    protected $seekable;

    protected $readable;

    protected $writable;

    /**
     * @var array Hash of readable and writable stream types
     */
    private static $accessRights = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    public function __construct($stream, $delimiter, $enclosure)
    {
        $this->setStream($stream);
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::$accessRights['read'][$meta['mode']]);
        $this->writable = isset(self::$accessRights['write'][$meta['mode']]);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }

    /**
     * Closes the stream when the helper is destructed
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param $filename
     * @param $mode
     * @param $delimiter
     * @param $enclosure
     * @return CsvFile
     */
    public static function openFile($filename, $mode, $delimiter, $enclosure)
    {
        $stream = fopen($filename, $mode);
        return new self($stream, $delimiter, $enclosure);
    }

    /**
     * @param $stream
     */
    protected function setStream($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }
        $this->stream = $stream;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->stream);
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        // If the stream is a file based stream and local, then use fstat
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see  seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        rewind($this->stream);
    }

    /**
     * Write data to the stream.
     *
     * @param array $row The row to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write(array $row)
    {
        return fputcsv($this->stream, $row, $this->delimiter, $this->enclosure);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        $this->writable;
    }

    /**
     * @return array
     */
    public function read()
    {
        return fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        $this->readable;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while reading.
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *                    provided. Returns a specific key value if a key is provided and the
     *                    value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        return !$key ? $meta : (array_key_exists($key, $meta) ? $meta[$key] : null);
    }
}
