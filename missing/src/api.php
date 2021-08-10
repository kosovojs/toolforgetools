<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

date_default_timezone_set('Europe/Riga');

require_once __DIR__ . '/../../php/oauth.php';
require_once __DIR__ . '/../../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('missing_p');

$WIKI = 'lvwiki';

switch (isset($_REQUEST['act']) ? $_REQUEST['act'] : '') {
	case "get":
		getData();
		break;

	case "saveLV":
		if (isset($_REQUEST['data']) && isset($_REQUEST['data'])) {
			saveLV($_REQUEST['data']);
			break;
		}
	case 'authorize': //izņemt ārā no koda!
		$oauth->doAuthorizationRedirect();
		exit(0);
		return;
	case 'userinfo':
		echo json_encode($oauth->getConsumerRights());
		break;
	case 'logout':
		echo json_encode($oauth->logout());
		break;
	default:
		echo json_encode(['status' => 'error', 'message' => 'unknown action']);
}

function getData()
{
	global $conn, $WIKI;

	$howMany = isset($_REQUEST['full']) ? ($_REQUEST['full'] == 1 ? "" : "limit 250") : "limit 250";

	$result = $conn->query("SELECT orig, lang, iws, wd, descr from articles where archived is null and wiki=? $howMany", [$WIKI])->fetchAll('assoc');

	$lv_res = $conn->query("SELECT GROUP_CONCAT(label SEPARATOR '|') as lv, article from lv where wiki=? group by article", [$WIKI])->fetchAll('assoc');

	$latvians = [];

	foreach ($lv_res as $latvian) {
		$latvians[$latvian['article']][] = $latvian['lv'];
	}

	$getDate = $conn->query("select value from meta where data='upd' and wiki=?", [$WIKI])->fetch('assoc')['value'];

	$res = [];

	foreach ($result as $row) {
		$wditem = $row['wd'];
		$lvtitle = (array_key_exists($wditem, $latvians)) ? implode("|", $latvians[$wditem]) : "";
		$row['wd'] = "Q$wditem";
		$row['lv'] = $lvtitle;
		$res[] = $row;
	}

	echo json_encode(['time' => $getDate, 'articles' => $res]);
}

function saveLV($data)
{
	global $conn, $oauth, $WIKI;

	if (!$oauth->isAuthOK()) return;

	$titles = isset($data['lv']) ? explode("|", $data['lv']) : null;
	$wdItem = isset($data['wd']) ? $data['wd'] : null;

	if ($titles == null || $wdItem == null) {
		echo json_encode(['status' => 'error', 'message' => 'Netika padots nosaukums vai Vikidatu Q numurs']);
		return;
	}

	if (substr($wdItem, 0, 1) === "Q") {
		$wdItem = substr($wdItem, 1);
	}

	$user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;
	$cur_time = date("YmdHis");


	foreach ($titles as $title) {

		$insert = $conn->query("INSERT INTO lv (label, article, user_add, time_add, wiki) values (?, ?, ?, ?, ?)", [$title, $wdItem, $username, $cur_time, $WIKI]);
		//$result = mysqli_query($conn,"INSERT INTO lv (lv, article) values ('$title', $wdItem)") or die(mysqli_error($conn));

		if ($insert->affectedRows() < 1) {
			echo json_encode(['status' => 'error', 'message' => 'error']);
			return;
		}
	}
	echo json_encode(['status' => 'ok', 'message' => 'ok']);
}
