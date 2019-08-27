<?php
/**
 * MailChimp API wrapper library example of use
 *
 * @author: AlexK
 * Date: 17-Aug-19
 * Time: 11:10 PM
 */

use MailingListLibrary\MailingListFactory;

require('../../vendor/autoload.php');
$apiKey = filter_input(INPUT_POST, 'api_key', FILTER_SANITIZE_STRING);
$fetchLists = filter_input(INPUT_POST, 'fetch_lists', FILTER_SANITIZE_NUMBER_INT);
$listId = filter_input(INPUT_POST, 'list_id', FILTER_SANITIZE_STRING);
$emailAddress = filter_input(INPUT_POST, 'email_address', FILTER_SANITIZE_STRING);
$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);

if ($apiKey) {
    // Create an instance of the MailingListFactory
    // Provide some valid MailChimp's API Key
    $factory = new MailingListFactory($apiKey);

    try {
        $mailChimp = $factory->mailChimp();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No API Key provided']);
    exit;
}

if ($fetchLists) {
    try {
        // Fire the "lists" request of MailChimp API in order to GET all mailing lists
        $mailChimp->lists();

        if ($mailChimp->result) {
            $json = [];

            foreach ($mailChimp->result['lists'] as $value) {
                $json[] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'member_count' => $value['stats']['member_count']
                ];
            }

            echo json_encode(['result' => $json, 'success' => 'Your Mailing Lists have been fetched']);
        } else {
            echo json_encode(['error' => $mailChimp->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

if ($listId && $emailAddress) {
    try {
        // Fire the "addToList" request of MailChimp API in order to POST new email to the list
        // Supply the list ID and the Email Address
        // 3rd argument is array of extra data (merge_field by MailChimp) where default names are:
        // FNAME, LNAME, ADDRESS, PHONE, BIRTHDAY
        // MailChimp users may create their custom merge_field names
        $mailChimp->addToList($listId, $emailAddress, ['FNAME' => $firstName, 'LNAME' => $lastName]);

        if ($mailChimp->result) {
            echo json_encode(
                [
                    'success' =>
                        "Address {$emailAddress} has been added to list {$listId}.
                        <br>Note, the member count is updating within few minutes.
                        Try fetching lists later to see change."
                ]);
        } else {
            echo json_encode(['error' => $mailChimp->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

echo json_encode(['error' => 'No input data provided']);
exit;