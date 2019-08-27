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
use MailingListLibrary\Providers\ProviderAweber;
use MailingListLibrary\Providers\ProviderMailChimp;

class MailingListFactory
{
    private $ApiKey;
    private $OAuthClientId;
    private $OAuthClientSecret;
    private $OAuthRedirectUri;
    private $OAuthAccessToken;
    private $OAuthRefreshToken;
    private $OAuthExpiresToken;

    /**
     * MailingListFactory constructor.
     * @param string $ApiKey
     * @param string $OAuthClientId
     * @param string $OAuthClientSecret
     * @param string $OAuthRedirectUri
     * @param string $OAuthAccessToken
     * @param string $OAuthRefreshToken
     * @param string $OAuthExpiresToken
     */
    public function __construct(
        $ApiKey = '',
        $OAuthClientId = '',
        $OAuthClientSecret = '',
        $OAuthRedirectUri = '',
        $OAuthAccessToken = '',
        $OAuthRefreshToken = '',
        $OAuthExpiresToken = '')
    {
        $this->ApiKey = $ApiKey;
        $this->OAuthClientId = $OAuthClientId;
        $this->OAuthClientSecret = $OAuthClientSecret;
        $this->OAuthRedirectUri = $OAuthRedirectUri;
        $this->OAuthAccessToken = $OAuthAccessToken;
        $this->OAuthRefreshToken = $OAuthRefreshToken;
        $this->OAuthExpiresToken = $OAuthExpiresToken;
    }

    /**
     * @return ProviderMailChimp
     * @throws Exception
     */
    public function mailChimp(): ProviderMailChimp
    {
        return new ProviderMailChimp($this->ApiKey);
    }

    public function aweber(): ProviderAweber
    {
        return new ProviderAweber($this->OAuthClientId, $this->OAuthClientSecret, $this->OAuthRedirectUri, $this->OAuthAccessToken, $this->OAuthRefreshToken, $this->OAuthExpiresToken);
    }
}