<?php
/**
 * @author: AlexK
 * Date: 21-Aug-19
 * Time: 1:10 PM
 */

namespace MailingListLibrary\Providers;

use GuzzleHttp\{Exception\ClientException, Client};
use MailingListLibrary\OauthHandler;

class ProviderAweber extends OauthHandler
{
    private $oauthUrl = 'https://auth.aweber.com/oauth2/';
    private $apiUrl = 'https://api.aweber.com/1.0/';
    private $resourceOwnerDetailsUrl = 'https://api.aweber.com/1.0/accounts';
    private $client;

    public $accessToken;
    public $refreshToken;
    public $expiresToken;
    public $alreadyExpired;
    public $error;
    public $result;

    /**
     * ProviderAweber constructor.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $expiresToken
     */
    public function __construct($accessToken = '', $refreshToken = '', $expiresToken = '')
    {
        if (! defined('AWEBER_CLIENT_ID') || ! defined('AWEBER_CLIENT_SECRET') || ! defined('AWEBER_REDIRECT_URI')) {
            $this->error = 'No Client ID nor Client Secret nor Redirect URI supplied. Make sure the config file path is correct.';
            return false;
        }

        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresToken = $expiresToken;

        $scopes = [
            'account.read',
            'list.read',
            'list.write',
            'subscriber.read',
            'subscriber.write',
            'email.read',
            'email.write',
            'subscriber.read-extended'
        ];

        $this->clientId = AWEBER_CLIENT_ID;
        $this->clientSecret = AWEBER_CLIENT_SECRET;
        $this->redirectUri = AWEBER_REDIRECT_URI;
        $this->scopes = $scopes;
        $this->scopeSeparator = ' ';
        $this->urlAuthorize = $this->oauthUrl . 'authorize';
        $this->urlAccessToken = $this->oauthUrl . 'token';
        $this->urlResourceOwnerDetails = $this->resourceOwnerDetailsUrl;
        parent::__construct();

        if ($this->error) {
            return false;
        }

        $this->client = new Client();

        return true;
    }

    /**
     * Fetch mailing lists
     */
    public function lists()
    {
        $accounts = $this->getCollection($this->apiUrl . 'accounts');
        $listsUrl = $accounts[0]['lists_collection_link'];
        $this->result = $this->getCollection($listsUrl);
    }

    /**
     * Add new subscriber to mailing list
     *
     * @param $listUrl
     * @param $emailAddress
     * @param null $extraData {array}
     * @return bool
     */
    public function addToList($listUrl, $emailAddress, $extraData = null)
    {
        $data = array_merge(['email' => $emailAddress], $extraData);

        try {
            $body = $this->client->post($listUrl, [
                'json' => $data,
                'headers' => ['Authorization' => 'Bearer ' . $this->accessToken]
            ]);
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->error->message;
            return false;
        }

        $subscriberUrl = $body->getHeader('Location')[0];
        $subscriberResponse = $this->client->get($subscriberUrl,
            ['headers' => ['Authorization' => 'Bearer ' . $this->accessToken]])->getBody();

        $subscriber = json_decode($subscriberResponse, true);
        $this->result = $subscriber;

        return true;
    }

    /**
     * Get data collection by URL
     *
     * @param $url
     * @return array|bool
     */
    private function getCollection($url) {
        $collection = [];

        while (isset($url)) {
            try {
                $request = $this->client->get($url,
                    ['headers' => ['Authorization' => 'Bearer ' . $this->accessToken]]
                );
            } catch (ClientException $e) {
                $msg = json_decode($e->getResponse()->getBody()->getContents());
                $this->error = $msg->error->message;
                return false;
            }

            $body = $request->getBody();
            $page = json_decode($body, true);

            if ($request->getStatusCode() != 200) {
                $collection = array_merge($page['error'], $collection);
                $this->error = $collection[0]['message'];
            } else {
                $collection = array_merge($page['entries'], $collection);
            }

            $url = isset($page['next_collection_link']) ? $page['next_collection_link'] : null;
        }

        return $collection;
    }
}