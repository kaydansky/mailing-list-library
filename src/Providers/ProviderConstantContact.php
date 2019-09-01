<?php
/**
 * @author: AlexK
 * Date: 29-Aug-19
 * Time: 9:51 PM
 */

namespace MailingListLibrary\Providers;

use GuzzleHttp\{Client, Exception\ClientException};
use MailingListLibrary\OauthHandler;

class ProviderConstantContact extends OauthHandler
{
    private $oauthUrl = 'https://oauth2.constantcontact.com/oauth2/';
    private $baseUrl = 'https://api.constantcontact.com/v2/';
    private $client;

    public $accessToken;
    public $expiresToken;
    public $alreadyExpired;
    public $error;
    public $result;

    /**
     * ProviderConstantContact constructor.
     *
     * @param string $accessToken
     * @param string $expiresToken
     */
    public function __construct($accessToken = '', $expiresToken = '')
    {
        if (! defined('CONSTANTCONTACT_API_KEY') || ! defined('CONSTANTCONTACT_CLIENT_SECRET') || ! defined('CONSTANTCONTACT_REDIRECT_URI')) {
            $this->error = 'No Client ID nor Client Secret nor Redirect URI supplied. Make sure the config file path is correct.';
            return false;
        }

        $this->accessToken = $accessToken;
        $this->expiresToken = $expiresToken;

        $this->clientId = CONSTANTCONTACT_API_KEY;
        $this->clientSecret = CONSTANTCONTACT_CLIENT_SECRET;
        $this->redirectUri = CONSTANTCONTACT_REDIRECT_URI;
        $this->urlAuthorize = $this->oauthUrl . 'oauth/siteowner/authorize';
        $this->urlAccessToken = $this->oauthUrl . 'oauth/token';
        parent::__construct();

        if ($this->error) {
            return false;
        }

        $this->client = new Client();
    }

    /**
     * Fetch mailing lists
     */
    public function lists()
    {
        try {
            $request = $this->client->get($this->baseUrl . 'lists?api_key=' . CONSTANTCONTACT_API_KEY,
                ['headers' => ['Authorization' => 'Bearer ' . $this->accessToken]]
            );
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg[0]->error_message;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }

    /**
     * Add new subscriber to mailing list
     *
     * @param $listId
     * @param $emailAddress
     * @param array|null $extraData
     * @return bool
     */
    public function addToList($listId, $emailAddress, array $extraData = null)
    {
        $data = array_merge(['email_addresses' => [['email_address' => $emailAddress]], 'lists' => [['id' => $listId]]], $extraData);

        try {
            $request = $this->client->post($this->baseUrl . 'contacts?api_key=' . CONSTANTCONTACT_API_KEY, [
                'json' => $data,
                'headers' => ['Authorization' => 'Bearer ' . $this->accessToken]
            ]);
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg[0]->error_message;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }
}