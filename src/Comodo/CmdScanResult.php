<?php

namespace Pay4Later\Comodo;

use DateTime;
use SplFileInfo;

class CmdScanResult
{
    private $fileInfo;
    private $infected;
    private $scanTime;
    private $elapsed;

    public function __construct(SplFileInfo $fileInfo, $infected, DateTime $scanTime, $elapsed)
    {
        $this->fileInfo = $fileInfo;
        $this->infected = $infected;
        $this->scanTime = $scanTime;
        $this->elapsed = $elapsed;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function isInfected()
    {
        return $this->infected;
    }

    public function getScanTime()
    {
        return $this->scanTime;
    }

    public function getElapsed()
    {
        return $this->elapsed;
    }
}