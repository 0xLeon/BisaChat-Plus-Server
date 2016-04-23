<?php
@header('Vary: Origin');

$allowOrigin = 'http://chat.bisaboard.de';

if (isset($_SERVER['HTTP_ORIGIN'])) {
	if (strtolower(substr($_SERVER['HTTP_ORIGIN'], 0, 5)) === 'https') {
		$allowOrigin = 'https://chat.bisaboard.de';
	}
}
else {
	@header($_SERVER['SERVER_PROTOCOL'] . '400 Bad Request');
	exit(0);
}


if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
	@header('Access-Control-Allow-Origin: ' . $allowOrigin);
	@header('Access-Control-Allow-Methods: GET, OPTIONS');
	@header('Access-Control-Allow-Headers: user-agent');
	@header('Access-Control-Max-Age: 86400');
	exit(0);
}

if (strtolower($_SERVER['HTTP_ORIGIN']) !== $allowOrigin) {
	@header($_SERVER['SERVER_PROTOCOL'] . '403 Forbidden');
	exit(0);
}

$inputVersion = (isset($_GET['version'])) ? $_GET['version'] : '0.0.0';
$includeUnstable = (isset($_GET['unstable'])) ? ($_GET['unstable'] === 'true') : false;
$scripts = glob('./releases/*.user.js', GLOB_MARK);
$latest = '0.0.0';

foreach ($scripts as $value) {
	$value = basename($value, '.user.js');
	$value = preg_replace('/^(?:.*?)(\d+\.\d+\.\d+)(dev|a|b|rc)?(\d+)?$/', '${1}${2}${3}', $value);
	$isUnstable = (preg_match('/(dev|a|b|rc)(\d+)?/', $value) === 1);
	
	if ($isUnstable && !$includeUnstable) {
		continue;
	}
	
	if (version_compare($value, $latest, '>')) {
		$latest = $value;
	}
}

$updateInformation = array(
	'updateAvailable' => false,
	'version' => '',
	'url' => ''
);

if (version_compare($latest, $inputVersion, '>')) {
	$updateInformation['updateAvailable'] = true;
	$updateInformation['version'] = $latest;
	$updateInformation['url'] = 'http://projects.0xleon.com/userscripts/bcplus/releases/BisaChat%20Plus%20' . rawurlencode($latest) . '.user.js';
}

@header('Access-Control-Allow-Origin: ' . $allowOrigin);
@header('Content-type: application/json');
echo json_encode($updateInformation, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
