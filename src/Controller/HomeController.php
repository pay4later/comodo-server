<?php

namespace Pay4Later\Controller;

use DI\Container;
use Pay4Later\Comodo\CmdScan;

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

        $scanned = [];

        foreach ($files as $key => $file) {
            $scanned[$key] = [
                'path' => $file['tmp_name'],
                'uri' => $file['name']
            ];
        }

        foreach ($urls as $key => $url) {
            throw new \Exception('Not Supported');
            $scanned[$key] = [
                'path' => file_put_contents('/tmp/unique', file_get_contents($url)),
                'uri' => $url
            ];
        }

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
}