<?php
namespace controllers;

require '../models/model.php';

class racerController
{
    public function logout()
    {
        session_start();
        if (session_destroy()) {
            header("Location: http://localhost");
        }
    }

    public function getRacersData($fb, $url = null)
    {
            $helper = $fb->getRedirectLoginHelper();

            $permissions = ['email']; // Optional permissions
            $loginUrl = $helper->getLoginUrl('http://localhost', $permissions);

            $racersData = getRacersInfo();
            $user = selectUserDetails($url);
            $this->renderView('../views/races.tpl.html', [
            'racersData' => $racersData,
            'user' => $user,
            'login_url' => $loginUrl,
            ]);
    }


    public function getPlusOnePoint($name)
    {

        $this->renderView('../views/success.tpl.html', ['name' => $name]);
    }

    public function failedToGetMorePoint($name)
    {

        $this->renderView('../views/fail.tpl.html', ['name' => $name]);
    }

    public function vote($hash)
    {
        $racerResult = $this->showUserDetails($hash);
        $_SESSION['name'] = $racerResult['name'];

        if (!empty($racerResult)) {
            $check = checkIp($_SERVER['REMOTE_ADDR'], $racerResult['id']);
            if (empty($check)) {
                updateCount($racerResult['id']);
                addIpOfUser($_SERVER['REMOTE_ADDR'], $racerResult['id']);
                header("Location: http://localhost/success");
            } else {
                header("Location: http://localhost/failed");
            }
        }
    }

    public function displayRacers($fb)
    {
        try {
          // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphUser();
        if ($raceUser = $this->showRacer($user->getEmail())) {
            $_SESSION['user_id'] = (int) $raceUser['id'];
        } else {
            $url = hash('sha256', $user->getEmail());
            $id = addRacer($user->getName(), $url, 0, $user->getEmail());
            $_SESSION['user_id'] = $id;
            exit;
        }
    }

    public function buildToken($fb) {
        if (!isset($_SESSION['fb_access_token']) && !empty($_GET['code'])) {
            try {
                $helper = $fb->getRedirectLoginHelper();
                $accessToken = $helper->getAccessToken();
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
              // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
              // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }


        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }


        // Logged in
        echo '<h3>Access Token</h3>';
        var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId('606990849742582'); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
            $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
        }

        echo '<h3>Long-lived</h3>';
        var_dump($accessToken->getValue());
        }

        $_SESSION['fb_access_token'] = (string) $accessToken;

        header("Location: http://localhost");
    }
}

    /**
    * Function that renders templates and is responsible to collect some data that will be passed on the templates
    */
    private function renderView($template, $vars = 0)
    {
        require_once($template);
    }


    /**
    * Function that get user's details from the hash of the url
    */
    private function showUserDetails($url)
    {
        if (!empty($url) && $url != null) {
            $user = selectUserDetails($url);

            return $user;
        }
    }

    private function showRacer($email)
    {
        if (!empty($email) && $email != null) {
            $racer = selectRacer($email);

            return $racer;
        }
    }
}
