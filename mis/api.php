<?php
header('Content-Type: application/json; charset=utf-8');
//header( 'Content-Type: application/json; charset=utf-8;' );
$file = '../../replica.my.cnf';
$config = parse_ini_file($file);
$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$config['user'],$config['password'],"s53143__mis_lists_p");
mysqli_set_charset( $conn, "utf8" );

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

$action	 = ($_REQUEST[ "act" ] ? $_REQUEST[ "act" ] : "");
$list	 = (isset($_REQUEST[ "val" ]) ? $_REQUEST[ "val" ] : "");

if ( isset( $action ) && !empty( $action ) ) {
	switch ( $action ) {
		//en infoboxes
		case "get_infoboxes":
			getOptions('eninfobox');
			break;

		case "get_data":
			getData('eninfobox',$list);
			break;

		//groups
		case "get_infoboxes1":
			getOptions('other');
			break;

		case "get_data1":
			getData('other',$list);
			break;

		//ru sports
		case "get_infoboxes2":
			getOptions('rusports');
			break;

		case "get_data2":
			getData('rusports',$list);
			break;
		//fr infobox
		case "get_fr_infoboxes":
			getOptions('frinfobox');
			break;

		case "get_data_fr":
			getData('frinfobox',$list);
			break;
		//vos2020

		case "vos2020":
			getData('vos2020',$list);
			break;
		case "vos2020_countries":
			linksFromCountries();
			break;
		//women, filmas etc. - atsevišķie saraksti
		case "get_data3":
			getData('other1',$list);
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

function getOptions($group) {
	global $conn;
	$sql = "SELECT name from entries where group_name='$group'";
	$result = do_sql($conn, $sql);
	$res2 = array();

	foreach($result as $resrow) {
		$res2[] = $resrow[0];
	}
	sort($res2, SORT_NATURAL | SORT_FLAG_CASE);
	echo json_encode( $res2 );
}

function getData($group,$list) {
	global $conn;
	$sql = "SELECT jsondata, last_upd,language
	from entries
	where name='$list' and group_name='$group'";
	$result = do_sql($conn, $sql);
	//var_dump($result);

	$lang = (isset($result[0][2]) ? $result[0][2] : "en");
	$data = (isset($result[0][0]) ? $result[0][0] : "");
	$upd = (isset($result[0][1]) ? $result[0][1] : "");

	echo json_encode( array('data'=>json_decode($data, true),'update'=>$upd, 'lang'=>$lang) );
}

function linksFromCountries() {
	global $conn;
	$sql = "SELECT page_title, iws
	FROM links_from_countries
	WHERE wanted IS NULL AND iws IS NOT NULL
	ORDER BY iws DESC
	LIMIT 500";
	$result = do_sql($conn, $sql);
	//var_dump($result);

	$lang = "en";
	$data = $result;
	$upd = "";

	echo json_encode( array('data'=>$data,'update'=>$upd, 'lang'=>$lang) );
}
