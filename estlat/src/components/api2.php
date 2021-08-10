<?php
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
header("Access-Control-Allow-Origin: *");
//header('Content-Type: application/json');

$conn = mysqli_connect( "localhost", "edgars", "edgars", 'estlat' );

if (mysqli_connect_errno())
{
	die("ERROR: Could not connect. " . mysqli_connect_error());
}

mysqli_set_charset( $conn, "utf8" );
//echo 'sdfsfsdfsf';

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

function main_jury() {
	$sql = "SELECT title, author, date_add, point_new, points_size, points_images, points_wd, id, wiki from entries";
	$result = do_sql($sql);
	$bigm = array();
	$sums = array();
	$final = array();

	$forsums = array(3,4,5,6);
	
	foreach($result as $resrow) {
		$thisres = array('id'=>$resrow[7],'title'=>$resrow[0],'added'=>$resrow[2],'new'=>$resrow[3],'size'=>$resrow[4],'images'=>$resrow[5],'wikidata'=>$resrow[6],'sum'=>array_sum(array($resrow[3],$resrow[4],$resrow[5],$resrow[6])));
		//if array_key_exists($resrow[1],$bigm)
		$bigm[$resrow[8]][$resrow[1]][] = $thisres;
		
		if (array_key_exists($resrow[1],$sums)) {
			foreach($forsums as $summing) {
				$sums[$resrow[8]][$resrow[1]][($summing-2)] += $resrow[$summing];
			}
		} else {
			foreach($forsums as $summing) {
				$sums[$resrow[8]][$resrow[1]][($summing-2)] = $resrow[$summing];
			}
		}
    }
    
	foreach($sums as $wiki => $wikiValue) {
        
        foreach($sums[$wiki] as $user => $sumvals) {
            $fullsum = array_sum($sumvals);
            $sumvals['full'] = $fullsum;

            $sum = 0;

            
            
            foreach($bigm[$wiki][$user] as $article) {
                $sum += $article['sum'];
            }
            
            $final[$wiki][] = array('user'=>$user, 'points'=>$sum,'articlecount'=>sizeof($bigm[$wiki][$user]), 'articles'=>$bigm[$wiki][$user]);
        }
    }
    
	echo json_encode( $final );
}

main_jury();