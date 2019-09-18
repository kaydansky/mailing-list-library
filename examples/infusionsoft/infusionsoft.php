<?php
/**
 * Infusionsoft API wrapper library example of use
 *
 * @author: AlexK
 * Date: 18-Sep-19
 * Time: 2:04 PM
 */

use MailingListLibrary\MailingListFactory;

require('../../vendor/autoload.php');

session_start();

$fetchContacts = filter_input(INPUT_GET, 'fetch_contacts', FILTER_SANITIZE_NUMBER_INT);
$emailAddress = filter_input(INPUT_POST, 'email_address', FILTER_SANITIZE_STRING);
$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$oauthAccessToken = ! empty($_SESSION['accessTokenInfusionsoft']) ? $_SESSION['accessTokenInfusionsoft'] : '';
$oauthRefreshToken = ! empty($_SESSION['refreshTokenInfusionsoft']) ? $_SESSION['refreshTokenInfusionsoft'] : '';
$oauthExpiresToken = ! empty($_SESSION['expiresTokenInfusionsoft']) ? $_SESSION['expiresTokenInfusionsoft'] : '';
$tokenExpiredIn = '';
$successMessage = '';
$rows = '';

$factory = new MailingListFactory('/home3/plastic/public_html/MailingListLibrary/config/config.php');
$infusionsoft = $factory->infusionsoft($oauthAccessToken, $oauthRefreshToken, $oauthExpiresToken);

if ($infusionsoft->accessToken && $infusionsoft->refreshToken && $infusionsoft->expiresToken) {
    $_SESSION['accessTokenInfusionsoft'] = $infusionsoft->accessToken;
    $_SESSION['refreshTokenInfusionsoft'] = $infusionsoft->refreshToken;
    $_SESSION['expiresTokenInfusionsoft'] = $infusionsoft->expiresToken;
} elseif (! $infusionsoft->error) {
    header('location: infusionsoft.html');
}

if (! $infusionsoft->error) {
    $tokenExpiredIn = ! empty($_SESSION['expiresTokenInfusionsoft']) ? $_SESSION['expiresTokenInfusionsoft'] : $infusionsoft->expiresToken;

    if ($fetchContacts) {
        $infusionsoft->contacts();

        if ($infusionsoft->result) {
            foreach ($infusionsoft->result['contacts'] as $value) {
                $rows .= '<tr>';
                $rows .= '<td>' . $value['id'] . '</td>';
                $rows .= '<td>' . $value['email_addresses'][0]['email'] . '</td>';
                $rows .= '<td>' . $value['email_addresses'][0]['field'] . '</td>';
                $rows .= '<td>' . $value['email_status'] . '</td>';
                $rows .= '</tr>';
            }

            $successMessage = 'Your Contact List has been fetched';
        }
    }

    if ($emailAddress) {
        $infusionsoft->addContact($emailAddress, ['given_name' => $firstName, 'family_name' => $lastName]);

        if ($infusionsoft->result) {
            $successMessage = "Contact {$infusionsoft->result['email_addresses'][0]['email']} has been created. Status: {$infusionsoft->result['email_status']}";
        }
    }

    function date_24midnight($format, $ts)
    {
        if (date('Hi', $ts) == '0000') {
            $replace = array(
                'H' => '24',
                'G' => '24',
                'i' => '00',
            );

            return date(
                str_replace(
                    array_keys($replace),
                    $replace,
                    $format
                ),
                $ts - 60
            );
        } else {
            return date($format, $ts);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Infusionsoft Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous" rel="stylesheet">
</head>
<body>
<div class="container mb-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Infusionsoft API Wrapper Example</h5>
                <div class="card-body">
                    <p id="TokenExpires" class="text-center">
                        <?php echo $tokenExpiredIn ? 'Token expires in ' . date_24midnight('H:i', ($tokenExpiredIn - time())) : ''; ?>
                    </p>
                    <p class="text-center">
                        <a href="/MailingListLibrary/examples/infusionsoft/infusionsoft.php?fetch_contacts=1"><button id="FetchContacts" class="btn btn-primary">Fetch Contacts</button></a>
                    </p>
                    <p id="errorAlert" class="text-center"><?php echo isset($infusionsoft->error) ? '<div class="alert alert-danger text-center" role="alert">ERROR: ' . $infusionsoft->error . '</div>' : '' ?></p>
                    <p id="successAlert" class="text-center"><?php echo $successMessage ? '<div class="alert alert-success text-center" role="alert">SUCCESS: ' . $successMessage . '</div>': '' ?></p>
                    <form method="post" action="/MailingListLibrary/examples/infusionsoft/infusionsoft.php" class="<?php echo $rows ? 'd-block' : 'd-none'; ?> my-4">
                        <div class="card">
                            <div class="card-body bg-light">
                                <div class="row justify-content-center">
                                    <div class="col-2 pr-0">
                                        <input type="text" class="form-control" name="email_address" value="" placeholder="Email Address">
                                    </div>
                                    <div class="col-2 pr-0">
                                        <input type="text" class="form-control" name="first_name" value="" placeholder="First Name">
                                    </div>
                                    <div class="col-2 pr-0">
                                        <input type="text" class="form-control" name="last_name" value="" placeholder="Last Name">
                                    </div>
                                    <div class="col-2">
                                        <input type="submit" class="btn btn-info" value="Create  Contact">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table id="listsTable" class="table <?php echo $rows ? 'd-block' : 'd-none'; ?>">
                        <thead>
                        <tr>
                            <th class="text-nowrap">Contact ID</th>
                            <th class="text-nowrap">Email</th>
                            <th class="text-nowrap">Field</th>
                            <th class="text-nowrap">Status</th>
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