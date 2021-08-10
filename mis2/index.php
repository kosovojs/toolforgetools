<?php
require_once __DIR__.'/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$settings = [
	'from' => 'en',
	'to' => 'lv',
	'sourceType' => 'wikiproject',
	'name'=>'Latvia'
];

$app = new Missing\Database();

//$app->forTemplate('Wintersport season 2018–19', 'en');
//$app->forTemplate('Конькобежец', 'ru');
//$app->handleRUWIKISports();//executeTask();
$app->handleWAM2019();