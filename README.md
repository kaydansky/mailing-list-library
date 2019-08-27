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
Create an instance of the MailingListFactory providing some valid MailChimp's API Key.
Use this line for any of providers, just supplying appropriate credentials as arguments.
In particular, MailChimp requires the API Key only, so provide it here:  
```php 
$factory = new MailingListFactory($apiKey);
```
Fire the "lists" request of MailChimp API in order to GET all mailing lists.
The ```$response``` is an array having whether ```['result']``` key containing successful
response array as its value or ```['error']``` key with the error description string 
(same for every function of the Library). The first function "mailChimp()" instructs using 
the MailChimp's API:
```php
$response = $factory->mailChimp()->lists();
```
Fire the "addToList" request of MailChimp API in order to POST new email to the list.
Required arguments are ``$listId`` and ``$emailAddress`` as API request won't work without.
Optional argument is ``$extraData`` array. Allows to post additional information along with the email.
Key names must correspond to field names supported by the API. 
For MailChimp default additional fields are: FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY, also users
are allowed to create their custom fields. [Learn more](https://mailchimp.com/help/set-default-merge-values/).
```php
$response = $factory->mailChimp()->addToList($listId, $emailAddress, $extraData);
```
So every Provider will require the similar methods differing with the first 
function name only. E.g Aweber call will be:
```php
$response = $factory->aweber()->lists();
$response = $factory->aweber()->addToList($listId, $emailAddress, $extraData);
```

## Examples

Example files located in "/examples" directory. Currently example for MailChimp is complete
and located in "/examples/mailchimp" directory.

Open "mailchimp.html" file in the browser. It is AJAX powered UI using mailchimp.php as back-end 
that you can refer for the Library usage in PHP.

I placed a copy to my test web host where you can try it right now:

 [https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html](https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html)