<?php

namespace CakeCsv\Libraries;

use Psr\Http\Message\StreamInterface;

class CsvStream implements StreamInterface
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
     * @return CsvStream
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
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        throw new \LogicException("You are not allowed to detach the stream");
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
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        // TODO: Implement tell() method.
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
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.
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
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        $this->writable;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        return fputs($this->stream, ($string . "\n"));
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

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return them. Fewer than $length bytes
     *                    may be returned if underlying stream call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        return fgets($this->stream, 4096);
    }

    /**
     * @return array
     */
    public function readRow()
    {
        return fgetcsv($this->stream, 0, $this->delimiter, $this->enclosure);
    }

    /**
     * @param array $row
     * @return int
     */
    public function writeRow(array $row)
    {
        return fputcsv($this->stream, $row, $this->delimiter, $this->enclosure);
    }

}