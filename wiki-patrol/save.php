<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';

$oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	exit();
}

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

if ($action === 'login') {
	$oauth->doAuthorizationRedirect();
	exit();
}

if ($action === 'rc') {
	get_rc();
	exit();
}

if ($action === 'patrol_edits') {
	patrol_edits();
	exit();
}

if ($action === 'save_last_good') {
	save_last_good();
	exit();
}

function save_last_good() {
	global $oauth;

	if (!$oauth->isAuthOK()) {
		echo json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
		return;
	}

	$title = getRequest('article');
	$rev = getRequest('rev');
	$editsForPatrol = getRequest('editsForPatrol');

	//var_dump($rev);
	//exit();

	$revisionIDToSave = $rev['revid'];

	$URL = "https://lv.wikipedia.org/w/api.php?action=query&format=json&prop=revisions&revids=".$revisionIDToSave."&utf8=1&formatversion=2&rvprop=content&rvslots=*";

	//echo $URL;


	$pg = json_decode ( file_get_contents ( $URL ),true ) ;
	//var_dump($pg);
	//exit();

	/* $ch = null;
	$pg = $oauth->doApiQuery( [
			"action" => "query",
			"format"=> "json",
			"prop"=> "revisions",
			"revids"=> $revisionIDToSave,
			"utf8"=> 1,
			"formatversion"=> "2",
			"rvprop"=> "content",
			"rvslots"=> "*"
		], $ch ); */

	$pageTextForsave = $pg['query']['pages'][0]['revisions'][0]['slots']['main']['content'];

	//$pageTextForsave = getPageText($revisionIDToSave);

	$summary = "saglabāta lapas versija " . $rev['revid'] . ", kuru " . $rev['timestamp'] . " saglabāja " . $rev['user'];

	$oauth->setPageText($title, $pageTextForsave, $summary);

	/*
	{"status":"ok","msg":[{"revid":"2544792","parentid":"2374480","minor":"","user":"EdgarsBot","timestamp":"2016-07-12T11:53:02Z","comment":"\/* Izveido\u0161ana *\/Typo labo\u0161ana. Skat\u012bt ar\u012b [[Vikiprojekts:Vikip\u0113dijas uzlabo\u0161ana\/Raksti\/Typo\/Labo\u0161ana|projekta lapu]]"},[{"type":"edit","ns":0,"pageid":248289,"revid":3198075,"old_revid":2544792,"rcid":9215011,"user":"Sommerwind18","bot":false,"new":false,"minor":false,"oldlen":9865,"newlen":9866,"timestamp":"2020-04-04T18:37:24Z","comment":"\/* ievads *\/","redirect":false,"tags":[],"oresscores":{"damaging":{"true":0.142,"false":0.858},"goodfaith":{"true":0.999,"false":0.0010000000000000009}}}]]}
	*/


	$revisionsForPatrol = [];

	foreach($editsForPatrol as $rev) {
		$revisionsForPatrol[] = $rev['revid'];
	}





	$patrolTokenObject = $oauth->doApiQuery( [
		'format' => 'json',
		'action' => 'query' ,
		'meta' => 'tokens',
		'type' => 'patrol'
	], $ch );

	if ( !isset( $patrolTokenObject->query->tokens->patroltoken ) ) {
		echo json_encode(['status'=>'error', 'msg'=> 'netika iegūta token vērtība']);
		return false ;
	}

	$patrolToken = $patrolTokenObject->query->tokens->patroltoken;

	$wasError = false;

	foreach($revisionsForPatrol as $edit) {
		$ch = null;


		$resObj = $oauth->doApiQuery([
			'format' => 'json',
			'action' => 'patrol' ,
			'revid' => $edit,
			'token' => $patrolToken
		], $ch);
		//$results[] = $oauth->error;

		if (!$resObj->patrol->rcid) {
			$wasError = true;
		}
	}
	/* if ($wasError) {
		echo json_encode(['status'=>'error', 'msg'=> 'neveiksmīga pārbaudes status saglabāšana']);
	} else {
		echo json_encode(['status'=>'ok']);
	} */

	echo json_encode(['status'=>'ok']);//, 'err'=> $oauth->error, 'txt'=>$pageTextForsave

}

function getPageText($revision) {
	global $oauth;

	return $pageText;
}

function get_rc() {
	global $oauth;

	//echo json_encode($oauth->getConsumerRights());
	//exit();

	/* $ch = null;
	$res = $oauth->doApiQuery( [
		'format' => 'json',
		'action' => 'query',
		'meta' => 'userinfo',
		'uiprop' => 'blockinfo|groups|rights'
	], $ch ); */

	//echo json_encode($res);

	//var_dump($res );

	if (!$oauth->isAuthOK()) {
		echo json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
		return;
	}

	$ch = null;
	$res = $oauth->doApiQuery( [
		//'origin'=> '*',
		'action'=> 'query',
		'format'=> 'json',
		'list'=> 'recentchanges',
		'utf8'=> 1,
		'formatversion'=> '2',
		'rcnamespace'=> '0',
		'rcprop'=>
			'title|timestamp|ids|comment|flags|loginfo|oresscores|redirect|sizes|tags|user',//patrolled|
			'rcshow'=> 'unpatrolled',#'!bot|!autopatrolled',
		'rclimit'=> '100',
		'rctype'=> 'edit|new|log'
	], $ch );

	echo json_encode($res);
	//var_dump($res );

}

function patrol_edits() {
	global $oauth;

	if (!$oauth->isAuthOK()) {
		echo json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
		return;
	}

	$edits = getRequest('edits');

	$results = [];
	$ch = null;

	$patrolTokenObject = $oauth->doApiQuery( [
		'format' => 'json',
		'action' => 'query' ,
		'meta' => 'tokens',
		'type' => 'patrol'
	], $ch );

	if ( !isset( $patrolTokenObject->query->tokens->patroltoken ) ) {
		echo json_encode(['status'=>'error', 'msg'=> 'netika iegūta token vērtība']);
		return false ;
	}

	$patrolToken = $patrolTokenObject->query->tokens->patroltoken;

	$wasError = false;

	foreach($edits as $edit) {
		$ch = null;/*
		$actionObj = new stdClass();
		$actionObj->action = 'patrol';
		$actionObj->format = 'json';
		$actionObj->revid = $edit;
		$actionObj->token = $patrolToken; */

		$resObj = $oauth->doApiQuery([
			'format' => 'json',
			'action' => 'patrol' ,
			'revid' => $edit,
			'token' => $patrolToken
		], $ch);
		//$results[] = $oauth->error;

		if (!$resObj->patrol->rcid) {
			$wasError = true;
		}
	}
	if ($wasError) {
		echo json_encode(['status'=>'error', 'msg'=> 'neveiksmīga pārbaudes status saglabāšana']);
	} else {
		echo json_encode(['status'=>'ok']);
	}
}

/*
[{"patrol":{"rcid":9237494,"ns":0,"title":"Fran\u0161\u012bze"}}]
*/
