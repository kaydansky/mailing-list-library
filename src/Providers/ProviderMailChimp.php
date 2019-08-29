<?php
/**
 * @author: AlexK
 * Date: 17-Aug-19
 * Time: 8:28 PM
 */

namespace MailingListLibrary\Providers;

use DrewM\MailChimp\MailChimp;
use Exception;

class ProviderMailChimp
{
    private $class;

    public $result;
    public $error;

    /**
     * ProviderMailChimp constructor.
     *
     * @param $apiKey
     * @throws Exception
     */
    public function __construct($apiKey)
    {
        $this->class = new MailChimp($apiKey);
    }

    /**
     * Fetch mailing lists
     *
     * @return bool
     */
    public function lists()
    {
        return $this->output($this->class->get('lists'));
    }

    /**
     * Add new subscriber to mailing list
     *
     * @param $listId
     * @param $emailAddress
     * @param null $extraData {array} | Default key names: FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY
     * @return bool
     */
    public function addToList($listId, $emailAddress, $extraData = null)
    {
        $array = ['email_address' => $emailAddress, 'status' => 'subscribed'];

        if ($extraData && is_array($extraData) && count($extraData)) {
            $array['merge_fields'] = $extraData;
        }

        return $this->output($this->class->post("lists/$listId/members", $array));
    }

    /**
     * Output/Error handler
     *
     * @param $result
     * @return bool
     */
    private function output($result)
    {
        if ($this->class->success()) {
            $this->result = $result;
            return true;
        } else {
            $this->error = $this->class->getLastError();
            return false;
        }
    }
}