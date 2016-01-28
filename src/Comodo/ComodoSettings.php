<?php

namespace Pay4Later\Comodo;

class ComodoSettings
{
    private $path;

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
}