 <?php
//error_reporting( E_ALL );
//ini_set( 'display_errors', '1' );

$file = '../../replica.my.cnf';
$config = parse_ini_file($file);
$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$config['user'],$config['password'],"s53143__jury_p");

require_once __DIR__.'/../oauth.php';

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

mysqli_set_charset( $conn, "utf8" );
//echo 'sdfsfsdfsf';
/*
if ( isset( $_REQUEST[ "data" ] ) && !empty( $_REQUEST[ "data" ] ) ) {
	$data = $_REQUEST[ "data" ];
}
*/
$action	 = $_REQUEST[ "action" ];

if ( isset( $action ) && !empty( $action ) ) {
		switch ( $action ) {
	case "new_article_to_jury":
		header('Content-Type: application/json');
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            new_article_to_jury( $_REQUEST['data'] );
			break;
        }
		
		
	case "get_data_jury":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            get_data_jury( $_REQUEST['data'] );
			break;
        }
		
	case "edit_data_jury":
        if ( isset ( $_REQUEST['data'] ) && isset ( $_REQUEST['data'] ) ) {
            edit_data_jury( $_REQUEST['data'] );
			break;
		}
		
	case "main_jury":
		//echo 'fgfdgdfgdf';
		header('Content-Type: application/json');
		main_jury();
		//new_article_to_jury( $data );
		break;
		}
}

function do_sql( $conn,$query ) {
	//global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_NUM );
	//echo count($rows).'<br>';
	return $rows;
}

function do_sql1( $conn,$query ) {
	//global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

function get_data_jury($data) {
	global $conn;
	$sql = "SELECT title, point_new, points_size, points_images, points_wd, author from entries where id=".$data;
	$result = do_sql($conn,$sql);
	$res = $result[0];
	
	echo json_encode( array('title'=>$res[0],'newd'=>$res[1],'size'=>$res[2],'imaged'=>$res[3],'data'=>$res[4],'author'=>$res[5]) );
}

function edit_data_jury($data) {
	global $conn;
	date_default_timezone_set('Europe/Riga');
	$thisid = (int)$data['id'];
	
	$article = test_input($data['article']);
	$author = test_input($data['author']);
	
	$user_data1 = getUserInfo();
	$res_user_data = json_decode( $user_data1 );
	$username = $res_user_data->query->userinfo->name;
	
	$sql1 = "SELECT author, user_add from entries where id=".$thisid;
	$result = do_sql1($conn,$sql1);
	$user1=$result[0]['author'];
	$user2=$result[0]['user_add'];
	
	$sqlput = "";
	//title,author,date_add,point_new,points_size,points_images,points_wd
	
	//echo $user1;
	if ($username=='Edgars2007' || $username==$user1 || $username==$user2) {
		if (isset( $data['new'] ))
			$sqlput .= " point_new=".$data['new'];
		
		if (isset( $data['size'] ))
			$sqlput .= " points_size=".$data['size'];
		
		if (isset( $data['images'] ))
			$sqlput .= " points_images=".$data['images'];
		
		if (isset( $data['wikidata'] ))
			$sqlput .= " points_wd=".$data['wikidata'];
		
		if (isset( $data['author'] ))
			$sqlput .= ' author="'.test_input($data['author']).'"';
		
		if (isset( $data['title'] ))
			$sqlput .= ' title="'.test_input($data['title']).'"';
		
		$sql1 = "UPDATE entries SET ".$sqlput. " where id=".$thisid;
		$resulfsdfdft = mysqli_query( $conn, $sql1 );
		
		echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	} else {
		echo json_encode(array('status' => 'fail','message'=> 'failed'));
	}
}

function main_jury() {
	global $conn;
	$sql = "SELECT title, author, date_add, point_new, points_size, points_images, points_wd, id from entries";
	$result = do_sql( $conn,$sql);
	$bigm = array();
	$sums = array();
	$final = array();

	$forsums = array(3,4,5,6);
	
	foreach($result as $resrow) {
		$thisres = array('id'=>$resrow[7],'title'=>$resrow[0],'added'=>$resrow[2],'new'=>$resrow[3],'size'=>$resrow[4],'images'=>$resrow[5],'wikidata'=>$resrow[6],'sum'=>array_sum(array($resrow[3],$resrow[4],$resrow[5],$resrow[6])));
		//if array_key_exists($resrow[1],$bigm)
		$bigm[$resrow[1]][] = $thisres;
		
		if (array_key_exists($resrow[1],$sums)) {
			foreach($forsums as $summing) {
				$sums[$resrow[1]][($summing-2)] += $resrow[$summing];
			}
		} else {
			foreach($forsums as $summing) {
				$sums[$resrow[1]][($summing-2)] = $resrow[$summing];
			}
		}
	}

	foreach($sums as $user => $sumvals) {
		$fullsum = array_sum($sumvals);
		$sumvals['full'] = $fullsum;
		
		$final[$user] = array('meta'=>array($fullsum,sizeof($bigm[$user])), 'articles'=>$bigm[$user]);
	}

	#header('Content-Type: application/json');
	//echo json_encode( $result );
	//echo '<br>';
	//echo json_encode( $bigm );
	//echo '<br>';
	echo json_encode( $final );
}

function new_article_to_jury($data) {
    //$ch = null;
	
    isAuthOkay();
	
	global $conn;
	
	$article = test_input($data['article']);
	$author = test_input($data['author']);
	
	
	$stmt1 = $conn->prepare("SELECT title from entries where title=?");
	$stmt1 ->bind_param("s", $article);
	$stmt1->execute();
   $result = $stmt1->get_result();
   $num_of_rows = $result->num_rows;
   //echo $num_of_rows;
	
	if ($num_of_rows>0) {
		
echo json_encode(array(
    'status' => 'error',
    'message'=> 'Raksts jau ir pievienots!'
));
	exit(0);
	}
	
	$user_data = getUserInfo();
	$res_user_data = json_decode( $user_data );
	$username = $res_user_data->query->userinfo->name;
	//echo $username;
	
	date_default_timezone_set('Europe/Riga');
	//$data = json_encode($data);
	$cur_time = date("YmdHis");
	$stmt = $conn->prepare("INSERT INTO entries (title,author,date_add,user_add,point_new,points_size,points_images,points_wd) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssdddd", $article,$author,$cur_time,$username,$data['new'],$data['size'],$data['images'],$data['wikidata']);
	//$stmt->execute();
	
	
if (!$stmt->execute()) {
    //echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	
echo json_encode(array(
    'status' => 'error',
    'message'=> "Execute failed: (" . $stmt->errno . ") " . $stmt->error
));

}
else {
echo json_encode(array(
    'status' => 'success'
));
	
}
}
