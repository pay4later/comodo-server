<?php

namespace Pay4Later\Model;

use Guzzle\Http\ClientInterface;

class RemoteFileService
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function create($url)
    {
        return new RemoteFile($this->client, $url);
    }
}