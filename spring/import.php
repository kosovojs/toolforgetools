<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//header('Content-Type: application/json');

//date_default_timezone_set('Europe/Riga');

require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';
//require_once __DIR__.'/../php/simple-mysqli.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

$tfc->tool_user_name = 'edgars';

$use_db_cache = true;

$db_cache = [];
$db_servers = [
			'fast' => '.web.db.svc.eqiad.wmflabs' ,
			'slow' => '.analytics.db.svc.eqiad.wmflabs' ,
			'old' => '.labsdb'
] ;

function getDBpassword () {
		$passwordfile = '/data/project/edgars/replica.my.cnf' ;
		
		$config = parse_ini_file( $passwordfile );
		
	return ['user'=>$config['user'],'passw'=>$config['password']];
}

function openDB ( $language , $project , $slow_queries = false , $persistent = false ) {
	global $db_cache, $db_servers, $use_db_cache, $tfc;
	
	$db_key = "$language.$project" ;
	if ( !$persistent and isset ( $db_cache[$db_key] ) ) return $db_cache[$db_key] ;

	$db_creds = getDBpassword ();
	$dbname = $tfc->getDBname ( $language , $project ) ;

	# Try optimal server
	$server = substr( $dbname, 0, -2 ) . ( $slow_queries ? $db_servers['slow'] : $db_servers['fast'] ) ;
	if ( $persistent ) $server = "p:$server" ;
	$db = new SimpleMySQLi($server, $db_creds['user'], $db_creds['passw'], $dbname, "utf8mb4", "assoc");//@new mysqli($server, $this->mysql_user, $this->mysql_password , $dbname);

	if ( !$persistent and $use_db_cache ) $db_cache[$db_key] = $db ;
	return $db ;
}

$conn = openDB ( 'lv' , 'wikipedia');

$ex = $conn->query('select * from recentchanges limit 50')->fetchAll('assoc');

var_dump($ex);