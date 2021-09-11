<?php
/*
TRUNCATE suggestion_server;
INSERT INTO suggestion_server (suggestion_title, suggestion_target, suggestion_added, views, statuss)
SELECT bfs_orig_title, bfs_new_title, NOW(), 0, 0
FROM bf_suggestions
WHERE bfs_orig_title IN (SELECT bfs_orig_title
FROM bf_suggestions
WHERE LENGTH(bfs_orig_title)>3 AND SUBSTRING(bfs_orig_title,1,1)<>'.' AND not bfs_orig_title REGEXP '^[0-9]+$'

AND NOT (SUBSTRING(bfs_orig_title, -5) = '_gadi' AND SUBSTRING(bfs_new_title, -5) = '_gads' )
GROUP BY bfs_orig_title
HAVING COUNT(*) < 7)
*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';
/* require_once __DIR__.'/lib/oauth.php';
require_once __DIR__.'/lib/ToolforgeCommon.php'; */

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

$tfc->tool_user_name = 'edgars';

/* if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	exit();
} */

$conn = $tfc->openDBtool('meta_p');

function getRequest($key, $default = "")
{
	global $reqParams;

	//if ( isset ( $this->prefilled_requests[$key] ) ) return $this->prefilled_requests[$key] ;
	if (isset($reqParams[$key])) {
		return str_replace("\'", "'", $reqParams[$key]) ;
	}
	return $default ;
}

$reqParams = empty($_REQUEST) ? json_decode(file_get_contents('php://input'), true) : $_REQUEST;

$action = getRequest('action');


if (!empty($action)) {
    switch ($action) {
		case "next_suggestion":
			echo getNextSuggestion();
			break;

		case "save_action":
			echo saveArticle(getRequest('title'), getRequest('message'));
			break;

		case "redirect":
			echo createRedirect(getRequest('from'), getRequest('to'));
			break;
		case "status":
			echo json_encode($oauth->getConsumerRights());
			break;
		case "titles_to_ignore":
			echo titleToIgnore();
			break;
    }
}

function getNextSuggestion() {
    global $conn;


	/*


	and suggestion_title like '%gad%'

	and suggestion_target REGEXP '^[0-9][0-9][0-9][0-9]$'


	(LOWER(suggestion_title)!=LOWER(suggestion_target) or suggestion_title like '\"%')

	(suggestion_target like '%FK%' or suggestion_title like '%FK%')
	(suggestion_target like '%FC%' or suggestion_title like '%FC%')

	$title = $conn->query("SELECT suggestion_title from suggestion_server
	WHERE statuss=0 AND (views<3 or views is null) and

	(LOWER(suggestion_title)=LOWER(suggestion_target) or suggestion_title like '\"%')
	ORDER BY rand() limit 1")->fetch('assoc')['suggestion_title']; */

	// and easy_level>12
	$title = $conn->query("SELECT distinct suggestion_title from suggestion_server
	WHERE  (statuss=0 or statuss is null)   AND (views<3 or views is null) and resolved_time is null
	and import_id=7

	ORDER BY rand() limit 1")->fetch('assoc')['suggestion_title'];
	//

	$targets = $conn->query("SELECT distinct suggestion_target from suggestion_server
	WHERE suggestion_title=?",[$title])->fetchAll('col');

	/* if (sizeof($targets) > 3) {
		return json_encode(['suggestion'=>null, 'targets'=> []]);
	} */

	$conn->query("update suggestion_server set views= views+1 where suggestion_title=?", [$title]);

	return json_encode(['suggestion'=>$title, 'targets'=> $targets]);
}

function createRedirect($from, $to) {
    global $conn, $oauth;

    if (!$oauth->isAuthOK()) {
        return json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
	}

    $user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;

	$to = str_replace('_',' ',$to);

	$newContent = "#REDIRECT [[$to]]";

	$res = $oauth->setPageText($from, $newContent, 'izveidota pāradresācija');

	if ($res) {
		$conn->query("update suggestion_server set statuss=?, resolved_time=?, resolved_user=? where suggestion_title=? or suggestion_title=?", [makeStatuss('redirect'), date("YmdHis"), $username, str_replace(' ','_',$from), $from]);
		return json_encode(array('status' => 'ok','msg'=> 'Everything is ok'));
	}

	//, 'saveError' => $oauth->error, 'params'=>[$from, $newContent]
	return json_encode(array('status' => 'error','msg'=> 'failed', 'saveError' => $oauth->error, 'params'=>[$from, $newContent]));
}

function makeStatuss($inputText) {
	$mapping = [
		'not redlink' => 1,
		'no links' => 2,
		'redirect' => 3,
		'done' => 4,
		'delete' => 5
	];

	if (array_key_exists($inputText, $mapping)) {
		return $mapping[$inputText];
	}

	return 999;
}

function saveArticle($title, $message) {
    global $conn, $oauth;

    if (!$oauth->isAuthOK()) {
        return json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
	}

    $user_data = $oauth->getConsumerRights();
    $username = $user_data->query->userinfo->name;

	$cur_time = date("YmdHis");

    $stmt = $conn->query("update suggestion_server set statuss=?, resolved_time=?, resolved_user=? where suggestion_title=? or suggestion_title=?", [makeStatuss($message), $cur_time, $username, str_replace(' ','_',$title), $title]);

    if ($stmt->affectedRows() < 1) {
        echo json_encode(array('status' => 'error','msg'=> 'failed'));
    } else {
        echo json_encode(array('status' => 'ok','msg'=> 'Everything is ok'));
    }
}

function titleToIgnore() {
    global $conn;

	$oldQuery = "select distinct suggestion_title from suggestion_server where views=3 or statuss>0
	UNION ALL
	select distinct suggestion_title from suggestion_server where views=3 or statuss>0
	UNION ALL
	select distinct suggestion_title from suggestion_server_2020 where views=3 or statuss>0
	UNION ALL
	select distinct suggestion_title from suggestion_server_postgres where views=3 or statuss>0";

    $res = $conn->query("select distinct suggestion_title from suggestion_server
	UNION ALL
	select distinct suggestion_title from suggestion_server
	UNION ALL
	select distinct suggestion_title from suggestion_server_2020
	UNION ALL
	select distinct suggestion_title from suggestion_server_postgres")->fetchAll('col');

    echo json_encode($res);
}
