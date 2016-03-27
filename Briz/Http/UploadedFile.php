<?php
namespace Briz\Http;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 */
class UploadedFile implements UploadedFileInterface
{

    /**  @var is_bool */
    protected $isMoved = false;

    /** @var string */
    protected $file;

    /** @var int */
    protected $size;

    /** @var int */
    protected $error;

    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    /** @var bool */
    protected $sapi;

    /**
     * Array of valid image mimes
     * @var array
     */
    protected $imageMimes = [
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpe' => ['image/jpeg', 'image/pjpeg'],
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
    ];

    /**
     * Constructor 
     *
     * @param string $file the file path
     * @param int $size  the file size
     * @param int $error the file error code 
     * @param string $name file name
     * @param string $type the media type of the file
     * @param bool $sapi is file is in sapi environment
     */
    public function __construct($file = null, $size = null, $error = UPLOAD_ERR_NO_FILE, $name = null, $type = null, $sapi = false
    )
    {
        $this->file = $file;
        $this->size = isset($size) ? (int) $size : null;
        $this->error = $error;
        $this->name = $name;
        $this->type = $type;
        $this->sapi = $sapi;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     * @return StreamInterface for the uploaded file
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created. 
     */
    public function getStream()
    {
        if ($this->isMoved) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved.');
        }
        return new Stream(fopen($this->file, 'r'));
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if (!is_string($targetPath) || $targetPath === '') {
            throw new \InvalidArgumentException('Invalid path or path is empty.');
        }
        if ($this->isMoved) {
            throw new \RuntimeException('Uploded file already moved');
        }
        if (!is_writable(dirname($targetPath))) {
            throw new \InvalidArgumentException('Target path is not writable');
        }
        // if target is a stream
        if (strpos($targetPath, '://') > 0) {
            if (!copy($this->file, $targetPath)) {
                throw new \RuntimeException('Failed to move file to the target stream');
            }

            unlink($this->file);
        } elseif ($this->sapi) {
            if (!is_uploaded_file($this->file)) {
                throw new \RuntimeException('Invalid upload file');
            }

            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new \RuntimeException('Error moving uploaded file ');
            }
        } else {
            if (!rename($this->file, $targetPath)) {
                throw new \RuntimeException('Error Moving Uploaded file');
            }
            $this->isMoved = true;
        }
    }

    /**
     * Get File size
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * php magic function should return the path
     * 
     * @return string 
     */
    public function __toString()
    {
        return $this->file ? $this->file : '';
    }

    /**
     * To check if an uploaded file is image from its media type
     * this method is not part of psr7 standard
     * 
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     * 
     * @return bool
     */
    public function isImage()
    {
        $imageMime = $this->getClientMediaType();
        foreach ($this->imageMimes as $imageMime) {
            if (in_array($this->type, (array) $imageMime)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the error message for an error code
     * This method not in psr7 standard
     * 
     * @return string 
     */
    public function getErrorMessage()
    {
        $code = $this->error;
        switch ($code) {
            case UPLOAD_ERR_OK:
                $message = "Upload Successfull.";
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

}
