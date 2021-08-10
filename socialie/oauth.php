<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//header('Content-Type: text/html; charset=utf-8');
//include_once('../connect_inc.php');
$myRoot = $_SERVER['DOCUMENT_ROOT'].'../';

//require_once("/mnt/nfs/labstore-secondary-tools-project/edgars/connect.inc.php");
require_once $myRoot."connect.inc.php";
//$db_handle = new DBController();
/**
 * Written in 2013 by Brad Jorsch
 *
 * To the extent possible under law, the author(s) have dedicated all copyright
 * and related and neighboring rights to this software to the public domain
 * worldwide. This software is distributed without any warranty.
 *
 * See <http://creativecommons.org/publicdomain/zero/1.0/> for a copy of the
 * CC0 Public Domain Dedication.
 */

// ******************** CONFIGURATION ********************

/**
 * Set this to point to a file (outside the webserver root!) containing the
 * following keys:
 * - agent: The HTTP User-Agent to use
 * - consumerKey: The "consumer token" given to you when registering your app
 * - consumerSecret: The "secret token" given to you when registering your app
 */
$inifile = '../../oauth.ini';

/**
 * Set this to the Special:OAuth/authorize URL.
 * To work around MobileFrontend redirection, use /wiki/ rather than /w/index.php.
 */
$mwOAuthAuthorizeUrl = 'https://www.mediawiki.org/wiki/Special:OAuth/authorize';

/**
 * Set this to the Special:OAuth URL.
 * Note that /wiki/Special:OAuth fails when checking the signature, while
 * index.php?title=Special:OAuth works fine.
 */
$mwOAuthUrl = 'https://www.mediawiki.org/w/index.php?title=Special:OAuth';

/**
 * Set this to the interwiki prefix for the OAuth central wiki.
 */
$mwOAuthIW = 'mw';

/**
 * Set this to the API endpoint
 */
$apiUrl = 'https://lv.wikipedia.org/w/api.php';

/**
 * Set this to Special:MyTalk on the above wiki
 */
#$mytalkUrl = 'https://test.wikidata.org/wiki/Special:MyTalk#Hello.2C_world';

/**
 * This should normally be "500". But Tool Labs insists on overriding valid 500
 * responses with a useless error page.
 */
$errorCode = 200;

// ****************** END CONFIGURATION ******************

// Setup the session cookie
session_name( 'EdgarsTool' );
$params = session_get_cookie_params();
session_set_cookie_params(
    $params['lifetime'],
    dirname( $_SERVER['SCRIPT_NAME'] )
);

function test_input( $data ) {
   $data = trim( $data );
   $data = stripslashes( $data );
   $data = htmlspecialchars( $data );
   return $data;
}


// Read the ini file
$ini = parse_ini_file( $inifile );
if ( $ini === false ) {
    header( "HTTP/1.1 $errorCode Internal Server Error" );
    echo 'The ini file could not be read';
    exit(0);
}
if ( !isset( $ini['agent'] ) ||
    !isset( $ini['consumerKey'] ) ||
    !isset( $ini['consumerSecret'] )
) {
    header( "HTTP/1.1 $errorCode Internal Server Error" );
    echo 'Required configuration directives not found in ini file';
    exit(0);
}
$gUserAgent = $ini['agent'];
$gConsumerKey = $ini['consumerKey'];
$gConsumerSecret = $ini['consumerSecret'];

// Load the user token (request or access) from the session
$gTokenKey = '';
$gTokenSecret = '';
session_start();
if ( isset( $_SESSION['tokenKey'] ) ) {
    $gTokenKey = $_SESSION['tokenKey'];
    $gTokenSecret = $_SESSION['tokenSecret'];
} elseif ( isset( $_COOKIE['tokenKey'] ) ) {
    $gTokenKey = $_COOKIE['tokenKey'];
    $gTokenSecret = $_COOKIE['tokenSecret'];
}

session_write_close();

// Fetch the access token if this is the callback from requesting authorization
if ( isset( $_REQUEST['oauth_verifier'] ) && $_REQUEST['oauth_verifier'] ) {
    fetchAccessToken();
}

//echo 'sdfsdfsdf';
// Take any requested action
$conn = connect_db("s53143__meta_p");

$action = test_input($_REQUEST['act']);

if (array_key_exists('data', $_REQUEST)) {
	$dataOK = $_REQUEST['data'];
} else {
	$dataOK = '';
}
	
switch ( isset( $_REQUEST['act'] ) ? $_REQUEST['act'] : '' ){
	
	case "getF":
		header('Content-Type: application/json');
		getdata();
		break;
		
	case "no":
		header('Content-Type: application/json');
		do_sql_queryNO('no',$dataOK);
		break;
		
	case "edit":
		header('Content-Type: application/json');
		do_sql_queryEdit($dataOK);
		break;
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
	$comm = "SELECT id,text,image,mainlinks from soctikli where showing is null and image<>'' ORDER BY RAND() limit 1";
	$result = do_sql($comm);
	$result = $result[0];
	$toRet = array('id'=>$result['id'],'text'=>$result['text'],'image'=>$result['image'],'mainlinks'=>json_decode($result['mainlinks'],true));
	
	echo json_encode( $toRet );
}

function do_sql_queryEdit($dataOK) {
	date_default_timezone_set('Europe/Riga');
	$cur_time = date("YmdHis");
	global $conn;
    isAuthOkay();
	//$newtext = test_input($dataOK['newText']);
	
	$user_data1 = getUserInfo();
	$res_user_data = json_decode( $user_data1 );
	$username = $res_user_data->query->userinfo->name;
	
	if ($dataOK['newText']=='') {
		$sqlquery = "UPDATE soctikli set showing='y', timeUpd='$cur_time', UpdBy='$username', mainlinks='".test_input(json_encode($dataOK['checkboxes']))."' where id='".test_input($dataOK['factID'])."'";
	} else {
		$sqlquery = "UPDATE soctikli set text='".test_input($dataOK['newText'])."', timeUpd='$cur_time', UpdBy='$username', showing='y', mainlinks='".test_input(json_encode($dataOK['checkboxes']))."' where id='".test_input($dataOK['factID'])."'";
	}
	
	//echo $sqlquery;
	
	$result = mysqli_query($conn,$sqlquery) or die(mysqli_error($conn));
	echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	
}

function do_sql_queryNO($action,$dataOK) {
	date_default_timezone_set('Europe/Riga');
	$cur_time = date("YmdHis");
	global $conn;
    isAuthOkay();
	
	$user_data1 = getUserInfo();
	$res_user_data = json_decode( $user_data1 );
	$username = $res_user_data->query->userinfo->name;
	
	$sqlquery = "UPDATE soctikli set timeUpd='$cur_time', UpdBy='$username', showing='n' where id='".test_input($dataOK)."'";
	
	//echo $sqlquery;
	
	$result = mysqli_query($conn,$sqlquery) or die(mysqli_error($conn));
	echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
}

// ******************** CODE ********************

/**
 * Utility function to sign a request
 *
 * Note this doesn't properly handle the case where a parameter is set both in
 * the query string in $url and in $params, or non-scalar values in $params.
 *
 * @param string $method Generally "GET" or "POST"
 * @param string $url URL string
 * @param array $params Extra parameters for the Authorization header or post
 *     data (if application/x-www-form-urlencoded).
 * @return string Signature
 */
 
function get_data_jury($data) {
	global $conn_jury;
	$sql = "SELECT title, point_new, points_size, points_images, points_wd, author from entries where id=".$data;
	$result = do_sql($conn_jury,$sql);
	$res = $result[0];
	
	echo json_encode( array('title'=>$res[0],'newd'=>$res[1],'size'=>$res[2],'imaged'=>$res[3],'data'=>$res[4],'author'=>$res[5]) );
}

function edit_data_jury($data) {
	global $conn_jury;
	date_default_timezone_set('Europe/Riga');
	$thisid = (int)$data['id'];
	
	$article = test_input($data['article']);
	$author = test_input($data['author']);
	
	$user_data1 = getUserInfo();
	$res_user_data = json_decode( $user_data1 );
	$username = $res_user_data->query->userinfo->name;
	
	$sql1 = "SELECT author, user_add from entries where id=".$thisid;
	$result = do_sql1($conn_jury,$sql1);
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
		$resulfsdfdft = mysqli_query( $conn_jury, $sql1 );
		
		echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	} else {
		echo json_encode(array('status' => 'fail','message'=> 'failed'));
	}
}

function npp_getdata($last_id,$type_selection) {
	global $conn_npp;
	
	$comm = "SELECT id,title FROM main WHERE (comment is NULL and reviewed is NULL) and id>";
	
	if ($type_selection=="rnd") {
		$comm .= "0 ORDER BY RAND() limit 1";
	} else if ($type_selection=="next") {
		$comm .= $last_id." limit 1";
	}
	
	//echo '-'.$comm.'---';
	$row = do_sql( $conn_npp,$comm );
	//echo $row;
	
	//fixme: if no results
	
	echo json_encode($row);//var_dump($row);//echo json_encode($row);
}

function npp_leave_comment($action,$id,$comment) {
	global $conn_npp;
    isAuthOkay();
	
	//$user_data = getUserInfo();
	//$res_user_data = json_decode( $user_data );
	//$username = $res_user_data->query->userinfo->name;
	
	//sql_put($conn_npp,"UPDATE main SET comment=? WHERE id=?","si",array($comment,$id));
	//insert into actions...
	
	
	$stmt = $conn_npp->prepare("UPDATE main SET comment=? WHERE id=?");
	$stmt->bind_param("si", $comment,$id);
	$stmt->execute();
	
}
/*
function sql_put($conn,$sql_query,$bind1,$bind2) {
	$stmt = $conn->prepare($sql_query);
	$stmt->bind_param($bind1, ...$bind2);
	$stmt->execute();
}*/
	/*
if (!) {
    //echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	
return json_encode(array(
    'status' => 'error',
    'message'=> "Execute failed: (" . $stmt->errno . ") " . $stmt->error
));

}
else {
return json_encode(array(
    'status' => 'success'
));
	
}
*/

function npp_do_sql_query($action,$id) {
	global $conn_npp;
    isAuthOkay();
	
	$user_data1 = getUserInfo();
	$res_user_data = json_decode( $user_data1 );
	$usernam1e = $res_user_data->query->userinfo->name;
	
	//sql_put($conn_npp,"UPDATE main SET reviewed=? WHERE id=?","si",array($username,$id));
	//insert into actions...
	
	
	$stmt1 = $conn_npp->prepare("UPDATE main SET reviewed=? WHERE id=?");
	$stmt1->bind_param("si",$usernam1e,$id);
	//$stmt1->execute();
	
	if (!$stmt1->execute()) {
		return json_encode(array(
			'status' => 'error',
			'message'=> "Execute failed: (" . $stmt1->errno . ") " . $stmt1->error
		));
	}
	else
	{
		return json_encode(array(
			'status' => 'success'
		));
		
	}
}

// ******************** CODE ********************

/**
 * Utility function to sign a request
 *
 * Note this doesn't properly handle the case where a parameter is set both in
 * the query string in $url and in $params, or non-scalar values in $params.
 *
 * @param string $method Generally "GET" or "POST"
 * @param string $url URL string
 * @param array $params Extra parameters for the Authorization header or post
 *     data (if application/x-www-form-urlencoded).
 * @return string Signature
 */
function sign_request( $method, $url, $params = array() ) {
    global $gConsumerSecret, $gTokenSecret;

    $parts = parse_url( $url );
    // We need to normalize the endpoint URL
    $scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'http';
    $host = isset( $parts['host'] ) ? $parts['host'] : '';
    $port = isset( $parts['port'] ) ? $parts['port'] : ( $scheme == 'https' ? '443' : '80' );
    $path = isset( $parts['path'] ) ? $parts['path'] : '';
    if ( ( $scheme == 'https' && $port != '443' ) ||
        ( $scheme == 'http' && $port != '80' )
    ) {
        // Only include the port if it's not the default
        $host = "$host:$port";
    }

    // Also the parameters
    $pairs = array();
    parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );
    $query += $params;
    unset( $query['oauth_signature'] );
    if ( $query ) {
        $query = array_combine(
            // rawurlencode follows RFC 3986 since PHP 5.3
            array_map( 'rawurlencode', array_keys( $query ) ),
            array_map( 'rawurlencode', array_values( $query ) )
        );
        ksort( $query, SORT_STRING );
        foreach ( $query as $k => $v ) {
            $pairs[] = "$k=$v";
        }
    }

    $toSign = rawurlencode( strtoupper( $method ) ) . '&' .
        rawurlencode( "$scheme://$host$path" ) . '&' .
        rawurlencode( join( '&', $pairs ) );
    $key = rawurlencode( $gConsumerSecret ) . '&' . rawurlencode( $gTokenSecret );
    return base64_encode( hash_hmac( 'sha1', $toSign, $key, true ) );
}

/**
 * Request authorization
 * @return void
 */
function doAuthorizationRedirect() {
    global $mwOAuthUrl, $mwOAuthAuthorizeUrl, $gUserAgent, $gConsumerKey, $gTokenSecret, $errorCode;

    // First, we need to fetch a request token.
    // The request is signed with an empty token secret and no token key.
    $gTokenSecret = '';
    $url = $mwOAuthUrl . '/initiate';
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'format' => 'json',

        // OAuth information
        'oauth_callback' => 'oob', // Must be "oob" for MWOAuth
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    ) );
    $signature = sign_request( 'GET', $url );
    $url .= "&oauth_signature=" . urlencode( $signature );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    curl_close( $ch );
    $token = json_decode( $data );
    if ( is_object( $token ) && isset( $token->error ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
        exit(0);
    }
    if ( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid response from token request';
        exit(0);
    }

    // Now we have the request token, we need to save it for later.
    session_start();
    $_SESSION['tokenKey'] = $token->key;
    $_SESSION['tokenSecret'] = $token->secret;
    $t = time()+60*60*24*30; // expires in one month
    setcookie ( 'tokenKey',$_SESSION['tokenKey'],$t,'/pltools' );
    setcookie ( 'tokenSecret',$_SESSION['tokenSecret'],$t,'/pltools' );
    session_write_close();

    // Then we send the user off to authorize
    $url = $mwOAuthAuthorizeUrl;
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'oauth_token' => $token->key,
        'oauth_consumer_key' => $gConsumerKey,
    ) );
    header( "Location: $url" );
    echo 'Please see <a href="' . htmlspecialchars( $url ) . '">' . htmlspecialchars( $url ) . '</a>';
}


function do_sql1( $conn,$query ) {
	//global $conn_jury;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}



function main_jury() {
	global $conn_jury;
	$sql = "SELECT title, author, date_add, point_new, points_size, points_images, points_wd, id from entries";
	$result = do_sql( $conn_jury,$sql);
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


function main_reminders() {
	global $conn_reminders;
	$sql = "SELECT id, page, add_date, notif_time, completed, ping_user, comment from main where archive is NULL";
	//echo $sql.'<br>';
	$result = do_sql1($conn_reminders,$sql);
	
	echo json_encode( $result );
}

function main_wikidays() {
	global $conn_wikidays;
	$sql = "SELECT day,article,date,comment from entries";
	//echo $sql.'<br>';
	$result = do_sql1($conn_wikidays,$sql);
	
	echo json_encode( $result );
}


/**
 * Handle a callback to fetch the access token
 * @return void
 */
function fetchAccessToken() {
    global $mwOAuthUrl, $gUserAgent, $gConsumerKey, $gTokenKey, $gTokenSecret;

    $url = $mwOAuthUrl . '/token';
    $url .= strpos( $url, '?' ) ? '&' : '?';
    $url .= http_build_query( array(
        'format' => 'json',
        'oauth_verifier' => $_GET['oauth_verifier'],

        // OAuth information
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_token' => $gTokenKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    ) );
    $signature = sign_request( 'GET', $url );
    $url .= "&oauth_signature=" . urlencode( $signature );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    curl_close( $ch );
    $token = json_decode( $data );
    if ( is_object( $token ) && isset( $token->error ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
        exit(0);
    }
    if ( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Invalid response from token request';
        exit(0);
    }

    // Save the access token
    session_start();
    $_SESSION['tokenKey'] = $gTokenKey = $token->key;
    $_SESSION['tokenSecret'] = $gTokenSecret = $token->secret;
    $t = time()+60*60*24*30; // expires in one month
    setcookie ( 'tokenKey',$_SESSION['tokenKey'],$t,'/pltools' );
    setcookie ( 'tokenSecret',$_SESSION['tokenSecret'],$t,'/pltools' );
    session_write_close();
}

/**
 * Send an API query with OAuth authorization
 *
 * @param array $post Post data
 * @param object $ch Curl handle
 * @return array API results
 */
function doApiQuery( $post, &$ch = null, $returnraw = null ) {
    global $apiUrl, $gUserAgent, $gConsumerKey, $gTokenKey;

    $headerArr = array(
        // OAuth information
        'oauth_consumer_key' => $gConsumerKey,
        'oauth_token' => $gTokenKey,
        'oauth_version' => '1.0',
        'oauth_nonce' => md5( microtime() . mt_rand() ),
        'oauth_timestamp' => time(),

        // We're using secret key signatures here.
        'oauth_signature_method' => 'HMAC-SHA1',
    );
    $signature = sign_request( 'POST', $apiUrl, $post + $headerArr );
    $headerArr['oauth_signature'] = $signature;

    $header = array();
    foreach ( $headerArr as $k => $v ) {
        $header[] = rawurlencode( $k ) . '="' . rawurlencode( $v ) . '"';
    }
    $header = 'Authorization: OAuth ' . join( ', ', $header );

    if ( !$ch ) {
        $ch = curl_init();
    }
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_URL, $apiUrl );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $post ) );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array( $header ) );
    //curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_USERAGENT, $gUserAgent );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    $data = curl_exec( $ch );
    if ( !$data ) {
        header( "HTTP/1.1 Internal Server Error" );
        echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
        exit(0);
    }
    if ( $returnraw === null ) {
        $ret = json_decode( $data );
        if ( $ret === null ) {
            header( "HTTP/1.1 Internal Server Error" );
            echo 'Unparsable API response: <pre>' . htmlspecialchars( $data ) . '</pre>';
            exit(0);
        }
        return $ret;
    } else {
        return $data;
    }
}


//----------------------------------------------------------------
//
//
//----------------------------------------------------------------


/**
 * logout
 * @return void
 */
function logout(){
    session_start();
    setcookie( 'tokenKey', '', 1, '/edgars' );
    setcookie( 'tokenSecret', '', 1, '/edgars' );
    $_SESSION['tokenKey'] = '';
    $_SESSION['tokenSectret'] = '';
    $gTokenKey = null;
    $gTokenSecret = null;
    session_write_close();
}


/**
 * Send an API query to rollback an edit
 *
 * @param array $revid revision id
 * @param object $title page title
 * @param array $usertext user name
 * @return API error
 */
 

function arhivet($id) {
	global $conn_reminders;
    isAuthOkay();
	$id = (int)$id;
	
	$user_data = getUserInfo();
	$res_user_data = json_decode( $user_data );
	$username = $res_user_data->query->userinfo->name;
	//$username = 'Edgars2007';
	
	//sql_put($conn_npp,"UPDATE main SET comment=? WHERE id=?","si",array($comment,$id));
	//insert into actions...
	$sql1 = "SELECT add_user, ping_user from main where id=".$id;
	$result = do_sql1($conn_reminders,$sql1);
	$user1=$result[0]['add_user'];
	$user2=$result[0]['ping_user'];
	
	//echo $user1;
	if ($username=='Edgars2007' || $username==$user1 || $username==$user2) {
		$stmt = $conn_reminders->prepare("UPDATE main SET archive=1 WHERE id=?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		echo json_encode(array('status' => 'good','message'=> 'Everything is ok'));
	} else {
		echo json_encode(array('status' => 'fail','message'=> 'failed'));
	}
	
}

function new_article_to_jury($data) {
    //$ch = null;
	
    isAuthOkay();
	
	global $conn_jury;
	
	$article = test_input($data['article']);
	$author = test_input($data['author']);
	
	
	$stmt1 = $conn_jury->prepare("SELECT title from entries where title=?");
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
	$stmt = $conn_jury->prepare("INSERT INTO entries (title,author,date_add,user_add,point_new,points_size,points_images,points_wd) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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


function new_article_to_wikidays($data) {
    //$ch = null;
	global $conn_wikidays;
	
    isAuthOkay();
	
	$article = isset($data['article']) ? test_input($data['article']) : "";
	$day = isset($data['day']) ? test_input($data['day']) : 999;
	$comment = isset($data['comment']) ? test_input($data['comment']) : "";
	$date = isset($data['date']) ? test_input($data['date']) : "";
	
	if ($date!="") {
		//$date = date('d/m/Y H:i',strtotime($dateOLD));
		$date = DateTime::createFromFormat('Y-m-d', $date);
	}
	$user_data = getUserInfo();
	$res_user_data = json_decode( $user_data );
	$username = $res_user_data->query->userinfo->name;
	//echo $username;
	
	date_default_timezone_set('Europe/Riga');
	//$data = json_encode($data);
	$cur_time = date("YmdHis");
	$notif_time = $date->format('Ymd');//date("YmdHis",$date);
	//echo $notif_time;
	$stmt = $conn_wikidays->prepare("INSERT INTO entries (day,article,date,comment,entry_add_date,entry_add_user) VALUES (?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("dsssss", $day,$article,$notif_time,$comment,$cur_time,$username);
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


function new_article_to_reminders($data) {
    //$ch = null;
	global $conn_reminders;
	
    isAuthOkay();
	
	$article = isset($data['article']) ? test_input($data['article']) : "";
	$author = isset($data['author']) ? test_input($data['author']) : "";
	$wiki = isset($data['wiki']) ? test_input($data['wiki']) : "";
	$dateOLD = isset($data['date']) ? test_input($data['date']) : "";
	$comment = isset($data['comment']) ? test_input($data['comment']) : "";
	$output = isset($data['output']) ? test_input($data['output']) : "";
	if ($dateOLD!="") {
		//$date = date('d/m/Y H:i',strtotime($dateOLD));
		$date = DateTime::createFromFormat('d/m/Y H:i', $dateOLD);
	}
	$user_data = getUserInfo();
	$res_user_data = json_decode( $user_data );
	$username = $res_user_data->query->userinfo->name;
	//echo $username;
	
	date_default_timezone_set('Europe/Riga');
	//$data = json_encode($data);
	$cur_time = date("YmdHis");
	$notif_time = $date->format('YmdHis');//date("YmdHis",$date);
	//echo $notif_time;
	$stmt = $conn_reminders->prepare("INSERT INTO main (page,add_date,comment,wiki,page_notif,add_user,ping_user,notif_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssssss", $article,$cur_time,$comment,$wiki,$output,$username,$author,$notif_time);
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

function getUserInfo(){

    $ch = null;

    // Fetch the userinfo
    $json = doApiQuery( array(
        'format' => 'json',
        'action' => 'query',
        'meta' => 'userinfo',
        'uiprop' => 'blockinfo|groups|rights|options',
    ), $ch, 1 );
    return $json;
}

function isAuthOkay ( $checkrights = array() ) {
    $ch = null;

    // First fetch the username
    $data = getUserInfo();
    $res = json_decode( $data );

    if ( isset( $res->error->code ) && $res->error->code === 'mwoauth-invalid-authorization' ) {
        echo json_encode(array(
    'status' => 'error',
    'message'=> 'You haven\'t authorized this application yet! Go <a href="../index.php?action=authorize" target="_parent">here</a> to do that.'
));
		//echo 'You haven\'t authorized this application yet! Go <a href="../index.php?action=authorize" target="_parent">here</a> to do that.';
        exit(0);
    }
    else if ( !isset( $res->query->userinfo ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Bad API response: <pre>' . htmlspecialchars( var_export( $res, 1 ) ) . '</pre>';
        exit(0);
    }
    else if ( isset( $res->query->userinfo->anon ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Not logged in. (How did that happen?)';
        exit(0);
    }

    foreach( $checkrights as $right){
        if ( !in_array($right,$res->query->userinfo->rights ) ) {
            echo 'You haven\'t '.$right.' rights';
            exit(0);
        }
    }
    return true ;
}

function fetchEditToken($type) {
    // Next fetch the edit token
    $res = doApiQuery( array(
        'format' => 'json',
        'action' => 'query',
        'meta' => 'tokens',
        'type' => $type,
        'bot' => 1
    ), $ch );

    if ( !isset( $res->query->tokens ) ) {
        header( "HTTP/1.1 $errorCode Internal Server Error" );
        echo 'Bad API response: <pre>' . htmlspecialchars( var_export( $res, 1 ) ) . '</pre>';
        exit(0);
    }
    return $res;
}