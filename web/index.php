<?php
use \controllers\racerController;

require_once '../controllers/racerController.php';
require '../vendor/autoload.php';
session_start();
$fb = new Facebook\Facebook([
  'app_id' => '606990849742582', // Replace {app-id} with your app id
  'app_secret' => '2e7dddf85e6aceabb7ca0fa4cf52a75a',
  'default_graph_version' => 'v2.2',
  ]);



$racer = new racerController;

$racer->buildToken($fb);

if (isset($_SESSION['fb_access_token'])) {
    if (empty($_SESSION['user_id'])) {
        $racer->displayRacers($fb);
    }
}

if (isset($_GET['hash'])) {
    $racer->vote($_GET['hash']);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ('/index.php' === $uri || '/' === $uri || $uri === '') {
    if (!isset($_SESSION['fb_access_token'])) {
        $racer->getRacersData($fb);
    } else {
        $racerUser = getRacerDataById($_SESSION['user_id']);
        $racer->getRacersData($fb, hash('sha256', $racerUser['email_facebook']));
    }
} elseif ('/success' === $uri) {
    $racer->getPlusOnePoint($_SESSION['name']);
} elseif ('/failed' === $uri) {
    $racer->failedToGetMorePoint($_SESSION['name']);
} elseif ($uri ==  '/index.php/logout') {
        $racer->logout();
}
