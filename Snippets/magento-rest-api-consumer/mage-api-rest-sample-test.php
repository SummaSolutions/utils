<?php

ini_set('xdebug.var_display_max_depth', 5);
ini_set('xdebug.var_display_max_children', 256);
ini_set('xdebug.var_display_max_data', 1024);

/**
 * Example of products list retrieve using Customer account via Magento REST API. OAuth authorization is used
 */
$callbackUrl = "http://shareascarf.localhost.com/mage-api-rest-sample-test.php";
$temporaryCredentialsRequestUrl = "http://shareascarf.summasolutions.net/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
$adminAuthorizationUrl = '[HOST_URL]/admin/oauth_authorize'; //change "HOST_URL" to the magento URL
$accessTokenRequestUrl = '[HOST_URL]/oauth/token';           //change "HOST_URL" to the magento URL
$apiUrl = '[HOST_URL]/api/rest';                             //change "HOST_URL" to the magento URL
$consumerKey = 'ec7ed8a29616cedb1c24f63c08e7a12a';           //This is the consumer key generated when registering a new consumer in the admin at system->web services->Rest Oauth consumers
$consumerSecret = 'a32dd43a3a8e563b92e659e3e2697020';        //This is the consumer secret generated when registering a new consumer in the admin at system->web services->Rest Oauth consumers

session_start();

if (!isset($_SESSION['state'])) {
    $_SESSION['state'] = 0;
}


if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
    $_SESSION['state'] = 0;
}
try {
    $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
    $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
    $oauthClient->enableDebug();

    if (!isset($_GET['oauth_token']) && !$_SESSION['state']) {
        $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
        $_SESSION['secret'] = $requestToken['oauth_token_secret'];
        $_SESSION['state'] = 1;
        header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
        exit;
    } else if ($_SESSION['state'] == 1) {
        $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
        $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
        $_SESSION['state'] = 2;
        $_SESSION['token'] = $accessToken['oauth_token'];
        $_SESSION['secret'] = $accessToken['oauth_token_secret'];
        header('Location: ' . $callbackUrl);
        exit;
    } else {
        $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);
	$resourceUrl = "$apiUrl/orders";                    //this is where you choose which resource you want to consume, in this case is the orders resource.
	$oauthClient->fetch($resourceUrl, array(), 'GET', array("Content-Type" => "application/json","Accept" => "*/*"));    //the Accept parameter could be changed to "application/xml" to receive the info in xml format instead of json
        $productsList = json_decode($oauthClient->getLastResponse());
        var_dump($productsList);
    }
} catch (OAuthException $e) {
    print_r($e);
}

