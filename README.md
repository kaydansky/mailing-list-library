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
Create selected provider instance:
```php
$mailChimp = $factory->mailChimp();
``` 
Fire the "lists" request in order to GET all mailing lists:
```php
$mailChimp->lists();
```
Get result as the object:
```php
$mailChimp->result;
```
Or an error message as alternative:
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
Fire the "addToList" request in order to POST new email to the list.
Required arguments are ``$listId`` and ``$emailAddress`` as API request won't work without.
Optional argument is ``$extraData`` array. Allows to post additional information along with the email.
Key names must correspond to field names supported by the API. 
For MailChimp default additional fields are: FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY, also users
are allowed to create their custom fields. [Learn more](https://mailchimp.com/help/set-default-merge-values/).
```php
$mailChimp->addToList($listId, $emailAddress, ['FNAME' => $firstName, 'LNAME' => $lastName]);
```
So every Provider will require the similar methods differing with the first 
function name only. E.g, AWeber call will be:
```php
$aweber = $factory->aweber();
$aweber->lists();
$aweber->addToList($listUrl, $emailAddress, ['name' => $firstName . ' ' . $lastName]);
```

## Examples

Example files located in "/examples" directory. Currently example for MailChimp is complete
and located in "/examples/mailchimp" directory.

Open "mailchimp.html" file in the browser. It is AJAX powered UI using mailchimp.php as back-end 
that you can refer for the Library usage in PHP.

I placed a copy to my test web host where you can try it right now:

MailChimp: [https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html](https://ruscoder.com/MailingListLibrary/examples/mailchimp/mailchimp.html)

AWeber: [https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.html](https://ruscoder.com/MailingListLibrary/examples/awebr/aweber.html)