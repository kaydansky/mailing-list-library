<?php
/**
 * @author: AlexK
 * Date: 21-Aug-19
 * Time: 1:10 PM
 */

namespace MailingListLibrary\Providers;

use GuzzleHttp\Exception\ClientException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Client;

class ProviderAweber
{
    private $provider;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $oauthUrl = 'https://auth.aweber.com/oauth2/';
    private $apiUrl = 'https://api.aweber.com/1.0/';
    private $resourceOwnerDetailsUrl = 'https://api.aweber.com/1.0/accounts';
    private $client;

    public $accessToken;
    public $refreshToken;
    public $expiresToken;
    public $alreadyExpired;
    public $result;
    public $error;

    public function __construct($clientId, $clientSecret, $redirectUri, $accessToken = '', $refreshToken = '', $expiresToken = '')
    {
        if (! $clientId || ! $clientSecret || ! $redirectUri) {
            $this->error = 'No Client ID nor Client Secret nor Redirect URI supplied';
            return false;
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresToken = $expiresToken;
        $this->client = new Client();

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

        $this->provider = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri'=> $this->redirectUri,
            'scopes' => $scopes,
            'scopeSeparator' => ' ',
            'urlAuthorize' => $this->oauthUrl . 'authorize',
            'urlAccessToken' => $this->oauthUrl . 'token',
            'urlResourceOwnerDetails' => $this->resourceOwnerDetailsUrl
        ]);

        if (! $this->accessToken && ! $this->refreshToken) {
            $this->authenticate();
        }
        elseif ($this->refreshToken && $this->expiresToken) {
            $this->checkExpiration();
        }

        return true;
    }

    public function lists()
    {
        $accounts = $this->getCollection($this->apiUrl . 'accounts');
        $listsUrl = $accounts[0]['lists_collection_link'];
        $this->result = $this->getCollection($listsUrl);
    }

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

    private function authenticate()
    {
        if (! isset($_GET['code'])) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $this->provider->getState();

            header('Location: ' . $authorizationUrl);
            exit;
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            $this->error = 'Invalid state';
            return false;
        } else {
            $this->getTokens('authorization_code', ['code' => $_GET['code']]);
        }

        return true;
    }

    private function checkExpiration()
    {
        if (time() > $this->expiresToken) {
            $this->refresh();
        }
    }

    private function refresh()
    {
        $this->getTokens('refresh_token', ['refresh_token' => $this->refreshToken]);
    }

    private function getTokens($grantType, array $params)
    {
        try {
            $token = $this->provider->getAccessToken($grantType, $params);
            $this->accessToken = $token->getToken();
            $this->refreshToken = $token->getRefreshToken();
            $this->expiresToken = $token->getExpires();
            $this->alreadyExpired = ($token->hasExpired() ? 'expired' : 'not expired');
            return true;
        } catch (IdentityProviderException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}