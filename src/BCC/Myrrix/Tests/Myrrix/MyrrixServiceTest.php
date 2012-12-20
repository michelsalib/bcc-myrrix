<?php

namespace BCC\Myrrix\Tests\Myrrix;

use BCC\Myrrix\MyrrixClient;
use Guzzle\Http\Message\Response;
use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit_Framework_TestCase;
use BCC\Myrrix\MyrrixService;

class MyrrixServiceTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        // ARRANGE
        $myrrix = new MyrrixService('host', 1234, 'testname', 'pa$$word');

        // ACT
        $client = $myrrix->getClient();

        // ASSERT
        $this->assertEquals('host', $client->getConfig('hostname'));
        $this->assertEquals(1234, $client->getConfig('port'));
        $this->assertEquals('testname', $client->getConfig('username'));
        $this->assertEquals('pa$$word', $client->getConfig('password'));
    }
}
