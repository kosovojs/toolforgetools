<?php
//error_reporting( E_ALL );
//ini_set( 'display_errors', '1' );
header('Content-Type: application/json; charset=utf-8');
//header( 'Content-Type: application/json; charset=utf-8;' );
$myRoot = $_SERVER['DOCUMENT_ROOT'].'../';

//require_once("/mnt/nfs/labstore-secondary-tools-project/edgars/connect.inc.php");
require_once $myRoot."connect.inc.php";
$conn = connect_db("s53143__estlat_p");

$action	 = $_REQUEST[ "act" ];

if ( isset( $action ) && !empty( $action ) ) {
	switch ( $action ) {
		case "get_infoboxes":
			all_infoboxes();
			break;
			
		case "get_latvija":
			get_latvija();
			break;
			
		case "get_data":
			if ( isset ( $_REQUEST['val'] ) && isset ( $_REQUEST['wiki'] ) ) {
				get_infobox_data($_REQUEST['val'],$_REQUEST['wiki']);
			}
			break;
			
	}
}

function do_sql( $conn,$query ) {
	//echo $query;
	$result = mysqli_query( $conn, $query );
	//var_dump($result);
	$rows   = mysqli_fetch_all( $result, MYSQLI_NUM );
	//echo count($rows).'<br>';
	return $rows;
}

function do_sql1( $conn,$query ) {
	//echo $query;
	$result = mysqli_query( $conn, $query );
	//var_dump($result);
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

function get_latvija() {
	global $conn;
	$sql = 'SELECT raksts, baiti, garums, mean1, mean2, sum1, sum2 from popular where garums>0 and sum1>25';
	$result = do_sql1($conn, $sql);
	//var_dump($result);
	
	echo json_encode( $result);
}

function all_infoboxes() {
	$res2 = array('en','fr','ru','wd');//,'fr','ru'
	echo json_encode( $res2 );
}

function get_infobox_data($name,$wiki) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from lists
	where name="Latvia" and wiki="'.$wiki.'" and source="'.$name.'"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}