<?php
/* header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
 */
/* 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 */
header('Content-Type: application/json');
date_default_timezone_set('Europe/Riga');
/* 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Access-Control-Allow-Origin: *'); 
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
 */
require_once __DIR__.'/../../php/oauth.php';
require_once __DIR__.'/../../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$db = $tfc->openDBtool('meta_p');

switch ( isset( $_REQUEST['act'] ) ? $_REQUEST['act'] : '' ){
	case "new_article":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            new_article_to_jury( $_REQUEST['data'] );
			break;
        }

	case "get_data":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            get_data_jury( $_REQUEST['data'] );
			break;
        }
		
	case "edit_data":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            edit_data_jury( $_REQUEST['data'] );
			break;
		}
		
	case "main":
		main_jury();
        break;
}

function main_jury() {
	global $db;
	
	$bigm = array();
	$sums = array();
	$final = array();
	
	$sql = "SELECT article, user, datest, apjoms, populars, renew, id from sakartosana__articles_2020";
	$result = $db->query($sql)->fetchAll('assoc');
	
	foreach($result as $row) {
		
		$thisres = $row;
		$thissum = array_sum(array($row['apjoms'],$row['populars'],$row['renew']));//array_sum(array($resrow[3],$resrow[4],$resrow[5],$resrow[6]));
		$thisres['sum'] = $thissum;
		
		//if array_key_exists($resrow[1],$bigm)
		$bigm[$row['user']]['articles'][] = $thisres;
		
		if (array_key_exists('sum',$bigm[$row['user']])) {
			$bigm[$row['user']]['sum'] += $thissum;
		} else {
			$bigm[$row['user']]['sum'] = $thissum;
		}
    }
	
	foreach($bigm as $username => $userdata) {
		$currUser = array('user'=>$username,'articles'=>$userdata['articles'],'points'=>$userdata['sum'],'articleCount'=>count($userdata['articles']));
		$final[] = $currUser;
    }
	
	echo json_encode( $final );
}


function edit_data_jury($data) {
	global $db, $oauth;
	date_default_timezone_set('Europe/Riga');
	$thisid = (int)$data['id'];
	
	if ( !$oauth->isAuthOK() ) {
		echo json_encode(['status'=>'error','message'=>'Tu neesi ielogojies!']);
		return;
	}
	
	$user_data = $oauth->getConsumerRights();
	$username = $user_data->query->userinfo->name;
	
	$sql1 = "SELECT user, user_saved from articles where id=".$thisid;
	$result = $db->query($sql1)->fetchAll('assoc');
	$user1=$result[0]['user'];
	$user2=$result[0]['user_saved'];
	
	$sqlput = array();
	$sqlparams = array();
	//title,author,date_add,point_new,points_size,points_images,points_wd
	
	//echo $user1;
	if ($username=='Edgars2007' || $username==$user1 || $username==$user2) {
		if (isset( $data['size'] )) {
			$sqlput[]= " apjoms=? ";
			$sqlparams[]= $data['size'];
		}
		if (isset( $data['popularity'] )) {
			$sqlput[]= " populars=? ";
			$sqlparams[]= $data['popularity'];
		}
		
		$sql1 = "UPDATE articles SET ".implode(', ', $sqlput)." where id=".$thisid;
		$db->query($sql1, $sqlparams);
		
		echo json_encode(array('status' => 'success','message'=> 'Everything is ok'));//,'query'=>$sql1
	} else {
		echo json_encode(array('status' => 'error','message'=> 'failed'));
	}
}

function get_data_jury($data) {
	global $db;
	
	$theSQL = "SELECT article, user, datest, apjoms, populars, renew, id from sakartosana__articles_2020 where id=?";
	
	$article = $db->query($theSQL, [$data])->fetch('assoc');
	
	if (!$article) {
		echo json_encode(['status'=>'error','msg'=>'Radās problēma!']);
	}
	echo json_encode($article);
}


function new_article_to_jury($data) {
	global $db, $oauth;
	
	if ( !$oauth->isAuthOK() ) {
		echo json_encode(['status'=>'error','message'=>'Tu neesi ielogojies!']);
		return;
	}
	
	//$data = json_decode($data, true);
	
	//var_dump($data);
	
	$user_data = $oauth->getConsumerRights();
	$user = $user_data->query->userinfo->name;
	
	$isArticleAlready = $db->query("SELECT id FROM sakartosana__articles_2020 WHERE user=? and article=? limit 1", [$data['user'],$data['article']])->fetch("assoc");
	
	if ($isArticleAlready) {
		echo json_encode(['status'=>'error','message'=>'Šis raksts jau ir pievienots!']);
		return;
	}
	
	$curTime = date("YmdHis");
	
	$theSQL = "insert into sakartosana__articles_2020 (article, apjoms, populars, renew, user, user_saved, datest) values (?, ?, ?, ?, ?, ?, ?)";
	
	$insert = $db->query($theSQL, [$data['article'],$data['articleSize'],$data['popularity'],$data['updating'],$data['user'],$user,$curTime]);
	
	if ($insert->affectedRows() != 1) {
		echo json_encode(['status'=>'error','message'=>'Radās problēma!']);
	}
	echo json_encode(['status'=>'success','message'=>'Viss kārtībā']);
}