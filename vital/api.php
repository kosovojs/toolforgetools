<?php

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
//header('Content-Type: application/json; charset=utf-8');
//header( 'Content-Type: application/json; charset=utf-8;' );
$file = '../../replica.my.cnf';
$config = parse_ini_file($file);
$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$config['user'],$config['password'],"s53143__mis_lists_p");
mysqli_set_charset( $conn, "utf8" );

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

$action	 = $_REQUEST[ "act" ];

if ( isset( $action ) && !empty( $action ) ) {
	switch ( $action ) {
		case "get_infoboxes":
			all_infoboxes();
			break;
			
		case "all_data":
			all_data();
			break;
			
		case "get_data":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_infobox_data($_REQUEST['val']);
			}
			break;
		case "all_views":
			all_views();
			break;
		case "get_views":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_views($_REQUEST['val']);
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

function all_infoboxes() {
	global $conn;
	$sql = 'SELECT name from vital
	where language is null';
	$result = do_sql($conn, $sql);
	$res2 = array();
	
	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function all_views() {
	global $conn;
	$sql = 'SELECT name from vital
	where language is not null';
	$result = do_sql($conn, $sql);
	$res2 = array();
	
	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function get_views($currLang) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from vital
	where language is not null and name="'.$currLang.'"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

function get_infobox_data($name) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from vital
	where language is null and
	name="'.$name.'"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

function all_data() {
	global $conn;
	$sql = 'SELECT name, level, jsondata, last_upd
	from vital
	where language is null
	order by name';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	$toret = array();
	
	foreach($result as $resRow) {
		$themaindata = json_decode($resRow[2], true);
		$toret[] = array(
					'jsondata'=>$themaindata,
					'count'=>count($themaindata),
					'update'=>$resRow[3],
					'limenis'=>$resRow[1],
					'name'=>$resRow[0]
				);
	}
					
	echo json_encode($toret);
}

//all_infoboxes();