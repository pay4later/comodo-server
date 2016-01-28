<?php

namespace Pay4Later\Controller;

use DI\Container;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Pay4Later\Comodo\CmdScan;
use Pay4Later\Model\RemoteFile;

class HomeController
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch()
    {
        if (empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            return ['error' => 'Method Not Allowed'];
        }

        $files = isset($_FILES) ? $_FILES : [];
        $urls  = isset($_POST['files']) && is_array($_POST['files']) ? $_POST['files'] : [];

        if ($files && $urls) {
            header('HTTP/1.1 400 Bad Request');
            return ['error' => 'File uploads and remote paths may not be mixed'];
        }

        if (count($files) + count($urls) === 0) {
            header('HTTP/1.1 400 Bad Request');
            return ['error' => 'At least one file must be set'];
        }

        if ($files) $scanned = $this->getLocalFilesToScan($files);
        else if ($urls) $scanned = $this->getRemoteFilesToScan($urls);

        $cmdScan = $this->container->get(CmdScan::class);
        $details = [];
        $elapsed = 0;
        $numScanned  = 0;
        $numInfected = 0;

        foreach ($scanned as $key => $scan) {
            $result = $cmdScan->scan($scan['path']);

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

    /**
     * @todo move to model
     * @param array $files
     * @return array
     */
    private function getLocalFilesToScan(array $files)
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

    /**
     * @todo move to model
     * @param array $files
     * @return array
     */
    private function getRemoteFilesToScan(array $files)
    {
        $remoteFiles = [];
        /** @var RemoteFile $remoteFile */

        // check for the existence of all remote files
        foreach ($files as $key => $url) {
            // todo implement a RemoteFileService
            $remoteFile = $this->container->make(RemoteFile::class, ['url' => $url]);
            if (!$remoteFile->exists()) {
                header('HTTP/1.1 400 Bad Request');
                return ['error' => 'File not found: ' . $url];
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
            header('HTTP/1.1 400 Bad Request');
            return ['error' => 'Failed to retrieve file: ' . $remoteFile->getUrl()];
        }

        return $result;
    }
}