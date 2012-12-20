<?php

namespace BCC\Myrrix;

/**
 * MyrrixService helps you leverage the Myrrix REST api
 */
class MyrrixService
{
    /**
     * @var MyrrixClient
     */
    protected $client;

    /**
     * @param string $host     The hostname
     * @param int    $port     The port
     * @param string $username The username
     * @param string $password The password
     */
    function __construct($host, $port, $username = null, $password = null)
    {
        $this->client = MyrrixClient::factory(array(
            'hostname' => $host,
            'port'     => $port,
            'username' => $username,
            'password' => $password,
        ));
    }

    /**
     * Gets a recommendation for a known user
     *
     * @param int $userId The user id
     * @param int $count  The number of result to retrieve
     *
     * @return array
     */
    public function getRecommendation($userId, $count = null)
    {
        $command = $this->client->getCommand('GetRecommendation', array(
            'userId'  => $userId,
            'howMany'  => $count,
        ));

        return $this->client->execute($command)->json();
    }


    /**
     * Gets a recommendation for a list of known users
     *
     * @param array $userIds The user ids
     * @param int   $count   The number of result to retrieve
     *
     * @return array
     */
    public function getRecommendationToMany(array $userIds, $count = null)
    {
        $command = $this->client->getCommand('GetRecommendationToMany', array(
            'userIds'  => $userIds,
            'howMany'  => $count,
        ));

        return $this->client->execute($command)->json();
    }

    /**
     * Gets a recommendation for an unknown user, infer its tastes using a preference array.
     *
     * @param array $preferences The known preferences of the unknown user
     * @param int   $count       The number of result to retrieve
     *
     * @return array
     */
    public function getRecommendationToAnonymous(array $preferences = array(), $count = null)
    {
        $command = $this->client->getCommand('GetRecommendationToAnonymous', array(
            'preferences'  => $preferences,
            'howMany'      => $count,
        ));

        return $this->client->execute($command)->json();
    }

    /**
     * Sets a preference between a user and an item
     *
     * @param int   $userId The user id
     * @param int   $itemId The item id
     * @param float $value  The strength of the association
     *
     * @return bool
     */
    public function setPreference($userId, $itemId, $value = null)
    {
        $command = $this->client->getCommand('PostPref', array(
            'userID' => $userId,
            'itemID' => $itemId,
            'value'  => $value !== null ? (string)$value : null,
        ));

        return $this->client->execute($command)->isSuccessful();
    }

    /**
     * Sets a batch preference between users and items
     *
     * @param array $preferences An array of arrays with keys 'userID', 'itemID' and 'value'
     *
     * @return bool
     */
    public function setPreferences(array $preferences)
    {
        $command = $this->client->getCommand('Ingest', array(
            'data' => $preferences,
        ));

        return $this->client->execute($command)->isSuccessful();
    }

    /**
     * Removes a preference between a user and an item
     *
     * @param int   $userId The user id
     * @param int   $itemId The item id
     *
     * @return bool
     */
    public function removePreference($userId, $itemId)
    {
        $command = $this->client->getCommand('RemovePref', array(
            'userID' => $userId,
            'itemID' => $itemId,
        ));

        return $this->client->execute($command)->isSuccessful();
    }

    /**
     * Attempts to explain a recommendation by giving most significant associations of the model.
     *
     * @param int   $userId The user id
     * @param int   $itemId The item id
     *
     * @return array
     */
    public function getBecause($userId, $itemId)
    {
        $command = $this->client->getCommand('GetBecause', array(
            'userId'  => $userId,
            'itemID'  => $itemId,
        ));

        return $this->client->execute($command)->json();
    }

    /**
     * Gets recommendation scores for a user and some items

     * @param int   $userId  The user id
     * @param array $itemIds The item ids
     *
     * @return float[]
     */
    public function getEstimations($userId, array $itemIds)
    {
        $command = $this->client->getCommand('GetEstimation', array(
            'userId'  => $userId,
            'itemIDs' => $itemIds,
        ));

        $result = $this->client->execute($command)->getBody(true);

        return preg_split('/\r\n/', trim($result));
    }

    /**
     * Gets similar items
     *
     * @param array $itemIds The item ids
     * @param int   $count   The number of result to retrieve
     *
     * @return array
     */
    public function getSimilarItems(array $itemIds, $count = null)
    {
        $command = $this->client->getCommand('GetSimilarity', array(
            'itemIDs' => $itemIds,
            'howMany' => $count,
        ));

        return $this->client->execute($command)->json();
    }

    /**
     * Gets the list of users
     *
     * @return int[]
     */
    public function getUsers()
    {
        $command = $this->client->getCommand('GetAllUserIDs');

        return $this->client->execute($command)->json();
    }

    /**
     * Gets the list of items
     *
     * @return int[]
     */
    public function getItems()
    {
        $command = $this->client->getCommand('GetAllItemIDs');

        return $this->client->execute($command)->json();
    }

    /**
     * Asks Myrrix to refresh, may take time.
     *
     * @return bool
     */
    public function refresh()
    {
        $command = $this->client->getCommand('Refresh');

        return $this->client->execute($command)->isSuccessful();
    }


    /**
     * Asks if Myrrix is ready to answer requests.
     *
     * @return bool
     */
    public function isReady()
    {
        $command = $this->client->getCommand('Ready');

        return $this->client->execute($command)->isSuccessful();
    }

    /**
     * @return MyrrixClient
     */
    public function getClient()
    {
        return $this->client;
    }
}
