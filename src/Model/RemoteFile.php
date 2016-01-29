<?php

namespace Pay4Later\Model;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Pay4Later\Exception\FileWriteException;

class RemoteFile
{
    private $client;
    private $url;
    private $localPath;

    public function __construct(ClientInterface $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function __destruct()
    {
        if (file_exists($this->localPath)) {
            unlink($this->localPath);
        }
    }

    public function exists()
    {
        $statusCode = null;
        try {
            $response = $this->getClient()->head($this->url)->send();
            $statusCode = $response->getStatusCode();
        } catch (ClientErrorResponseException $e) {
            return false;
        }
        return 200 <= $statusCode && $statusCode < 300;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getLocalPath()
    {
        if (!$this->localPath) {
            $filePath = $this->getTempFilePath();
            $hnd = fopen($filePath, 'wb');
            if (!$hnd) {
                throw FileWriteException::create($filePath);
            }
            try {
                $this->client->get($this->url)
                    ->setResponseBody($hnd)
                    ->send();
            } finally {
                if (is_resource($hnd)) {
                    fclose($hnd);
                }
            }
            $this->localPath = $filePath;
        }
        return $this->localPath;
    }

    public function getUrl()
    {
        return $this->url;
    }

    private function getTempFilePath()
    {
        return sys_get_temp_dir() . '/' . uniqid();
    }
}