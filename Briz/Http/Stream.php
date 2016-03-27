<?php
namespace Briz\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{

    /**
     * The Given Stream
     */
    protected $stream;

    /**
     * Create a new Stream object
     * @param resource 
     * @throws \RunTimeException
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new \RuntimeException(
            "argument to Stream must be a valid resource"
            );
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
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        $fstat = fstat($this->stream);
        if (isset($fstat['size'])) {
            return $fstat['size'];
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
        if (!is_resource($this->stream)) {
            throw new \RuntimeException("unable to get the current position of the file read/write pointer");
        }
        $position = ftell($this->stream);
        if (!$position) {
            throw new \RuntimeException("unable to get the current position of the file read/write pointer");
        }
        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        if (!is_resource($this->stream)) {
            return true;
        }
        return feof($this->stream);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_resource($this->stream)) {
            throw new \RuntimeException("unable to Seek to in the stream");
        }
        if (fseek($this->stream, $offset, $whence) < 0) {
            throw new \RuntimeException("unable to Seek to in the stream");
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if (!is_resource($this->stream) or ! (rewind($this->stream))) {
            throw new \RuntimeException("unable to Seek to the beginning of the stream");
        }
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {

        if (is_resource($this->stream)) {
            $uri = $this->getMetadata('uri');
            if (strpos($uri, 'php://') == 0) {
                if ($uri == 'php://input' || $uri == 'php://stdin') {
                    return false;
                }
                $modes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];
                $mode = $this->getMetadata('mode');
                foreach ($modes as $m) {
                    if (strpos($m, $mode) == 0) {
                        return true;
                    }
                }
                return false;
            }
            if (is_resource($this->stream) and is_writable($this->getMetadata('uri'))) {
                return true;
            }
        }
        return false;
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
        if (strlen($string) == 0) {
            return 0;
        }
        if ($this->isWritable()) {
            $bytes = fwrite($this->stream, $string);
            if ($bytes == 0 or $bytes === false) {
                throw new \RuntimeException("unable to Write data to the stream");
            }
            return $bytes;
        } else {
            throw new \RuntimeException("Resource is not writable. unable to Write data to the stream ");
        }
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {

        if (is_resource($this->stream)) {
            $uri = $this->getMetadata('uri');
            if (strpos($uri, 'php://') == 0) {
                if ($uri == 'php://output' || $uri == 'php://stdout' || $uri == 'php://stderr') {
                    return false;
                }
                $modes = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];
                $mode = $this->getMetadata('mode');
                foreach ($modes as $m) {
                    if (strpos($m, $mode) == 0) {
                        return true;
                    }
                }
                return false;
            }
            if (is_readable($uri)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if ($this->isReadable()) {
            $string = fread($this->stream, $length);
            if ($string === false) {
                throw new \RuntimeException("unable to Read data from the stream");
            }
            return $string;
        } else {
            throw new \RuntimeException("resource is not readble. Unable to Read data from the stream ");
        }
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!is_resource($this->stream) or ( ($contents = stream_get_contents($this->stream)) === false)) {
            throw new \RuntimeException('unable to get contents of stream');
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
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $meta;
        }
        if (isset($meta[$key])) {
            return $meta[$key];
        }
        return null;
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
        if (!is_resource($this->stream)) {
            return '';
        }
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\RuntimeException $e) {
            return '';
        }
    }

}
