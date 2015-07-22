<?php

namespace Studiow\MOReader\Exception;

class InvalidFileException extends \Exception
{

    public function __construct($filename)
    {
        parent::__construct("File {$filename} is not a valid gettext file");
    }

}
