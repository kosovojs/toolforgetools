<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

if ( isset( $_REQUEST[ "data" ] ) && !empty( $_REQUEST[ "data" ] ) ) {
	$data = $_REQUEST[ "data" ];
}

if ( isset( $_REQUEST[ "tips" ] ) && !empty( $_REQUEST[ "tips" ] ) ) {
	$tips = $_REQUEST[ "tips" ];
}

save_to_db($tips,$data);

function save_to_db($tips,$data) {
	global $conn, $oauth;
	
	if ( !$oauth->isAuthOK() ) {
		echo json_encode(['status'=>'error','msg'=>'Tu neesi ielogojies!']);
		return;
	}
	
	$data = json_decode($data, true);
	
	//var_dump($data);
	
	if (sizeof($data)>3) {
		echo json_encode(['status'=>'error','msg'=>'Norādīts vairāk par 3 dalībniekiem!']);
		return;
	}
	
	$user_data = $oauth->getConsumerRights();
	$user = $user_data->query->userinfo->name;
	
	$isUserAlready = $conn->query("SELECT id FROM labakais WHERE balsotajs=? and tips=? limit 1", [$user,$tips])->fetch("assoc");
	
	if ($isUserAlready) {
		echo json_encode(['status'=>'error','msg'=>'Tu jau esi nobalsojis!']);
		return;
	}
	
	$curTime = date("YmdHis");
	
	$theSQL = "insert into labakais (user, balsotajs, laiks, tips) values (?, ?, ?, ?)";
	
	$paramsToAdd = [];
	
	foreach($data as $userAdd) {
		if (!is_numeric($userAdd)) {
			echo json_encode(['status'=>'error','msg'=>'Kāda no vērtībām nav cipars!']);
			return;
		}
		
		$paramsToAdd[] = [$userAdd,$user,$curTime,$tips];
	}
	
	try {
		$insert = $conn->atomicQuery($theSQL, $paramsToAdd);
	} catch (mysqli_sql_exception $e) {
		echo json_encode(['status'=>'error','msg'=>'Radās kļūda, saglabājot datus. Mēģini vēlreiz!']);
		return;
	}
	
	echo json_encode(['status'=>'success','msg'=>'Paldies, balsojums pieņemts!']);
}