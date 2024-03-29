# Mailing Lists Library

Wrapper bundling a number of mailing list providers APIs, in PHP

Providers currently implemented:

* ActiveCampaign
* AWeber
* ConstantContact
* Infusionsoft
* MailChimp

## Requirements

* PHP 7.3.0 - 7.3.8
* PHP cURL Extension
* CA root certificate for SSL verification. [Learn more](http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/)

## Installation

* Extract the archive content into directory on your server
* Add Composer autoloader (most likely you have it already) to your application with this line: ```require("vendor/autoload.php");```
* Include MailingListFactory.php (e.g ```use MailingListLibrary\MailingListFactory;```)

## Usage

Include the Library and run the autoloader:
```php
use MailingListLibrary\MailingListFactory;
require("vendor/autoload.php");
```
Create an instance of the MailingListFactory:  
```php 
$factory = new MailingListFactory();
```
Create selected provider instance. Supply required credentials as arguments.

**MailChimp** requires the API key:
```php
$apiKey = 'abc123abc123abc123abc123abc123-us1';
$mailChimp = $factory->mailChimp($apiKey);
``` 
Fire the "lists" request (say to MailChimp) in order to GET all mailing lists:
```php
$mailChimp->lists();
```
Get result as the object:
```php
$mailChimp->result;
```
Or an error message string as alternative:
```php
$mailChimp->error;
```
You may check the response as follows:
```php
if ($mailChimp->result) {
    // Successful response to show:
    var_dump($mailChimp->result);
} elseif ($mailChimp->error) {
    // Error occured:
    echo $mailChimp->error;
}
```
Then parse the ```$mailChimp->result``` array to find out parameters required for further operations,
e.g, the list ID number (```$listId```) as the ```$mailChimp->result['lists'][$i]['id']```.
 
Fire the "addToList" request in order to POST new email to the list.
Required arguments are ``$listId`` and ``$emailAddress`` as API request won't work without.
Optional argument is ``$extraData`` array allowing to post the optional data along with 
the email (see below).
```php
$extraData = ['FNAME' => $firstName, 'LNAME' => $lastName];
$mailChimp->addToList($listId, $emailAddress, $extraData);
```
AWeber is using OAuth authentication. Therefore their API requires 6 parameters including 
3 constant: Client ID, Client Secret, Redirect URI and 3 dynamic: Access Token, Refresh Token 
and Token Expiration Timestamp. 

Create some configuration file to store your constants. For example, ```/config/config.php``` 
and define constants with your credentials (from your AWeber account):
```php
define('AWEBER_CLIENT_ID', 'O9T6kB4BTeNsp2vFTbATrMsNQbYtfXck');
define('AWEBER_CLIENT_SECRET', '2ur7tArsAGfpURCxBiHp4oZFmMzoJgZH');
define('AWEBER_REDIRECT_URI', 'https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.php');
```
So creating the instance is: 
```php
// Supply the real path of your configuration file as argument
$factory = new MailingListFactory('/your_server_root/path/config/config.php');

// Or get the real path from relative one
$factory = new MailingListFactory(realpath('../../config/config.php'));

// Supply saved tokens and expiration timestamp if any. Those are opotional arguments, 
// if empty then user will be redirected to authorization page: 
$aweber = $factory->aweber($oauthAccessToken, $oauthRefreshToken, $oauthExpiresToken);
```
Use same methods for API operations:
```php
// Fetch lists
$aweber->lists();

// Add new subscriber
$extraData = ['name' => $firstName . ' ' . $lastName];
$aweber->addToList($listUrl, $emailAddress, $extraData);
```
**AWeber** (unlike MailChimp) is using the list URL to define the list where subscriber is added to.
This URL is returned with the ```lists()``` method as ```$aweber->result[$i]['subscribers_collection_link']``` value 
looking like ```"https://api.aweber.com/1.0/accounts/123/lists/456/subscribers"```.

**ConstantContact** has OAuth authentication as well. Unlike AWeber they issue the access token for 
10 years and don't provide the refresh token. I.e. your system should store and pass to 
 ```$factory->constantContact()``` the access token and token expiration timestamp only 
 (actually the access token only would be enough as the expiration is 10 years ahead). 
 Subscriber first and last name field names are particular (see Optional Data below). The rest is same as for AWeber.
 
**Infusionsoft** is OAuth too. It is particular having no "list" concept at all. 
They are using a single Contact List to store all 
email addresses along with additional contact information. Therefore new methods created:

```contacts()``` instead ```lists()``` and ```addContact()``` instead ```addToList()```

### API methods summary

```$factory = new MailingListFactory();``` the factory object.

MailChimp:

```php
/**
 * MailChimp object
 * @param string $apiKey
 */
$mailChimp = $factory->mailChimp($apiKey);

/**
 * Fetch mailing lists
 */
$mailChimp->lists();

/**
 * Add new subscriber to mailing list
 * @param string|int $listId
 * @param string $emailAddress
 * @param array|null $extraData optional
 */
$mailChimp->addToList($listId, $emailAddress, $extraData);
```

AWeber:

```php
/**
 * AWeber object
 * @param string|null $OAuthAccessToken optional
 * @param string|null $OAuthRefreshToken optional
 * @param string|null $OAuthExpiresToken optional
 */
$aweber = $factory->aweber($OAuthAccessToken, $OAuthRefreshToken, $OAuthExpiresToken);

/**
 * Fetch mailing lists
 */
$aweber->lists();

/**
 * Add new subscriber to mailing list
 * @param string $listUrl
 * @param string $emailAddress
 * @param array|null $extraData optional
 */
$aweber->addToList($listUrl, $emailAddress, $extraData);
```

ConstantContact:

```php
/**
 * ConstantContact object
 * @param string|null $OAuthAccessToken optional
 * @param string|null $OAuthExpiresToken optional
 */
$constantContact = $factory->constantContact($OAuthAccessToken, $OAuthExpiresToken);

/**
 * Fetch mailing lists
 */
$constantContact->lists();

/**
 * Add new subscriber to mailing list
 * @param string|int $listId
 * @param string $emailAddress
 * @param array|null $extraData optional
 */
$constantContact->addToList($listId, $emailAddress, $extraData);
```

Infusionsoft:

```php
/**
 * Infusionsoft object
 * @param string|null $OAuthAccessToken optional
 * @param string|null $OAuthRefreshToken optional
 * @param string|null $OAuthExpiresToken optional
 */
$infusionsoft = $factory->infusionsoft($OAuthAccessToken, $OAuthRefreshToken, $OAuthExpiresToken);

/**
 * Fetch contacts
 */
$infusionsoft->contacts();

/**
 * Create new contact
 * @param string $email
 * @param array|null $extraData optional
 */
$infusionsoft->addContact($email, $extraData);
```

ActiveCampaign:

```php
/**
 * ActiveCampaign object
 * @param string $apiKey
 * @param string $baseUrl
 */
$activeCampaign = $factory->activeCampaign($apiKey, $baseUrl);

/**
 * Fetch contacts
 */
$activeCampaign->contacts();

/**
 * Create new contact
 * @param string $email
 * @param array|null $extraData optional
 */
$activeCampaign->addContact($email, $extraData);

/**
 * Delete contact
 * @param int $contactId
 */
$activeCampaign->deleteContact($contactId);
```

### Optional data

The ```$extraData``` argument is an array with optional data (fields) to supply along with new subscriber email 
address. Every provider has their own field names. In order to comply, your array's key names must correspond to 
the field names supported by the provider. 

**MailChimp** default optional fields are: FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY, also users
are allowed to create their custom fields. [Learn more](https://mailchimp.com/help/set-default-merge-values/).   

**AWeber** default optional fields are: ad_tracking, custom_fields, ip_address, last_followup_message_number_sent, 
misc_notes, name, tags. [Learn more](https://api.aweber.com/#tag/Subscribers/paths/~1accounts~1{accountId}~1lists~1{listId}~1subscribers/post).

**ConstantContact** has a lot of optional fields. 
[Learn more](https://developer.constantcontact.com/docs/contacts-api/contacts-collection.html?method=POST). 
For the subscriber name use: ```['first_name' => 'value', 'last_name' => 'value']```.

**Infusionsoft** has a lot of optional fields (some of them are of the object type).
[Learn more](https://developer.infusionsoft.com/docs/rest/#!/Contact/createContactUsingPOST).
For the subscriber name use: ```['given_name' => 'value', 'family_name' => 'value']```.

**ActiveCampaign** optional fields are: firstName, lastName, phone.
[Learn more](https://developers.activecampaign.com/reference#create-contact).
For the subscriber name use: ```['firstName' => 'value', 'lastName' => 'value']```.  

## Examples

Example files located in "/examples" directory. Just open /examples/mailchimp/mailchimp.html or 
/examples/aweber/aweber.html in your browser.

I placed a copy to my test web host where you can try it right now:

MailChimp: [https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html](https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html)

AWeber: [https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.html](https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.html)

ConstantContact: [https://ruscoder.com/MailingListLibrary/examples/constantcontact/constantcontact.html](https://ruscoder.com/MailingListLibrary/examples/constantcontact/constantcontact.html)

Infusionsoft: [https://ruscoder.com/MailingListLibrary/examples/infusionsoft/infusionsoft.html](https://ruscoder.com/MailingListLibrary/examples/infusionsoft/infusionsoft.html)

ActiveCampaign: [https://ruscoder.com/MailingListLibrary/examples/activecampaign/activecampaign.html](https://ruscoder.com/MailingListLibrary/examples/activecampaign/activecampaign.html)