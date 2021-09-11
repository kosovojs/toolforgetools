<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';
$tfc = new ToolforgeCommon('edgars');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

$data = json_decode(file_get_contents('./results.json'), true);

foreach($data as $entry) {
	[$redlink, $bluelink, $likelyhood] = $entry;
	if ($likelyhood < 10) {
		continue;
	}
	$conn->query("insert into suggestion_server (suggestion_title, suggestion_target, suggestion_added, views, statuss, import_id, easy_level) values (?, ?, ?, ?, ?, ?, ?)", [$redlink, $bluelink, date('YmdHis'), 0, 0, 7, $likelyhood]);
}
