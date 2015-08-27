#!/usr/bin/php
<?php
namespace bullhorn;
use Curl\Curl;

define("KEY_REST_TOKEN", "rest-token");
define("KEY_REST_URL", "rest-url");
define("DEBUG", false);

require __DIR__ . "/../vendor/autoload.php";

if(DEBUG)echo "\n";

if(DEBUG)echo "Refreshing auth data...\n";
passthru(__DIR__ . "/auth.php");

if(!isset($argv)) {
	$argv = [];
}

$url = Data::get(KEY_REST_URL);
$method = strtolower($argv[1]);
$endpoint = $argv[2];

$query = "?";
for($i = 3, $c = count($argv); $i < $c; $i++) {
	$query .= $argv[$i] . "&";
}

$restToken = Data::get(KEY_REST_TOKEN);

if(DEBUG)echo "Performing call...\n";

$curl = new Curl();
$curl->$method("$url$endpoint$query&BhRestToken=$restToken");

if(DEBUG)echo "\n\n";
echo $curl->response . "\n";
if(DEBUG)echo "\n";
