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

		case "main_reminders":
			reminders__main_reminders();
			break;

		case "archive_reminder":
			if ( isset ( $_REQUEST['data'] ) ) {
				arhivet($_REQUEST['data']);
			}
			break;

		case "new_article_to_reminders":
			if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
				new_article_to_reminders( $_REQUEST['data'] );
			}
			break;

	}
}

function test_input( $data ) {
	$data = trim( $data );
	$data = stripslashes( $data );
	$data = htmlspecialchars( $data );
	return $data;
 }
 
function reminders__main_reminders() {
	global $conn;
	
	$result = $conn->query("SELECT id, page, add_date, notif_time, completed, ping_user, comment from reminders__main where archive is NULL")->fetchAll('assoc');
	
	echo json_encode( $result );
}


function arhivet($id) {
	global $conn, $oauth;
	
	if ( !$oauth->isAuthOK() ) return;
	
	$id = (int)$id;
	
	$user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;
	
	$sql1 = "SELECT add_user, ping_user from reminders__main where id=".$id;
	
	$result = $conn->query("SELECT add_user, ping_user from reminders__main where id=?",[$id])->fetch('assoc');
	$user1=$result['add_user'];
	$user2=$result['ping_user'];
	
	//echo $user1;
	if ($username=='Edgars2007' || $username==$user1 || $username==$user2) {
		$stmt = $conn->query("UPDATE reminders__main SET archive=1 WHERE id=?",[$id]);
		
		if($stmt->affectedRows() < 1) {
			echo json_encode(array('status' => 'fail','message'=> 'failed'));
		} else {
			echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
		}
	} else {
		echo json_encode(array('status' => 'fail','message'=> 'failed'));
	}
	
}

function new_article_to_reminders($data) {
	global $conn, $oauth;
	
	if ( !$oauth->isAuthOK() ) return;
	
	$article = isset($data['article']) ? test_input($data['article']) : "";
	$author = isset($data['author']) ? test_input($data['author']) : "";
	$wiki = isset($data['wiki']) ? test_input($data['wiki']) : "";
	$dateOLD = isset($data['date']) ? test_input($data['date']) : "";
	$comment = isset($data['comment']) ? test_input($data['comment']) : "";
	$output = isset($data['output']) ? test_input($data['output']) : "";
	if ($dateOLD!="") {
		//$date = date('d/m/Y H:i',strtotime($dateOLD));
		$date = DateTime::createFromFormat('d/m/Y H:i', $dateOLD);
	}
	
	$user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;
	
	$cur_time = date("YmdHis");
	$notif_time = $date->format('Ymd');//date("YmdHis",$date);
	
	$stmt = $conn->query("INSERT INTO reminders__main (page,add_date,comment,wiki,page_notif,add_user,ping_user,notif_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",[$article,$cur_time,$comment,$wiki,$output,$username,$author,$notif_time]);
	
	if($stmt->affectedRows() < 1) {
		echo json_encode(array('status' => 'error','message'=> 'failed'));
	} else {
		echo json_encode(array('status' => 'success','message'=> 'Everything is ok'));
	}
}