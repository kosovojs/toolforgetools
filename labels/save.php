<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

//header('Content-Type: application/json');

date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';

$oauth = new MW_OAuth('edgars', 'wikidata');

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


$wditem = getRequest('wditem');
$label = getRequest('label');

if ($wditem == '' || $label == '') {
	echo json_encode(['status'=>'error', 'msg'=> 'no data passed']);
	exit();
}

if (!$oauth->isAuthOK()) {
	echo json_encode(['status'=>'error', 'msg'=> 'lietotājs nav autentificējies']);
}

$resFroLabelSet = $oauth->setLabel($wditem, $label, 'lv');

sleep(2);

if ($resFroLabelSet) {
	echo json_encode(['status'=>'ok']);
	exit();
}

echo json_encode(['status'=>'error', 'error' => $oauth->error]);
exit();
