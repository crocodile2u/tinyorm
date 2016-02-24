<?php
/**
 * FileLog.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\log;


class FileLog implements LogInterface
{
    /**
     * @var resource
     */
    private $descriptor;
    /**
     * FileLog constructor.
     * @param string|resource $file
     */
    function __construct($file = STDOUT)
    {
        if (is_string($file)) {
            $this->descriptor = fopen($file, "w+");
        } elseif (is_resource($file)) {
            $this->descriptor = $file;
        } elseif ($file instanceof \SplFileObject) {
            $this->descriptor = $file->openFile("w+");
        } else {
            throw new \InvalidArgumentException("Argument 1 should a string, a file descriptor or an SplFileObject");
        }
    }

    /**
     * @param $message
     * @return int
     */
    function write($message)
    {
        return fwrite($this->descriptor, "[" . date("Y-m-d H:i:s") . "] " . $message . "\n");
    }
}