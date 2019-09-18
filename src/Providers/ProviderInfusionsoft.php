<?php
/**
 * @author: AlexK
 * Date: 18-Sep-19
 * Time: 2:54 PM
 */

namespace MailingListLibrary\Providers;

use GuzzleHttp\{Client, Exception\ClientException};
use MailingListLibrary\OauthHandler;

class ProviderInfusionsoft extends OauthHandler
{
    private $oauthUrl = 'https://signin.infusionsoft.com/app/oauth/authorize';
    private $tokenUrl = 'https://api.infusionsoft.com/token';
    private $baseUrl = 'https://api.infusionsoft.com/crm/rest/v1/';
    private $client;

    public $accessToken;
    public $refreshToken;
    public $expiresToken;
    public $alreadyExpired;
    public $error;
    public $result;

    /**
     * ProviderInfusionsoft constructor.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $expiresToken
     */
    public function __construct($accessToken = '', $refreshToken = '', $expiresToken = '')
    {
        if (! defined('INFUSIONSOFT_CLIENT_ID') || ! defined('INFUSIONSOFT_CLIENT_SECRET') || ! defined('INFUSIONSOFT_REDIRECT_URI')) {
            $this->error = 'No Client ID nor Client Secret nor Redirect URI supplied. Make sure the config file path is correct.';
            return false;
        }

        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresToken = $expiresToken;

        $this->clientId = INFUSIONSOFT_CLIENT_ID;
        $this->clientSecret = INFUSIONSOFT_CLIENT_SECRET;
        $this->redirectUri = INFUSIONSOFT_REDIRECT_URI;
        $this->urlAuthorize = $this->oauthUrl;
        $this->urlAccessToken = $this->tokenUrl;
        parent::__construct();

        if ($this->error) {
            return false;
        }

        $this->client = new Client();
        return true;
    }

    /**
     * Fetch contacts
     *
     * @return bool
     */
    public function contacts()
    {
        try {
            $request = $this->client->get($this->baseUrl . 'contacts',
                ['headers' => ['Authorization' => 'Bearer ' . $this->accessToken]]
            );
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->message;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }

    /**
     * Create new contact
     *
     * @param $email
     * @param array|null $extraData
     * @return bool
     */
    public function addContact($email, array $extraData = null)
    {
        $email1 = new \stdClass;
        $email1->field = 'EMAIL1';
        $email1->email = $email;
        $data = array_merge(['email_addresses' => [$email1]], $extraData);

        try {
            $request = $this->client->post($this->baseUrl . 'contacts', [
                'json' => $data,
                'headers' => ['Authorization' => 'Bearer ' . $this->accessToken]
            ]);
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->message;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }
}