<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header ( "Access-Control-Allow-Headers: *") ;

require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

date_default_timezone_set('Europe/Riga');

$sqlQuery = "SELECT *
FROM datestoadd
WHERE parsed_date IS NOT NULL and added_to_wd is null and wikidata!=''
GROUP BY wikidata";

$data = $conn->query($sqlQuery)->fetchAll('assoc');

$forQS = [];

$wpitems = json_decode(file_get_contents('wpeditionids.json'), true);

foreach($data as $entry) {
	['wikidata' => $wd, 'wiki' => $lang, 'parsed_date' => $parsedDate] = $entry;

	[$timestamp, $prec] = json_decode($parsedDate);

	if ($timestamp === '+0000-00-00T00:00:00Z') {
		continue;
	}

	$statement = "$wd\tP569\t$timestamp/$prec";

	$wiki = "{$lang}wiki";
	if (array_key_exists($wiki, $wpitems)) {
		$langItem = $wpitems[$wiki];
		$statement .= "\tS143\tQ$langItem";
	}

	$forQS[] = $statement;
}

echo "See <a href='https://quickstatements.toolforge.org/#/batch'>OS</a><br />";

echo "<textarea rows='50' cols='80'>
".implode("\n", $forQS)."
</textarea>";

$conn->query("update datestoadd set added_to_wd=1 where parsed_date IS NOT NULL and added_to_wd is null and wikidata!=''");