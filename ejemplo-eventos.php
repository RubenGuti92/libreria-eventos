<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

///**
// * Returns an authorized API client.
// * @return Google_Client the authorized client object
// */
//function getClient()
//{
//    $client = new Google_Client();
//    $client->setApplicationName('Insertar evento de google');
//    $client->setScopes(Google_Service_Calendar::CALENDAR);
//    $client->setAuthConfig('libreria-eventos-5a7500e35a0b.json');
//    $client->setAccessType('offline');
//    $client->setPrompt('select_account consent');
//
//    // Load previously authorized token from a file, if it exists.
//    // The file token.json stores the user's access and refresh tokens, and is
//    // created automatically when the authorization flow completes for the first
//    // time.
//    $tokenPath = 'token.json';
//    if (file_exists($tokenPath)) {
//        $accessToken = json_decode(file_get_contents($tokenPath), true);
//        $client->setAccessToken($accessToken);
//    }
//
//    // If there is no previous token or it's expired.
//    if ($client->isAccessTokenExpired()) {
//        // Refresh the token if possible, else fetch a new one.
//        if ($client->getRefreshToken()) {
//            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//        } else {
//            // Request authorization from the user.
//            $authUrl = $client->createAuthUrl();
//            printf("Open the following link in your browser:\n%s\n", $authUrl);
//            print 'Enter verification code: ';
//            $authCode = trim(fgets(STDIN));
//
//            // Exchange authorization code for an access token.
//            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//            $client->setAccessToken($accessToken);
//
//            // Check to see if there was an error.
//            if (array_key_exists('error', $accessToken)) {
//                throw new Exception(join(', ', $accessToken));
//            }
//        }
//        // Save the token to a file.
//        if (!file_exists(dirname($tokenPath))) {
//            mkdir(dirname($tokenPath), 0700, true);
//        }
//        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//    }
//    return $client;
//}

// Get the API client and construct the service object.
//$client = getClient();
//$client->setClientId('672737812241-n511b8v75nfgmeofhg1roce5gq2s0ois.apps.googleusercontent.com');
//$client->setClientSecret('B595UyQf1a6O7gMuEieT83Mq');
//    $client->setRedirectUri('http://google.es');
//$client->addScope('profile');
//
//$client->addScope(Google_Service_Calendar::CALENDAR);
//$service = new Google_Service_Calendar($client);

$client = new Google_Client();
$client->setApplicationName("TFG Eventos");
$client->setAuthConfig('libreria-eventos-5a7500e35a0b.json');
$client->setScopes([
    Google_Service_Calendar::CALENDAR
]);
//$client->setAccessType('offline');
$client->setSubject('ruben.gutierrez@sauki.es');

//$prueba = new DateTime('2020-05-04T21:00:00');
//$start = new Google_Service_Calendar_EventDateTime();
////$start->setDateTime("2020-05-04T21:00:00");
//$start->setTimeZone("Europe/Madrid");
//$start->setDateTime($prueba->format(\DateTime::RFC3339));
//
//$end = new Google_Service_Calendar_EventDateTime();
//$end_f = new DateTime('2020-05-04T21:00:00');
//$start->setTimeZone("Europe/Madrid");
//$end->setDateTime($end_f->format(\DateTime::RFC3339));

$start = new Google_Service_Calendar_EventDateTime();
$start->setDate('2020-05-15');


$event = new Google_Service_Calendar_Event(array(
    'id' => 'mievento4',
    'summary' => 'Primer Evento',
    'color' => 1,
//  'location' => '800 Howard St., San Francisco, CA 94103',
    'description' => 'Generamos el primer evento',
    'start' => $start,
    'end' => $start,
//    'start' => $start,
//    'end' => $end,
//  'end' => array(
//    'dateTime' => '2015-05-28T17:00:00-07:00',
//    'timeZone' => 'America/Los_Angeles',
//  ),
//  'recurrence' => array(
//    'RRULE:FREQ=DAILY;COUNT=2'
//  ),
//  'attendees' => array(
//    array('email' => 'ruben.gutierrez@sauki.es'),
//    array('email' => 'sbrin@example.com'),
//  ),
    'reminders' => array(
        'useDefault' => FALSE,
        'overrides' => array(
            array('method' => 'email', 'minutes' => 24 * 60),
            array('method' => 'popup', 'minutes' => 10),
        ),
    ),
));


/**
 * EJECUCIÓN
 */
$service = new Google_Service_Calendar($client);
////  //Obtener colores
// 	$colors = $service->colors->get();
//foreach ($colors->getEvent() as $key => $color) {
//    print "colorId : {$key}<br />";
//    print "  Background: {$color->getBackground()}<br />";
//    print "  Foreground: {$color->getForeground()}<br />";
//}
try {
    $eventos_creados = $service->events->insert('primary', $event);
    echo $eventos_creados->getId();

} catch (Google_Service_Exception $ex) {
    echo $ex;
}
