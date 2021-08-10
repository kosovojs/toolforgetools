 <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect("localhost","edgars", "edgars",'tviteris');

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

mysqli_set_charset( $conn, "utf8" );
//echo 'sdfsfsdfsf';

function test_input( $data ) {
   $data = trim( $data );
   $data = stripslashes( $data );
   $data = htmlspecialchars( $data );
   return $data;
}

if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
	//{'q':q, 'site':site, 'year':year, 'month':month, 'day':day,'prec':precision}
	$action = test_input($_POST['act']);
	if (array_key_exists('q', $_POST)) {
		$qitem = test_input($_POST['q']);
	} else {
		$qitem = '';
	}
	if (array_key_exists('raw', $_POST)) {
		$raw_text = test_input($_POST['raw']);
	} else {
		$raw_text = '';
	}
	
	if (array_key_exists('right', $_POST)) {
		$right_id = test_input($_POST['right']);
	} else {
		$right_id = '';
	}
	
	//':label,'':coords,'ref':r
	if (array_key_exists('data', $_POST)) {
		$dataOK = $_POST['data'];
	} else {
		$dataOK = '';
	}
	
	if (array_key_exists('coords', $_POST)) {
		$coords = test_input($_POST['coords']);
	} else {
		$coords = '';
	}
	
	if (array_key_exists('ref', $_POST)) {
		$ref = test_input($_POST['ref']);
	} else {
		$ref = '';
	}
	
	//echo $action.'<br>';
	if ($action=='getF')
		getdata();
	
	if ($action=='no')
		do_sql_queryNO('no',$dataOK);
	
	if ($action=='ok')
		do_sql_query('ok',$dataOK);
	
	if ($action=='edit')
		do_sql_queryEdit($dataOK);
	
	//put_db($q,$article,$site,$year,$month,$day,$prec,$nodob,$raw,$campaign);
	
} else {
	echo 'fuck';
}

function do_sql( $query ) {
	global $conn;
	$result = mysqli_query( $conn, $query );
	//var_dump($result);
	$rows   = mysqli_fetch_all( $result,MYSQLI_ASSOC);
	//echo count($rows).'<br>';
	return $rows;
}

function getdata() {
	$comm = "SELECT id,text,image,mainlinks from entries where showing is null and image<>'' ORDER BY RAND() limit 1";
	$result = do_sql($comm);
	$result = $result[0];
	$toRet = array('id'=>$result['id'],'text'=>$result['text'],'image'=>$result['image'],'mainlinks'=>json_decode($result['mainlinks'],true));
	
	echo json_encode( $toRet );
}

function do_sql_queryEdit($dataOK) {
	global $conn;
	//$newtext = test_input($dataOK['newText']);
	
	if ($dataOK['newText']=='') {
		$sqlquery = "UPDATE entries set showing='y', mainlinks='".test_input(json_encode($dataOK['checkboxes']))."' where id='".test_input($dataOK['factID'])."'";
	} else {
		$sqlquery = "UPDATE entries set text='".test_input($dataOK['newText'])."', showing='y', mainlinks='".test_input(json_encode($dataOK['checkboxes']))."' where id='".test_input($dataOK['factID'])."'";
	}
	
	//echo $sqlquery;
	
	$result = mysqli_query($conn,$sqlquery) or die(mysqli_error($conn));
	echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	
}

function do_sql_queryNO($action,$dataOK) {
	date_default_timezone_set('Europe/Riga');
	$cur_time = date("YmdHis");
	global $conn;
	
	$sqlquery = "UPDATE entries set showing='n' where id='".test_input($dataOK)."'";
	
	//echo $sqlquery;
	
	$result = mysqli_query($conn,$sqlquery) or die(mysqli_error($conn));
	echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
}