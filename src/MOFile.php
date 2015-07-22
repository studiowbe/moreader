<?php

namespace Studiow\MOReader;

use Studiow\MOReader\Exception\FileNotFoundException;
use Studiow\MOReader\Exception\InvalidFileException;

class MOFile extends \ArrayObject
{

    private $packMode = 'V';
    private $file;
    private $messages = [];

   
    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }
        //open for binary reading
        $this->file = fopen($filename, 'rb');
        $this->packMode = $this->getPackMode();
        if (false === $this->packMode) {
            throw new InvalidFileException($filename);
        }
        $this->parseContent();
        fclose($this->file);
        
    }

    /**
     * Read all messages
     */
    private function parseContent()
    {
        $revision = $this->readData()[1];

        $numMessages = $this->readData()[1];

        $messagesOffset = $this->readData()[1];

        $translationsOffset = $this->readData()[1];


        fseek($this->file, $messagesOffset);
        $messages = $this->readData(2 * $numMessages);

        fseek($this->file, $translationsOffset);
        $translations = $this->readData(2 * $numMessages);

        for ($a = 0; $a < $numMessages; ++$a) {
            $pos = $a * 2 + 1;
            if (isset($messages[$pos]) && $messages[$pos] != 0) {
                fseek($this->file, $messages[$pos + 1]);
                $original = (array) fread($this->file, $messages[$pos]);
            } else {
                $original = [''];
            }

            if (isset($translations[$pos]) && $translations[$pos] != 0) {
                fseek($this->file, $translations[$pos + 1]);
                $translation = explode("\0", fread($this->file, $translations[$pos]));
                $this->setMessage($original, $translation);
            }
        }
        unset($this['']);
    }

    /**
     * Store the message
     * @param array $original
     * @param array $translation
     */
    private function setMessage($original, $translation)
    {
        if (sizeof($translation) > 0 && sizeof($original) > 0) {
            $this[$original[0]] = $translation[0];
            array_shift($original);
            foreach ($original as $orig) {
                $this[$orig] = '';
            }
        } else {
            
            $this[$original[0]] = $translation[0];
        }
    }

    /**
     * Read bytes from file
     * @param int $numBytes
     * @return array
     */
    private function readData($numBytes = 1)
    {
        return unpack($this->packMode . $numBytes, fread($this->file, 4 * $numBytes));
    }

    /**
     * Determine which mode to use when reading binary data
     * @return string|boolean N for big-endian, V for little-endian, or false for invalid data
     */
    private function getPackMode()
    {
        $data = $this->readData();
        if (isset($data[1])) {
            $str_test = substr(dechex(intval($data[1])), -8);
            if (strtolower($str_test) === 'de120495') {
                return 'N'; //big endian
            } else if (strtolower($str_test) === '950412de') {
                return 'V'; //small endian
            }
        }
        return false;
    }

    
    
}
