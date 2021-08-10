<?php
header("Access-Control-Allow-Origin: *");
header ( "Access-Control-Allow-Headers: *") ;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	exit();
}

require_once __DIR__.'/../php/ToolforgeCommon.php';

$inputData = json_decode(file_get_contents("php://input"),true);

$article = filter_var($inputData['izveletais']);
$comment = filter_var($inputData['comment']);

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

date_default_timezone_set('Europe/Riga');

$conn->query("insert into plani (title, comment, time_added) values (?, ?, ?)", [$article, $comment, date('YmdHis')]);

header("Content-type: application/json");
echo json_encode(['status'=> 'ok']);
