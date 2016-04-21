<?php
$inputVersion = (isset($_GET['version'])) ? $_GET['version'] : '0.0.0';
$includeUnstable = (isset($_GET['unstable'])) ? (intval($_GET['unstable']) === 1) : false;
$scripts = glob('./releases/*.user.js', GLOB_MARK);
$latest = '0.0.0';
$rawVersion = '';

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
	'newVersion' => false,
	'url' => ''
);

if (version_compare($latest, $inputVersion, '>')) {
	$updateInformation['newVersion'] = true;
	$updateInformation['url'] = 'http://projects.0xleon.com/userscripts/bcplus/releases/BisaChat%20Plus%20' . rawurlencode($latest) . '.user.js';
}


@header('Content-type: application/json');
echo json_encode($updateInformation, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
