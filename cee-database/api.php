<?php
$myRoot = $_SERVER['DOCUMENT_ROOT'].'../';
//echo $myRoot;

//require_once("/mnt/nfs/labstore-secondary-tools-project/edgars/connect.inc.php");
require_once $myRoot."connect.inc.php";

//error_reporting( E_ALL );
//ini_set( 'display_errors', '1' );
//header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$conn = connect_db("s53143__cee_db_p");

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

switch ( isset( $_REQUEST['act'] ) ? $_REQUEST['act'] : '' ){
	case "new_article":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            new_article_to_jury( $_REQUEST['data'] );
			break;
        }

	case "get_data":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            get_data_jury( $_REQUEST['data'] );
			break;
        }
		
	case "edit_data":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            edit_data_jury( $_REQUEST['data'] );
			break;
		}
		
	case "main":
		main_jury();
        break;
        /*
    case 'authorize':
        doAuthorizationRedirect();
        break;
    case 'userinfo':
        header('Content-Type: application/json');
        echo getUserInfo();
        break;
        */
}

function test_input( $data ) {
    $data = trim( $data );
    $data = stripslashes( $data );
    $data = htmlspecialchars( $data );
    return $data;
}

function do_sql1( $query ) {
	global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

function do_sql( $query ) {
	global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_NUM );
	//echo count($rows).'<br>';
	return $rows;
}

function meta_choices() {
	$sql_meta = "select choice_id, choise_value from meta_data group by choice_id, choise_value";
	$result = do_sql1($sql_meta);

	$toret = array();

	$mapping = array(0=>'links',1=>'projTypes',2=>'themes',3=>'wmProjects',4=>'languages',5=>'users');

	foreach($result as $row) {
		$choise_name = $mapping[$row['choice_id']];
		$toret[$choise_name][] = array('value'=>$row['choise_value'],'label'=>$row['choise_value']);
	}
	//{value: 'contest' , label: 'contest'},{value: 'education', label: 'education'}

	//var_dump($toret);

	return $toret;
}

function format_meta() {
	$sql_meta = "SELECT proj_id,choice_id,choise_value from meta_data";
	$result = do_sql1($sql_meta);

	$toret = array();

	$mapping = array(0=>'links',1=>'projTypes',2=>'themes',3=>'wmProjects',4=>'languages',5=>'users');

	foreach($result as $row) {
		$choise_name = $mapping[$row['choice_id']];
		$toret[$row['proj_id']][$choise_name][] = $row['choise_value'];
	}

	//var_dump($toret);

	return $toret;
}

//format_meta();

function main_jury() {
	$sql_main = "SELECT id,title,description,started,ended from projects";
	$metadata = format_meta();
	
	$result = do_sql1($sql_main);
	$final = array();

	$forsums = array(3,4,5,6);
	
	foreach($result as $resrow) {
		$thismeta = isset($metadata[$resrow['id']]) ? $metadata[$resrow['id']] : array();

		$thisres = array('title'=>$resrow['title'],
						'description'=>$resrow['description'],
						'started'=>$resrow['started'],
						'ended'=>$resrow['ended'],
						'id'=>$resrow['id'],

						'links'=>isset($thismeta['links']) ? $thismeta['links'] : array(),
						'projTypes'=>isset($thismeta['projTypes']) ? $thismeta['projTypes'] : array(),
						'themes'=>isset($thismeta['themes']) ? $thismeta['themes'] : array(),
						'wmProjects'=>isset($thismeta['wmProjects']) ? $thismeta['wmProjects'] : array(),
						'languages'=>isset($thismeta['languages']) ? $thismeta['languages'] : array(),
						'users'=>isset($thismeta['users']) ? $thismeta['users'] : array()
				);
		$final[] = $thisres;
	}
	
	echo json_encode( array('data'=>$final,'choices'=>meta_choices()) );
}

function get_one_project($id) {
	$sql_main = "SELECT id,title,description,started,ended from projects where id=$id";
	$metadata = format_meta();
	
	$result = do_sql1($sql_main);
	$final = array();
	
	foreach($result as $resrow) {
		$thismeta = isset($metadata[$resrow['id']]) ? $metadata[$resrow['id']] : array();

		$thisres = array('title'=>$resrow['title'],
						'description'=>$resrow['description'],
						'started'=>$resrow['started'],
						'ended'=>$resrow['ended'],
						'id'=>$resrow['id'],

						'links'=>isset($thismeta['links']) ? $thismeta['links'] : array(),
						'projTypes'=>isset($thismeta['projTypes']) ? $thismeta['projTypes'] : array(),
						'themes'=>isset($thismeta['themes']) ? $thismeta['themes'] : array(),
						'wmProjects'=>isset($thismeta['wmProjects']) ? $thismeta['wmProjects'] : array(),
						'languages'=>isset($thismeta['languages']) ? $thismeta['languages'] : array(),
						'users'=>isset($thismeta['users']) ? $thismeta['users'] : array()
				);
		$final[] = $thisres;
	}
	
	echo json_encode( $final );
}

function insert_meta($data,$last_id) {
	global $conn;
	//isAuthOkay();

	$toSave = array();
	$mapping = array('links'=>0,'projTypes'=>1,'themes'=>2,'wmProjects'=>3,'languages'=>4,'users'=>5);

	
	foreach($data as $row => $values) {
		if (!array_key_exists($row,$mapping)) {continue;}//already added into other table or an error
		$choiceID = $mapping[$row];
		
		if ($choiceID==0) {
			$values = explode("\n",$values);
		}
		
		if (count($values)<1) {continue;}

		foreach($values as $val) {
			if ($val=="") {continue;}
			$stmt = $conn->prepare("INSERT INTO meta_data (proj_id,choice_id,choise_value) VALUES (?, ?, ?)");
			$stmt->bind_param("dds", $last_id,$choiceID,$val);
			$stmt->execute();
		}
	}
	//echo json_encode(array('status' => 'success'));
}

function new_article_to_jury($data) {
	global $conn;
	//isAuthOkay();
	
	$title = test_input($data['title']);
    $description = test_input($data['description']);
    $started = test_input($data['start']);
    $ended = test_input($data['end']);
    
	//$user_data = getUserInfo();
	//$res_user_data = json_decode( $user_data );
	$username = "";//$res_user_data->query->userinfo->name;'
	
	//var_dump($data);
    
    date_default_timezone_set('Europe/Riga');
	$cur_time = date("YmdHis");
    $stmt = $conn->prepare("INSERT INTO projects (title,description,started,ended,added_time,added_user) VALUES (?, ?, ?, ?, ?, ?)");
    
    //{'wp':this.state.wikipedia,'user':this.state.userName,'article':this.state.articleName,'size':this.state.articleSize}

	$stmt->bind_param("ssssss", $title,$description,$started,$ended,$cur_time,$username);
    //$stmt->execute();
    
    if (!$stmt->execute()) {
        echo json_encode(array('status' => 'error', 'message'=> "Execute failed: (" . $stmt->errno . ") " . $stmt->error));
    } else {
		$last_id = $conn->insert_id;
		//echo $last_id;
		insert_meta($data,$last_id);

        echo json_encode(array('status' => 'success'));
    }
}