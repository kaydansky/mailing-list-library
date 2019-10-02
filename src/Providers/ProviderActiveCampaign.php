<?php
/**
 * @author: AlexK
 * Date: 01-Oct-19
 * Time: 8:47 PM
 */

namespace MailingListLibrary\Providers;

use GuzzleHttp\{Client, Exception\ClientException};

class ProviderActiveCampaign
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public $error;
    public $result;

    /**
     * ProviderActiveCampaign constructor.
     *
     * @param $apiKey
     * @param $baseUrl
     */
    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
        return true;
    }

    /**
     * Fetch contact list
     *
     * @return bool
     */
    public function contacts()
    {
        try {
            $request = $this->client->get($this->baseUrl . '/api/3/contacts',
                ['headers' => ['Api-Token' => $this->apiKey]]
            );
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->errors[0]->title;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }

    /**
     * Add new contact along with additional info
     *
     * @param $email
     * @param array|null $extraData
     * @return bool
     */
    public function addContact($email, array $extraData = null)
    {
        try {
            $request = $this->client->post($this->baseUrl . '/api/3/contacts', [
                'json' => ['contact' => array_merge(['email' => $email], $extraData)],
                'headers' => ['Api-Token' => $this->apiKey]
            ]);
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->errors[0]->title;
            return false;
        }

        $this->result = json_decode($request->getBody(), true);
        return true;
    }

    /**
     * Delete contact
     *
     * @param $contactId
     * @return bool
     */
    public function deleteContact($contactId)
    {
        try {
            $request = $this->client->delete($this->baseUrl . '/api/3/contacts/' . $contactId, [
                'headers' => ['Api-Token' => $this->apiKey]
            ]);
        } catch (ClientException $e) {
            $msg = json_decode($e->getResponse()->getBody()->getContents());
            $this->error = $msg->message;
            return false;
        }

        $this->result = true;
        return true;
    }
}