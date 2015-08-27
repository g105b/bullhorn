#!/usr/bin/php
<?php
namespace bullhorn;
use Curl\Curl;

define("KEY_REST_TOKEN", "rest-token");
define("KEY_REST_URL", "rest-url");

require __DIR__ . "/../vendor/autoload.php";

echo "\n";

echo "Refreshing auth data...\n";
passthru(__DIR__ . "/auth.php");

if(!isset($argv)) {
	$argv = [];
}

$url = Data::get(KEY_REST_URL);
$endpoint = $argv[1];

$query = "?";
for($i = 2, $c = count($argv); $i < $c; $i++) {
	$query .= $argv[$i] . "&";
}

$restToken = Data::get(KEY_REST_TOKEN);

echo "Performing call...\n";

$curl = new Curl();
$curl->get("$url$endpoint$query&BhRestToken=$restToken");
echo "\n\n" . $curl->response . "\n\n";
