<?php

namespace Pay4Later\Exception;

class FileNotFoundException extends FileException
{
    protected static function getFormattedMessage($uri)
    {
        return "File Not Found: $uri";
    }
}