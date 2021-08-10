<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

$newValue = filter_var($_REQUEST['value']);

$conn->query("update cee2020 set upd_timestamp = ? where year='2020'", [$newValue]);
