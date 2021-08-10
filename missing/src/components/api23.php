<?php
/**
 * To the extent possible under law, the author(s) have dedicated all copyright
 * and related and neighboring rights to this software to the public domain
 * worldwide. This software is distributed without any warranty.
 *
 * See <http://creativecommons.org/publicdomain/zero/1.0/> for a copy of the
 * CC0 Public Domain Dedication.
**/

header( 'Content-Type: application/json' );
require_once("/mnt/nfs/labstore-secondary-tools-project/edgars/connect.inc.php");

$conn = connect_db("s53143__missing_p");

function do_sql( $query ) {
	global $conn;
	$result = mysqli_query( $conn, $query );
	$rows   = mysqli_fetch_all( $result, MYSQLI_ASSOC );
	//echo count($rows).'<br>';
	return $rows;
}

$sql = "SELECT lv, orig, lang, iws, wd, descr from list";
$result = do_sql($sql);

echo json_encode( $result );