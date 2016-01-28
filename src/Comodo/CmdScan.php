<?php

namespace Pay4Later\Comodo;

use DateTime;
use Pay4Later\Comodo\Exception\CmdScanException;
use SplFileInfo;

class CmdScan
{
    private $comodoSettings;

    public function __construct(ComodoSettings $comodoSettings)
    {
        $this->comodoSettings = $comodoSettings;
    }

    /**
     * @param SplFileInfo|string $file
     * @return CmdScanResult
     * @todo use a process wrapper and redirect stderr
     */
    public function scan($file)
    {
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }

        // scan file for viruses
        $cmd = sprintf('%s -s %s 2>/dev/null', escapeshellarg($this->comodoSettings->getPath() . '/cmdscan'), escapeshellarg($file->getRealPath()));
        $startTime = microtime(true);
        $result = shell_exec($cmd);
        $elapsedTime = microtime(true) - $startTime;

        $scannedVirusesResult = $this->parseResult($result);
        $dateTime = new DateTime();
        $dateTime->setTimestamp($startTime);

        return new CmdScanResult(
            $file,
            $scannedVirusesResult['viruses'] !== 0,
            $dateTime,
            $elapsedTime
        );
    }

    private function parseResult($stdout)
    {
        preg_match('/^Number of Scanned Files: (\d+)/m', $stdout, $scanned);
        $scanned = $scanned ? (int) $scanned[1] : null;
        preg_match('/^Number of Found Viruses: (\d+)/m', $stdout, $viruses);
        $viruses = $viruses ? (int) $viruses[1] : null;

        if (!is_numeric($scanned) || !is_numeric($viruses)) {
            throw new CmdScanException('Unable to parse scan results');
        }

        return [
            'scanned' => $scanned,
            'viruses' => $viruses
        ];
    }
}