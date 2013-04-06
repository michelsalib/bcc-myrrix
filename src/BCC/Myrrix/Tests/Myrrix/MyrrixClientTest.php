<?php

namespace BCC\Myrrix\Tests\Myrrix;

use BCC\Myrrix\MyrrixClient;
use Guzzle\Http\Message\Response;
use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\RequestInterface;

class MyrrixClientTest extends GuzzleTestCase
{
    /**
     * @param MockPlugin $plugin
     * @param $code
     * @param null $body
     *
     * @return MyrrixClient
     */
    protected function prepareClient(MockPlugin $plugin, $code, $body = null)
    {
        $client = MyrrixClient::factory();
        $plugin->addResponse(new Response($code, null, $body));
        $client->addSubscriber($plugin);

        return $client;
    }

    /**
     * @param MockPlugin $plugin
     *
     * @return RequestInterface
     */
    protected function getRequest(MockPlugin $plugin)
    {
        $requests = $plugin->getReceivedRequests();

        return reset($requests);
    }

    public function testHomepage()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $response = $client->get()->send();

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080', $this->getRequest($plugin)->getUrl());
    }

    public function testWithUsernamePassword()
    {
        // ARRANGE
        $client = MyrrixClient::factory(array(
            'username' => 'test',
            'password' => '1234',
        ));

        // ACT
        $request = $client->createRequest();

        // ASSERT
        $this->assertEquals('Basic '.base64_encode('test:1234'), $request->getHeader('Authorization'));
    }

    public function testWithoutUsernamePassword()
    {
        // ARRANGE
        $client = MyrrixClient::factory();

        // ACT
        $request = $client->createRequest();

        // ASSERT
        $this->assertEquals(null, $request->getHeader('Authorization'));
    }

    public function testWithNullUsernamePassword()
    {
        // ARRANGE
        $client = MyrrixClient::factory(array(
            'username' => null,
            'password' => null,
        ));

        // ACT
        $request = $client->createRequest();

        // ASSERT
        $this->assertEquals(null, $request->getHeader('Authorization'));
    }

    public function testUserIds()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[123, 456]');

        // ACT
        $command = $client->getCommand('GetAllUserIDs');
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(array(123, 456), $response->json());
        $this->assertEquals('http://localhost:8080/user/allIDs', $this->getRequest($plugin)->getUrl());
    }

    public function testItemIds()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[12, 34]');

        // ACT
        $command = $client->getCommand('GetAllItemIDs');
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(array(12, 34), $response->json());
        $this->assertEquals('http://localhost:8080/item/allIDs', $this->getRequest($plugin)->getUrl());
    }

    public function testRecommendation()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetRecommendation', array('userID' => 2115287));
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/recommend/2115287', $this->getRequest($plugin)->getUrl());
    }

    public function testRecommendationToMany()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetRecommendationToMany',
            array('userIDs' => array(2115287, 2299226))
        );
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/recommendToMany/2115287/2299226', $this->getRequest($plugin)->getUrl());
    }

    public function testRecommendationToAnonymous()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetRecommendationToAnonymous',
            array('preferences' => array(115287 => 0.5, 2299226 => 0.7))
        );
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/recommendToAnonymous/115287=0.500000/2299226=0.700000', $this->getRequest($plugin)->getUrl());
    }

    public function testEstimationForAnonymous()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '0.65');

        // ACT
        $command = $client->getCommand('GetEstimationForAnonymous',
            array(
                'itemID'      => 135,
                'preferences' => array(115287 => 0.5, 2299226 => 0.7),
            )
        );
        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertEquals(0.65, $response->getBody(true));
        $this->assertEquals('http://localhost:8080/estimateForAnonymous/135/115287=0.500000/2299226=0.700000', $this->getRequest($plugin)->getUrl());
    }

    public function testReady()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('Ready');

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/ready', $this->getRequest($plugin)->getUrl());
    }

    public function testRefresh()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('Refresh');

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/refresh', $this->getRequest($plugin)->getUrl());
    }

    public function testBecause()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetBecause', array('userID' => 2115287, 'itemID' => 1020852));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/because/2115287/1020852', $this->getRequest($plugin)->getUrl());
    }

    public function testEstimate()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, <<<BODY
10.4
12.3

BODY
        );

        // ACT
        $command = $client->getCommand('GetEstimation', array('userID' => 2115287, 'itemIDs' => array(1020852, 1000272)));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertRegExp('/^([\d\.]+[^\d\.]+){2}$/', $response->getBody(true));
        $this->assertEquals('http://localhost:8080/estimate/2115287/1020852/1000272', $this->getRequest($plugin)->getUrl());
    }

    public function testSimilarity()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetSimilarity', array('itemIDs' => array(1020852, 1000272)));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/similarity/1020852/1000272', $this->getRequest($plugin)->getUrl());
    }

    public function testSimilarityToItem()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, <<<BODY
0.53
0.499

BODY
        );

        // ACT
        $command = $client->getCommand('GetSimilarityToItem', array(
            'toItemID' => 1020000,
            'itemIDs' => array(1020852, 1000272),
        ));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertRegExp('/^([\d\.]+[^\d\.]+){2}$/', $response->getBody(true));
        $this->assertEquals('http://localhost:8080/similarityToItem/1020000/1020852/1000272', $this->getRequest($plugin)->getUrl());
    }

    public function testMostPopularItems()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200, '[[325,0.53],[98,0.499]]');

        // ACT
        $command = $client->getCommand('GetMostPopularItems', array('itemIDs' => array(1020852, 1000272)));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(array(array(325,0.53), array(98,0.499)), $response->json());
        $this->assertEquals('http://localhost:8080/mostPopularItems', $this->getRequest($plugin)->getUrl());
    }

    public function testIngest()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('Ingest', array('data' => array(
            array("userID" => 2115287, "itemID" => 1, "value" => 0.234),
        )));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/ingest', $this->getRequest($plugin)->getUrl());
        $this->assertEquals(<<<BODY
2115287,1,0.234

BODY
            , (string)$this->getRequest($plugin)->getBody());
    }

    public function testPostPref()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('PostPref', array("userID" => 2115287, "itemID" => 1, "value" => (string)0.234));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/pref/2115287/1', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('POST', $this->getRequest($plugin)->getMethod());
        $this->assertEquals('0.234', (string)$this->getRequest($plugin)->getBody());
    }

    public function testRemovePref()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('RemovePref', array("userID" => 2115287, "itemID" => 1));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/pref/2115287/1', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('DELETE', $this->getRequest($plugin)->getMethod());
    }

    public function testPostUserTag()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('PostUserTag', array('userID' => 2115287, 'tag' => 'gender'));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/user/2115287/gender', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('POST', $this->getRequest($plugin)->getMethod());
        $this->assertEquals('1.0', (string)$this->getRequest($plugin)->getBody());
    }

    public function testPostUserTagWithValue()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('PostUserTag', array('userID' => 2115287, 'tag' => 'gender', 'value' => (string)2.0));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/user/2115287/gender', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('POST', $this->getRequest($plugin)->getMethod());
        $this->assertEquals('2', (string)$this->getRequest($plugin)->getBody());
    }

    public function testRemoveUserTag()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('RemoveUserTag', array('userID' => 2115287, 'tag' => 'gender'));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/user/2115287/gender', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('DELETE', $this->getRequest($plugin)->getMethod());
    }

    public function testPostItemTag()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('PostItemTag', array('itemID' => 2115287, 'tag' => 'color'));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/item/2115287/color', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('POST', $this->getRequest($plugin)->getMethod());
        $this->assertEquals('1.0', (string)$this->getRequest($plugin)->getBody());
    }

    public function testPostItemTagWithValue()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('PostItemTag', array('itemID' => 2115287, 'tag' => 'color', 'value' => (string)8.0));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/item/2115287/color', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('POST', $this->getRequest($plugin)->getMethod());
        $this->assertEquals('8', (string)$this->getRequest($plugin)->getBody());
    }

    public function testRemoveItemTag()
    {
        // ARRANGE
        $plugin = new MockPlugin();
        $client = $this->prepareClient($plugin, 200);

        // ACT
        $command = $client->getCommand('RemoveItemTag', array('itemID' => 2115287, 'tag' => 'color'));

        /** @var $response Response */
        $response = $client->execute($command);

        // ASSERT
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('http://localhost:8080/tag/item/2115287/color', $this->getRequest($plugin)->getUrl());
        $this->assertEquals('DELETE', $this->getRequest($plugin)->getMethod());
    }
}
