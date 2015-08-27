<?php
namespace bullhorn;

/**
 *
 */
class Data {

private static function getDataDir() {
	return realpath(__DIR__ . "/../data/");
}

public static function get($name) {
	$dataDir = self::getDataDir();
	$filePath = "$dataDir/$name";

	if(!file_exists($filePath)) {
		return null;
	}

	return trim(file_get_contents($filePath));
}

public static function set($name, $value) {
	$dataDir = self::getDataDir();
	$filePath = "$dataDir/$name";

	file_put_contents($filePath, trim($value));
}

}#
