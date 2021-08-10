<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

/**
 * To the extent possible under law, the author(s) have dedicated all copyright
 * and related and neighboring rights to this software to the public domain
 * worldwide. This software is distributed without any warranty.
 *
 * See <http://creativecommons.org/publicdomain/zero/1.0/> for a copy of the
 * CC0 Public Domain Dedication.
**/

header( 'Content-Type: application/json' );
$myRoot = $_SERVER['DOCUMENT_ROOT'].'../';
//echo $myRoot;

//require_once("/mnt/nfs/labstore-secondary-tools-project/edgars/connect.inc.php");
require_once $myRoot."connect.inc.php";

$conn = connect_db("s53143__missing_p");

function do_sql( $query ) {
	global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

$howMany = isset($_REQUEST['full']) ? ($_REQUEST['full']==1 ? "" : "limit 250") : "limit 250";

$sql = "SELECT orig, lang, iws, wd, descr from articles where archived is null $howMany";
$result = do_sql($sql);

$lv = "SELECT GROUP_CONCAT(lv SEPARATOR '|') as lv, article from lv group by article";
$lv_res = do_sql($lv);
$latvians = [];

foreach($lv_res as $latvian) {
	$latvians[$latvian['article']][] = $latvian['lv'];
}

$getDate = do_sql("select value from meta where data='upd'")[0]['value'];


$res = [];

foreach($result as $row) {
	$wditem = $row['wd'];
	$lvtitle = (array_key_exists($wditem,$latvians)) ? implode("|", $latvians[$wditem]) : "";
	$row['wd'] = "Q$wditem";
	$row['lv'] = $lvtitle;
	$res[] = $row;
}

echo json_encode( ['time'=> $getDate, 'articles'=> $res] );