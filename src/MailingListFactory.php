<?php
/**
 * Creates series of objects each representing particular Mailing Provider API wrapper
 *
 * @author: AlexK
 * Date: 17-Aug-19
 * Time: 3:45 PM
 */

namespace MailingListLibrary;

use Exception;
use MailingListLibrary\{Providers\ProviderAweber, Providers\ProviderMailChimp};

class MailingListFactory
{
    /**
     * MailingListFactory constructor.
     *
     * @param string $configPath
     */
    public function __construct($configPath = '')
    {
        if (file_exists($configPath)) {
            require_once($configPath);
        }
    }

    /**
     * Make MailChimp API wrapper instance
     *
     * @param $apiKey
     * @return ProviderMailChimp
     * @throws Exception
     */
    public function mailChimp($apiKey): ProviderMailChimp
    {
        return new ProviderMailChimp($apiKey);
    }

    /**
     * Make AWeber API wrapper instance
     *
     * @param $OAuthAccessToken
     * @param $OAuthRefreshToken
     * @param $OAuthExpiresToken
     * @return ProviderAweber
     */
    public function aweber($OAuthAccessToken, $OAuthRefreshToken, $OAuthExpiresToken): ProviderAweber
    {
        return new ProviderAweber($OAuthAccessToken, $OAuthRefreshToken, $OAuthExpiresToken);
    }
}