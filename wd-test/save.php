<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	exit();
}

require_once __DIR__.'/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

date_default_timezone_set('Europe/Riga');

$oauth = new MW_OAuth('edgars', 'wikidata');

$inputData = empty($_POST) ? json_decode(file_get_contents("php://input"),true) : $_POST;

$campaign = filter_var($inputData['campaign']);
$raw = filter_var($inputData['raw']);
$wdItem = filter_var($inputData['q']);
$property = filter_var($inputData['property']);
$site = filter_var($inputData['site']);
$article = filter_var($inputData['article']);
$prec = filter_var($inputData['prec']);
$year = filter_var($inputData['year']);
$month = filter_var($inputData['month']);
$day = filter_var($inputData['day']);

function padStringWithZeros ( $d , $num ) {
	while ( strlen($d) < $num ) $d = '0'.$d ;
	return $d ;
}

function formatDate($year, $month, $day, $prec, $raw = '') {
	if ($raw !== '') {
		if ($raw === '20gs') {
			return ["+2000-00-00T00:00:00Z",7];
		}

		if ($raw === '19gs') {
			return ["+1900-00-00T00:00:00Z",7];
		}

		if (preg_match("/^\d{4}$/",$raw)) {
			return ["+$raw-00-00T00:00:00Z", 9];
		}

		return [false, false];
	}

    $year  = padStringWithZeros($year,4);
	$month = padStringWithZeros($month,2);
	$day   = padStringWithZeros($day,2);

	return ["+$year-$month-{$day}T00:00:00Z", $prec];
}

$formatted = formatDate($year, $month, $day, $prec, $raw);

if ($formatted[0] === false) {
	die('unparsed');
}
/* 
$claimObject = [
	'type' => 'date',
	'date' => $formatted[0],//'+2020-03-15T00:00:00Z'
	'prec' => $formatted[1],
	'prop' => $property,
	'q' => $wdItem
];

$res = $oauth->setClaim($claimObject);

if ($res['status'] === 'error') {
	die('error '. $res['msg']);
}
$claimID = $res['claimID'];

$sources = [[
	'type' => 'wikibase-entityid',
	'q' => $wdItem,
	'p' => 'P143',
	'numericid' => 202472//ltwiki
]];

function createValue( $claim ){
    $value = "";
    if ( $claim['type'] == 'wikibase-entityid' ) {
        $value = '{"entity-type":"item","numeric-id":'.$claim['numericid'].'}';
    } elseif ( $claim['type'] == 'string' ) {
        $value = json_encode($claim['text']);
    } elseif ( $claim['type'] == 'time' ) {
        if ( empty($claim['calendar'])){
            $claim['calendar'] = 'http://www.wikidata.org/entity/Q1985727';
        }
        $value = '{"time":"'.$claim['date'].'","timezone": 0,"before": 0,"after": 0,"precision": '.$claim['precision'].',"calendarmodel": "'.$claim['calendar'].'"}';
    } else if ( $claim['type'] == 'location' ) {
        $value = '{"latitude":'.$claim['lat'].',"longitude": '.$claim['lon'].',"precision":0.00001,"globe": "http://www.wikidata.org/entity/Q2"}' ;
    } else if ( $claim['type'] == 'quantity' ) {
        if (array_key_exists('upper', $claim) and array_key_exists('lower', $claim)) {
            $value = '{"amount":"'.$claim['amount'].'","unit": "'.$claim['unit'].'","upperBound":"'.$claim['upper'].'","lowerBound":"'.$claim['lower'].'"}' ;
        } else {
            $value = '{"amount":"'.$claim['amount'].'","unit": "'.$claim['unit'].'"}' ;
        }
    } else if ( $claim['type'] == 'monolingualtext' ) {
        $value = '{"text":"'.json_encode($claim['text']).'","language": "'.json_encode($claim['language']).'"}' ;
    }
    return $value;
}

$snaks = '{';
    foreach ($sources as $source ) {
        $value = createValue( $source );
        $snaks .= '"'.$source['p'].'":[{"snaktype":"value","property":"'.$source['p'].'","datavalue": {"value":'.$value.', "type": "'.$source['type'].'"}}],';
    }
    $snaks = substr($snaks,0,-1).'}';

$oauth->setSource ( $claimID , $snaks );

 */
$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

date_default_timezone_set('Europe/Riga');

if ($campaign === 'ranom') {
	$conn->query("insert into datestoadd (wikidata, wiki, article, category, parsed_date) values (?, ?, ?, ?, ?)", [$wdItem, $site, $article, 'random', json_encode($formatted)]);
	header("Content-type: application/json");
	echo json_encode(['status'=>'ok1']);
	exit;
}

$conn->query("update datestoadd set done=1, parsed_date=? where wikidata=? and category=?", [json_encode($formatted), $wdItem, $campaign]);

header("Content-type: application/json");
echo json_encode(['status'=>'ok']);