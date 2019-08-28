# Mailing Lists Library

Wrapper bundling a number of mailing list providers APIs, in PHP

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
Create an instance of the MailingListFactory.
Use it for any of providers, just supplying appropriate credentials as arguments.
In particular, MailChimp requires the API Key only, so provide it here:  
```php 
$factory = new MailingListFactory($apiKey);
```
You may want to enable several providers at once. Then simply supply all their credentials 
together:
```php
$factory = new MailingListFactory(
    $MailChimpApiKey, 
    $AWeberClientId, 
    $AWeberClientSecret, 
    $AWeberRedirectUri, 
    $AWeberOauthAccessToken, 
    $AWeberOauthRefreshToken, 
    $AWeberOauthExpiresToken);
```
Create selected provider instance:
```php
$mailChimp = $factory->mailChimp();
``` 
and/or
```php
$aweber = $factory->aweber();
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
Use same methods for every Provider. So AWeber requests will be:
```php
// Fetch lists
$aweber->lists();

// Add new subscriber
$extraData = ['name' => $firstName . ' ' . $lastName];
$aweber->addToList($listUrl, $emailAddress, $extraData);
```
Unlike MailChimp using the list ID number the AWeber is using the list URL 
to define the list where subscriber is added to.
This URL is returned with the ```lists()``` method as ```$aweber->result[$i]['subscribers_collection_link']``` value 
looking like ```"https://api.aweber.com/1.0/accounts/123/lists/456/subscribers"```.

#### Optional data

The ```$extraData``` argument is an array with optional data (fields) to supply along with new subscriber email 
address. Every provider has their own field names. In order to comply, your array's key names must correspond to 
the field names supported by the provider. 

MailChimp default optional fields are: FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY, also users
are allowed to create their custom fields. [Learn more](https://mailchimp.com/help/set-default-merge-values/).   

AWeber default optional fields are: ad_tracking, custom_fields, ip_address, last_followup_message_number_sent, 
misc_notes, name, tags. [Learn more](https://api.aweber.com/#tag/Subscribers/paths/~1accounts~1{accountId}~1lists~1{listId}~1subscribers/post).

## Examples

Example files located in "/examples" directory. Just open /examples/mailchimp/mailchimp.html or 
/examples/aweber/aweber.html in your browser.

I placed a copy to my test web host where you can try it right now:

MailChimp: [https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html](https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html)

AWeber: [https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.html](https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.html)