<?php

namespace Pay4Later\Exception;

abstract class FileException extends Exception
{
    private $uri;
    
    public static function create($uri)
    {
        $exception = new static(static::getFormattedMessage($uri));
        $exception->setUri($uri);
        return $exception;
    }

    public function getUri()
    {
        return $this->uri;
    }

    protected static function getFormattedMessage($uri)
    {
        return "File Exception: $uri";
    }

    private function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }
}