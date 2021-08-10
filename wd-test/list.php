<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header ( "Access-Control-Allow-Headers: *") ;

require_once __DIR__.'/../php/ToolforgeCommon.php';

$inputData = empty($_GET) ? json_decode(file_get_contents("php://input"),true) : $_GET;

$campaign = filter_var($inputData['campaign']);

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

date_default_timezone_set('Europe/Riga');

$data = $conn->query("select article, wikidata, wiki from datestoadd where category=? and done is null  ORDER BY REGEXP_SUBSTR(wikidata,'[0-9]+') desc", [$campaign])->fetchAll('assoc');

header("Content-type: application/json");
echo json_encode($data);