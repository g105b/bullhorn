#!/usr/bin/php
<?php
namespace bullhorn;
use Curl\Curl;

define("KEY_CLIENT_ID", "client-id");
define("KEY_CLIENT_SECRET", "client-secret");
define("KEY_USERNAME", "username");
define("KEY_PASSWORD", "password");
define("KEY_REFRESH_TOKEN", "refresh-token");
define("KEY_REST_TOKEN", "rest-token");
define("KEY_REST_URL", "rest-url");

require __DIR__ . "/../vendor/autoload.php";

echo "\n";

foreach([
KEY_CLIENT_ID,
KEY_USERNAME,
KEY_PASSWORD,] as $name) {
	$data = Data::get($name);
	if(!$data) {
		echo "Missing required data: $name\n";
		exit(1);
	}
}

$curl = new Curl();
$curl->setUserAgent("Greg's sucky API fixer 4000");
$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

if(is_null(Data::get(KEY_REFRESH_TOKEN)) ) {
	echo "Logging in...\n";
	$curl->post("https://auth.bullhornstaffing.com/oauth/authorize", [
		"response_type" => "code",
		"client_id" => Data::get(KEY_CLIENT_ID),
		"username" => Data::get(KEY_USERNAME),
		"password" => Data::get(KEY_PASSWORD),
		"action" => "Login",
	]);

	$authCode = null;
	foreach($curl->response_headers as $header) {
		if(strpos($header, "Location: ") === 0) {
			$authCode = substr($header, strpos($header, "code=") + 5);
			if(strstr($authCode, "&")) {
				$authCode = substr($authCode, 0, strpos($authCode, "&"));
				break;
			}
		}
	}

	if(is_null($authCode)) {
		echo "Error generating auth code!\n";
		exit(1);
	}

	echo "Got authorization code: $authCode\n";
	echo "Getting fake grant...\n";

	$curl->post("https://auth.bullhornstaffing.com/oauth/token"
	 . "?code=$authCode", [
		"grant_type" => "authorization_code",
		"client_id" => Data::get(KEY_CLIENT_ID),
		"client_secret" => DATA::get(KEY_CLIENT_SECRET),
	]);

	$grantObj = json_decode($curl->response);
	echo "Got access token: " . $grantObj->access_token . "\n";
	echo "Got refresh token: " . $grantObj->refresh_token . "\n";

	Data::set(KEY_REFRESH_TOKEN, $grantObj->refresh_token);
}
else {
	echo "Refresh token is set!\n";
}

echo "Refreshing...\n";

$curl->post("https://auth.bullhornstaffing.com/oauth/token", [
	"grant_type" => "refresh_token",
	"refresh_token" => Data::get(KEY_REFRESH_TOKEN),
	"client_id" => Data::get(KEY_CLIENT_ID),
	"client_secret" => Data::get(KEY_CLIENT_SECRET),
]);

$grantObj = json_decode($curl->response);
echo "Got access token: " . $grantObj->access_token . "\n";
echo "Got refresh token: " . $grantObj->refresh_token . "\n";

$accessToken = $grantObj->access_token;
Data::set(KEY_REFRESH_TOKEN, $grantObj->refresh_token);

echo "Getting the Bullhorn rest token...\n";

$curl->get("https://rest.bullhornstaffing.com/rest-services/login"
 . "?version=*&access_token=$accessToken");

$restObj = json_decode($curl->response);
echo "Got rest details.\n\n";

Data::set(KEY_REST_TOKEN, $restObj->BhRestToken);
Data::set(KEY_REST_URL, $restObj->restUrl);

echo "All done.\n\n";
