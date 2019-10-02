<?php
/**
 * ActiveCampaign API wrapper library example of use
 *
 * @author: AlexK
 * Date: 30-Sep-19
 * Time: 8:52 PM
 */

use MailingListLibrary\MailingListFactory;

require('../../vendor/autoload.php');
$apiKey = filter_input(INPUT_POST, 'api_key', FILTER_SANITIZE_STRING);
$baseUrl = filter_input(INPUT_POST, 'base_url', FILTER_SANITIZE_STRING);
$fetchLists = filter_input(INPUT_POST, 'fetch_lists', FILTER_SANITIZE_NUMBER_INT);
$emailAddress = filter_input(INPUT_POST, 'email_address', FILTER_SANITIZE_STRING);
$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$deleteContactId = filter_input(INPUT_POST, 'delete_contact_id', FILTER_SANITIZE_NUMBER_INT);

if ($apiKey && $baseUrl) {
    // Create an instance of the MailingListFactory
    $factory = new MailingListFactory();
    $activeCampaign = $factory->activeCampaign($apiKey, $baseUrl);
} else {
    echo json_encode(['error' => 'No API Key nor Base URL provided']);
    exit;
}

if ($fetchLists) {
    // Fire the "contacts" request of ActiveCampaign API in order to GET all contacts lists
    $activeCampaign->contacts();

    if ($activeCampaign->result) {
        $json = [];

        if (! count($activeCampaign->result['contacts'])) {
            echo json_encode(['result' => null, 'success' => 'Your Contact List is empty. Add new contact.']);
        } else {
            foreach ($activeCampaign->result['contacts'] as $value) {
                $json[] = [
                    'id' => $value['id'],
                    'name' => $value['firstName'] . ' ' . $value['lastName'],
                    'email' => $value['email']
                ];
            }

            echo json_encode(['result' => $json, 'success' => 'Your Contact List has been fetched']);
        }
    } else {
        echo json_encode(['error' => $activeCampaign->error]);
    }

    exit;
}

if ($emailAddress) {
    try {
        // Fire the "addContact" request to POST new email to the list
        // Supply the Email Address
        // 3rd argument is array of extra data where default names are:
        // firstName, lastName, phone
        $activeCampaign->addContact($emailAddress, ['firstName' => $firstName, 'lastName' => $lastName]);

        if ($activeCampaign->result) {
            echo json_encode(
                [
                    'result' => 1,
                    'success' => "Address {$activeCampaign->result['contact']['email']} has been added."
                ]);
        } else {
            echo json_encode(['error' => $activeCampaign->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

if ($deleteContactId) {
    $activeCampaign->deleteContact($deleteContactId);

    if ($activeCampaign->result) {
        echo json_encode(['result' => 1, 'success' => "Contact ID $deleteContactId has been removed"]);
    } else {
        echo json_encode(['error' => $activeCampaign->error]);
    }

    exit;
}

echo json_encode(['error' => 'No input data provided']);
exit;