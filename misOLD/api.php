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
			//echo 'fgfgdfg';
			all_infoboxes();
			break;
			
		case "get_data":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_infobox_data($_REQUEST['val']);
			}
			break;
			
		case "get_medicine1":
			//echo 'fgfgdfg';
			all_medicine();
			break;
			
		case "get_medicine":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_medicine_data($_REQUEST['val']);
			}
			break;
		case "get_infoboxes1":
			//echo 'fgfgdfg';
			all_infoboxes1();
			break;
			
		case "get_data1":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_infobox_data1($_REQUEST['val']);
			}
			break;
			
		case "get_data2":
			if ( isset ( $_REQUEST['val'] ) ) {
				get_infobox_data2($_REQUEST['val']);
			}
			break;
	}
}
/*

if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
	$action = $_GET['act'];
	echo $action;
	if (array_key_exists('val', $_GET)) {
		$value = $_GET['val'];
	} else {
		$value = '';
	}
	
	if ($action=='get_infoboxes') {
		//$allinfs = file_get_contents('allinfoboxes.json');
		//echo json_encode($allinfs);
		all_infoboxes();
	}
	
	if ($action=='get_data') {
		$allinfs = file_get_contents(getcwd() .'/tools/'.$value.'.json');
		echo json_encode($allinfs);
	}
	
} else {
	echo 'fuck';
}
*/
//$conn = connect_db("s53143__mis_lists_p");

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
	$sql = 'SELECT name from entries where group_name="eninfobox"';
	$result = do_sql($conn, $sql);
	$res2 = array();
	
	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function all_medicine() {
	global $conn;
	$sql = 'SELECT name from entries where group_name="medicine"';
	$result = do_sql($conn, $sql);
	$res2 = array();
	
	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function get_medicine_data($name) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from entries
	where name="'.$name.'" and group_name="medicine"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

function get_infobox_data($name) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from entries
	where name="'.$name.'" and group_name="eninfobox"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

function all_infoboxes1() {
	global $conn;
	$sql = 'SELECT name from entries where group_name="other"';
	$result = do_sql($conn, $sql);
	$res2 = array();
	
	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function get_infobox_data1($name) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from entries
	where name="'.$name.'" and group_name="other"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

function get_infobox_data2($name) {
	global $conn;
	$sql = 'SELECT jsondata, last_upd
	from entries
	where name="'.$name.'" and group_name="other1"';
	$result = do_sql($conn, $sql);
	//var_dump($result);
	
	echo json_encode( array('data'=>json_decode($result[0][0], true),'update'=>$result[0][1]) );
}

//all_infoboxes();