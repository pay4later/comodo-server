<?php

namespace Pay4Later\Exception;

class FileReadException extends FileException
{
    protected static function getFormattedMessage($uri)
    {
        return "File Read Exception: $uri";
    }
}