<?php
$user = str_replace ( 'tools.' , '' , get_current_user() ) ;
$passwordfile = parse_ini_file('/data/project/' . $user . '/replica.my.cnf');

function connect_db($dbname) {
	global $passwordfile;
	$conn = mysqli_connect("tools.db.svc.eqiad.wmflabs",$passwordfile['user'],$passwordfile['password'],$dbname);
	return $conn;
}
