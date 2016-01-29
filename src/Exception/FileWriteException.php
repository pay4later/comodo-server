<?php

namespace Pay4Later\Exception;

class FileWriteException extends FileException
{
    protected static function getFormattedMessage($uri)
    {
        return "File Write Exception: $uri";
    }
}