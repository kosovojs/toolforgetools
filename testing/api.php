<?php
//header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBwiki('lvwikipedia');
/*
$theSQL = "";

$pagesCat = $tfc->getSQL($conn,'',2);
var_dump($pagesCat);
*/