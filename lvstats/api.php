<?php

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
header('Content-Type: application/json; charset=utf-8');
//header( 'Content-Type: application/json; charset=utf-8;' );
$file = '../../replica.my.cnf';
$config = parse_ini_file($file);
$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$config['user'],$config['password'],"s53143__lvstats_p");
mysqli_set_charset( $conn, "utf8" );

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

$action	 = $_REQUEST[ "action" ];

if ( isset( $action ) && !empty( $action ) ) {
	switch ( $action ) {
		case "graphdata":
			get_infobox_data();
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

function dateDifference($date_1 , $date_2 = "2018-11-18" , $differenceFormat = '%a' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
   
    $interval = date_diff($datetime1, $datetime2);
   
    return $interval->format($differenceFormat);
}

function get_infobox_data() {
	global $conn;
	$comm = "select left(timest,10), articles from stats";
	
	$row = do_sql( $conn,$comm );
	$result = array();
	
	foreach($row as $indrow) {
		$result[0][] = round((100000-$indrow[1])/dateDifference($indrow[0]),3);
		$result[1][] = $indrow[0];
	}
	
	echo json_encode($result);//var_dump($row);//echo json_encode($row);
}