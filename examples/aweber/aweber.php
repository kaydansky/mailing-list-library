<?php
/**
 * @author: AlexK
 * Date: 20-Aug-19
 * Time: 5:41 PM
 */

use MailingListLibrary\MailingListFactory;

require('../../vendor/autoload.php');

session_start();

$clientId = 'O9T6kB4BTeNsp2vFTbATrMsNQbYtfXck';
$clientSecret = '2ur7tArsAGfpURCxBiHp4oZFmMzoJgZH';
$redirectUri = 'https://ruscoder.com/MailingListLibrary/examples/aweber/aweber.php';

$fetchLists = filter_input(INPUT_GET, 'fetch_lists', FILTER_SANITIZE_NUMBER_INT);
$code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
$listUrl = filter_input(INPUT_POST, 'list_url', FILTER_SANITIZE_STRING);
$listName = filter_input(INPUT_POST, 'list_name', FILTER_SANITIZE_STRING);
$emailAddress = filter_input(INPUT_POST, 'email_address', FILTER_SANITIZE_STRING);
$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$oauthAccessToken = ! empty($_SESSION['accessTokenAweber']) ? $_SESSION['accessTokenAweber'] : '';
$oauthRefreshToken = ! empty($_SESSION['refreshTokenAweber']) ? $_SESSION['refreshTokenAweber'] : '';
$oauthExpiresToken = ! empty($_SESSION['expiresTokenAweber']) ? $_SESSION['expiresTokenAweber'] : '';
$tokenExpiredIn = '';
$successMessage = '';
$rows = '';

$factory = new MailingListFactory('/home3/plastic/public_html/MailingListLibrary/config/config.php');
$aweber = $factory->aweber($oauthAccessToken, $oauthRefreshToken, $oauthExpiresToken);

if ($aweber->accessToken && $aweber->refreshToken && $aweber->expiresToken) {
    $_SESSION['accessTokenAweber'] = $aweber->accessToken;
    $_SESSION['refreshTokenAweber'] = $aweber->refreshToken;
    $_SESSION['expiresTokenAweber'] = $aweber->expiresToken;
} elseif (! $aweber->error) {
    header('location: aweber.html');
}

if (! $aweber->error) {
    $tokenExpiredIn = ! empty($_SESSION['expiredTokenAweber']) ? $_SESSION['expiredTokenAweber'] : $aweber->expiresToken;

    if ($fetchLists) {
        $aweber->lists();

        if ($aweber->result) {
            foreach ($aweber->result as $value) {
                $rows .= '<tr>';
                $rows .= '<td>' . $value['id'] . '</td>';
                $rows .= '<td>' . $value['name'] . '</td>';
                $rows .= '<td>' . (intval($value['total_subscribed_subscribers']) + intval($value['total_unconfirmed_subscribers'])) . '</td>';
                $rows .= '<td class="text-nowrap"><form method="post" action="/MailingListLibrary/examples/aweber/aweber.php">
                        <div class="row"><div class="col-3 pr-0">
                        <input type="hidden" name="list_url" value="' . $value['subscribers_collection_link'] . '">
                        <input type="hidden" name="list_name" value="' . $value['name'] . '">
                        <input type="text" class="form-control" name="email_address" value="" placeholder="Email Address"></div>
                        <div class="col-3 pr-0">
                        <input type="text" class="form-control" name="first_name" value="" placeholder="First Name"></div>
                        <div class="col-3 pr-0">
                        <input type="text" class="form-control" name="last_name" value="" placeholder="Last Name"></div>
                        <div class="col-3">
                        <input type="submit" class="btn btn-info" value="Add To List"></div></div></form></td>';
                $rows .= '</tr>';
            }

            $successMessage = 'Your Mailing Lists have been fetched';
        }
    }

    if ($listUrl && $emailAddress) {
        $aweber->addToList($listUrl, $emailAddress, ['name' => $firstName . ' ' . $lastName]);

        if ($aweber->result) {
            $successMessage = "Address {$aweber->result['email']} has been added to list {$listName}. Status: {$aweber->result['status']}.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AWeber Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<div class="container mb-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">AWeber API Wrapper Example</h5>
                <div class="card-body">
                    <p id="TokenExpires" class="text-center">
                        <?php echo $tokenExpiredIn ? 'Token expires in ' . date('H:i', ($tokenExpiredIn - time())) : ''; ?>
                    </p>
                    <p class="text-center">
                        <a href="/MailingListLibrary/examples/aweber/aweber.php?fetch_lists=1"><button id="FetchLists" class="btn btn-primary">Fetch Lists</button></a>
                    </p>
                    <p id="errorAlert" class="text-center"><?php echo isset($aweber->error) ? '<div class="alert alert-danger text-center" role="alert">ERROR: ' . $aweber->error . '</div>' : '' ?></p>
                    <p id="successAlert" class="text-center"><?php echo $successMessage ? '<div class="alert alert-success text-center" role="alert">SUCCESS: ' . $successMessage . '</div>': '' ?></p>
                    <table id="listsTable" class="table <?php echo $rows ? 'd-block' : 'd-none'; ?>">
                        <thead>
                        <tr>
                            <th class="text-nowrap">List ID</th>
                            <th class="text-nowrap">List Name</th>
                            <th class="text-nowrap">Member Count</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php echo $rows ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>