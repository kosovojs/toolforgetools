 <?php
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

$file = '../../replica.my.cnf';
$config = parse_ini_file($file);
$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$config['user'],$config['password'],"s53143__uzwiki_p");

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

mysqli_set_charset( $conn, "utf8" );
//echo 'sdfsfsdfsf';

function npp_getdata() {
	global $conn;
	
	$comm = "SELECT title, saturs, labojuma_id FROM entries order by title asc";
	
	$row = do_sql1( $conn,$comm );
	echo json_encode($row);//var_dump($row);//echo json_encode($row);
}

function do_sql( $conn,$query ) {
	//global $conn_jury;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_NUM );
	//echo count($rows).'<br>';
	return $rows;
}

function do_sql1( $conn,$query ) {
	//global $conn_jury;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

npp_getdata();