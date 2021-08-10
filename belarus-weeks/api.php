<?php
header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('mis_lists_p');

$result = $conn->query("SELECT jsondata, last_upd from entries where name='BLR' and group_name='other'")->fetch('assoc');

echo json_encode(['time' => $result['last_upd'], 'articles' => json_decode($result['jsondata'], true)]);