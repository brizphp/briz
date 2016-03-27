<?php
namespace Briz\FileSystem;

/**
 * File Operations.
 */
class File
{

    /**
     * Read File.
     * 
     * Opens a file provided in $file and returns its contents as a string.
     * 
     * @param string $file path to file
     * @return string file contents
     */
    public function read($file)
    {
        return file_get_contents($file);
    }

    /**
     * Write a string to file.
     * 
     * @param string $file path to the file
     * @param string $data data to write
     * @return int Number of bytes writen
     */
    public function write($file, $data)
    {
        return file_put_contents($file, $data);
    }

    /**
     * Delete a File
     * 
     * Files must be writable or owned by the system in order to be deleted.
     * @param string $file
     * @return boolean
     */
    public function delete($file)
    {
        try {
            if (!@unlink($file)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }
    
    /**
     * Check Whether a File or Directory Exists
     * @param string $file path to filename or directory
     * @return bool
     */
    public function exists($file)
    {
        return file_exists($file);
    }

}
