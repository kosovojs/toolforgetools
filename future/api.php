<?php
header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

$action = $tfc->getRequest("action");

if ( !empty( $action ) ) {
	switch ( $action ) {

		case "main":
			if ( isset ( $_REQUEST['last_id'] ) && isset ( $_REQUEST['type1'] ) && isset ( $_REQUEST['offset'] ) ) {
				npp_getdata($_REQUEST['last_id'],$_REQUEST['offset'],$_REQUEST['type1']);
			}
			break;

		case "save":
			if ( isset ( $_REQUEST['id'] ) ) {
				save_data($_REQUEST['id'],$_REQUEST['result']);
			}
			break;

	}
}

function npp_getdata($last_id,$offset,$type_selection) {
	global $conn;
	$last_id = (int)$last_id;
	$offset = (int)$offset;
	
	$comm = "SELECT id,title,before_str,match_str,after_str FROM future__entries WHERE (result is NULL) and id>";
	
	if ($type_selection=="rnd") {
		$comm .= "0 ORDER BY RAND() limit 20 offset ".$offset;
	} else if ($type_selection=="next") {
		$comm .= $last_id." limit 20 offset ".$offset;
	}
	$numofres = $conn->query("SELECT count(*) FROM future__entries WHERE result is NULL")->fetch('num');
	
	$result = $conn->query($comm)->fetchAll('assoc');
	
	echo json_encode(array('list'=>$result,'articles'=>$numofres[0]));//var_dump($row);//echo json_encode($row);
}

function save_data($id,$result) {
	global $conn, $oauth;
	
	if ( !$oauth->isAuthOK() ) return;
	
	$id = (int)$id;
	
	$user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;
	
	$cur_time = date("YmdHis");
	
	
	$stmt = $conn->query("UPDATE future__entries SET result=?, result_user=?, result_time=? WHERE id=?",[$result,$username,$cur_time,$id]);
	
	if($stmt->affectedRows() < 1) {
		echo json_encode(array('status' => 'fail','message'=> 'failed'));
	} else {
		echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	}
}