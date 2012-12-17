<?php

namespace BCC\Myrrix;

use Guzzle\Service\Client;
use Guzzle\Common\Collection;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Common\Event;

class MyrrixClient extends Client
{
    public static function factory($config = array())
    {
        $default = array(
            'base_url' => 'http://{hostname}:{port}',
            'hostname' => 'localhost',
            'port'     => 8080,
        );
        $required = array('hostname', 'port', 'base_url');
        $config = Collection::fromConfig($config, $default, $required);

        $client = new self($config->get('base_url'), $config);
        $client->setDescription(ServiceDescription::factory(__DIR__.DIRECTORY_SEPARATOR.'service.json'));

        $client->setDefaultHeaders(array(
            'Accept' => 'text/html',
        ));

        return $client;
    }

    public static function filterIngestData(array $data)
    {
        $result = '';

        foreach ($data as $line) {
            $result .= $line['userID'].','.$line['itemID'].(isset($line['value']) ? ','.$line['value'] : '').PHP_EOL;
        }

        return $result;
    }
}
