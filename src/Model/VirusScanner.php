<?php

namespace Pay4Later\Model;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Pay4Later\Comodo\CmdScan;
use Pay4Later\Exception\FileReadException;
use Pay4Later\Exception\FileNotFoundException;

class VirusScanner
{
    private $cmdScan;
    private $remoteFileService;

    public function __construct(CmdScan $cmdScan, RemoteFileService $remoteFileService)
    {
        $this->cmdScan = $cmdScan;
        $this->remoteFileService = $remoteFileService;
    }

    public function scanUploadedFiles(array $files)
    {
        return $this->scan($this->getUploadedFiles($files));
    }

    public function scanRemoteFiles(array $files)
    {
        return $this->scan($this->getRemoteFiles($files));
    }

    private function scan(array $files)
    {
        $details = [];
        $elapsed = 0;
        $numScanned  = 0;
        $numInfected = 0;

        foreach ($files as $key => $scan) {
            $result = $this->cmdScan->scan($scan['path']);

            $details[$key] = [
                'uri'  => $scan['uri'],
                'size' => $result->getFileInfo()->getSize(),
                'time' => $result->getScanTime()->format('c'),
                'elapsed'  => number_format($result->getElapsed(), 3),
                'infected' => $result->isInfected()
            ];

            $elapsed += $result->getElapsed();
            $numScanned++;
            if ($result->isInfected()) $numInfected++;
        }

        return [
            'scanned'  => $numScanned,
            'infected' => $numInfected,
            'elapsed'  => number_format($elapsed, 3),
            'details'  => $details
        ];
    }

    private function getUploadedFiles(array $files)
    {
        $result = [];
        foreach ($files as $key => $file) {
            $result[$key] = [
                'path' => $file['tmp_name'],
                'uri'  => $file['name']
            ];
        }
        return $result;
    }

    private function getRemoteFiles(array $files)
    {
        $remoteFiles = [];

        // check for the existence of all remote files
        foreach ($files as $key => $url) {
            $remoteFile = $this->remoteFileService->create($url);
            if (!$remoteFile->exists()) {
                throw FileNotFoundException::create($url);
            }
            $remoteFiles[$key] = $remoteFile;
        }

        $result = [];

        try {
            // create a local copy of remote files
            foreach ($remoteFiles as $key => $remoteFile) {
                $result[$key] = [
                    'path' => $remoteFile->getLocalPath(),
                    'uri'  => $remoteFile->getUrl(),
                    'meta' => $remoteFiles
                ];
            }
        } catch (ClientErrorResponseException $e) {
            throw FileReadException::create($remoteFile->getUrl());
        }

        return $result;
    }
}