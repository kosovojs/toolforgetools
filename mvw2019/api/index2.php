<?php
header('Content-Type: application/json');
/* header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS"); */

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../../php/oauth.php';
require_once __DIR__.'/../../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');


function getAllRequestParameters()
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return $_GET;
    } else {
        return json_decode(file_get_contents('php://input'), true);
    }
}

$params = getAllRequestParameters();

if (isset($params[ "users" ]) && !empty($params[ "users" ])) {
    $data = $params[ "users" ];
}

if (isset($params[ "vote" ]) && !empty($params[ "vote" ])) {
    $tips = $params[ "vote" ];
}

save_to_db($tips, $data);

function save_to_db($tips, $data)
{
    global $conn, $oauth;
    
    if ( !$oauth->isAuthOK() ) {
        echo json_encode(['status'=>'error','msg'=>'Tu neesi ielogojies!']);
        return;
	}
	
    if (($tips === 'first' && sizeof($data)>15) || ($tips === 'second' && sizeof($data)>3) || ($tips === 'newbie' && sizeof($data)>3)) {
        echo json_encode(['status'=>'error','msg'=>'Norādīts pārāk daudz dalībnieku!']);
        return;
    }
    
    $user_data = $oauth->getConsumerRights();
    $user = $user_data->query->userinfo->name;
    
    $isUserAlready = $conn->query("SELECT id FROM mvw_2019_pirma WHERE voter=? and phase=? limit 1", [$user,$tips])->fetch("assoc");
    
    if ($isUserAlready) {
        echo json_encode(['status'=>'error','msg'=>'Tu jau esi nobalsojis!']);
        return;
    }
    
    $curTime = date("YmdHis");
    
    $theSQL = "insert into mvw_2019_pirma (username, voter, vote_time, phase) values (?, ?, ?, ?)";
    
    $paramsToAdd = [];
    
    foreach ($data as $userAdd) {
        /* if (!is_numeric($userAdd)) {
            echo json_encode(['status'=>'error','msg'=>'Kāda no vērtībām nav cipars!']);
            return;
        } */
        
        $paramsToAdd[] = [$userAdd,$user,$curTime,$tips];
    }
    
    try {
        $conn->atomicQuery($theSQL, $paramsToAdd);
    } catch (mysqli_sql_exception $e) {
        echo json_encode(['status'=>'error','msg'=>'Radās kļūda, saglabājot datus. Mēģini vēlreiz!']);
        return;
    }
    
    echo json_encode(['status'=>'success','msg'=>'Paldies, balsojums pieņemts!']);
}
