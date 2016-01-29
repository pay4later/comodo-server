<?php

namespace Pay4Later\Controller;

use DI\Container;
use Pay4Later\Exception\FileReadException;
use Pay4Later\Exception\FileNotFoundException;
use Pay4Later\Model\VirusScanner;

class HomeController
{
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

        $virusScanner = $this->container->get(VirusScanner::class);

        try {
            if ($files) {
                return $virusScanner->scanUploadedFiles($files);
            } else {
                return $virusScanner->scanRemoteFiles($urls);
            }
        } catch (FileNotFoundException $e) {
            header('HTTP/1.1 400 Bad Request');
            return ['error' => 'File not found: ' . $e->getUri()];
        } catch (FileReadException $e) {
            header('HTTP/1.1 400 Bad Request');
            return ['error' => 'Failed to retrieve file: ' . $e->getUri()];
        }
    }
}