<?php
/**
 * @author: AlexK
 * Date: 30-Aug-19
 * Time: 12:56 PM
 */

namespace MailingListLibrary;

use League\OAuth2\Client\{Provider\Exception\IdentityProviderException, Provider\GenericProvider};

class OauthHandler
{
    protected $provider;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes;
    protected $scopeSeparator;
    protected $urlAuthorize;
    protected $urlAccessToken;
    protected $urlResourceOwnerDetails;

    public $accessToken;
    public $refreshToken;
    public $expiresToken;
    public $alreadyExpired;
    public $error;

    /**
     * OauthHandler constructor.
     */
    protected function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri'=> $this->redirectUri,
            'scopes' => $this->scopes,
            'scopeSeparator' => $this->scopeSeparator,
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails
        ]);

        if (! $this->accessToken && ! $this->refreshToken) {
            $this->handleFlow();
        }
        elseif ($this->refreshToken && $this->expiresToken) {
            $this->checkExpiration();
        }
    }


    /**
     * OAuth authentication workflow handler
     *
     * @return bool
     */
    private function handleFlow()
    {
        $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
        $state = filter_input(INPUT_GET, 'state', FILTER_SANITIZE_STRING);

        if (! $code) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $this->provider->getState();
            header('Location: ' . $authorizationUrl);
            exit;
        } elseif (! $state || (isset($_SESSION['oauth2state']) && $state !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }

            $this->error = 'Invalid state';
            return false;
        } else {
            $this->getTokens('authorization_code', ['code' => $code]);
        }

        return true;
    }

    /**
     * Check if access token expired and if so then refresh it
     */
    private function checkExpiration()
    {
        if (time() > $this->expiresToken) {
            $this->getTokens('refresh_token', ['refresh_token' => $this->refreshToken]);
        }
    }

    /**
     * Attempt obtaining OAuth tokens
     *
     * @param $grantType
     * @param array $params
     * @return bool
     */
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