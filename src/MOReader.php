<?php

namespace Studiow\MOReader;

class MOReader
{

    private $fileCache = [];

    public function read($filename)
    {
        $filename = realpath($filename);
        if ((bool) $filename && !isset($this->fileCache[$filename])) {
            $this->fileCache[$filename] = (array) new MOFile($filename);
        }
        return $this->fileCache[$filename];
    }

}
