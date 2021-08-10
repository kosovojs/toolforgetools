<?php
header('Content-Type: application/json; charset=utf-8');

function test_input( $data ) {
   $data = trim( $data );
   $data = stripslashes( $data );
   $data = htmlspecialchars( $data );
   return $data;
}

function get_sparql2() {
	$endpointUrl = 'https://query.wikidata.org/sparql';
	$sparqlQuery = <<< 'SPARQL'
select ?saraksts ?sarakstsLabel (COUNT(?image) as ?images) (COUNT(?item) as ?objects) (COUNT(?coords) as ?coords1) {
  ?item wdt:P2494 [];
        wdt:P2817 ?saraksts .
  optional {?item wdt:P18 ?image}
  optional {?item wdt:P625 ?coords}
  SERVICE wikibase:label { bd:serviceParam wikibase:language "lv,en". }
}
group by ?saraksts ?sarakstsLabel
ORDER BY DESC(?objects)
SPARQL;

	echo file_get_contents( $endpointUrl . '?query=' . urlencode( $sparqlQuery )  );
}

function get_sparql() {
	$endpointUrl = 'https://query.wikidata.org/sparql';
	$sparqlQuery = <<< 'SPARQL'
select ?saraksts ?sarakstsLabel (COUNT(?image) as ?images) (COUNT(?item) as ?objects) (COUNT(?coords) as ?coords1) {
  ?item wdt:P2494 [];
        wdt:P2817 ?saraksts .
  optional {?item wdt:P18 ?image}
  optional {?item wdt:P625 ?coords}
  SERVICE wikibase:label { bd:serviceParam wikibase:language "lv,en". }
}
group by ?saraksts ?sarakstsLabel
ORDER BY DESC(?objects)
SPARQL;

	return file_get_contents( 'https://query.wikidata.org/sparql?query=select%20%3Fsaraksts%20%3FsarakstsLabel%20(COUNT(%3Fimage)%20as%20%3Fimages)%20(COUNT(%3Fitem)%20as%20%3Fobjects)%20(COUNT(%3Fcoords)%20as%20%3Fcoords1)%20%7B%0A%20%20%3Fitem%20wdt%3AP2494%20%5B%5D%3B%0A%20%20%20%20%20%20%20%20wdt%3AP2817%20%3Fsaraksts%20.%0A%20%20optional%20%7B%3Fitem%20wdt%3AP18%20%3Fimage%7D%0A%20%20optional%20%7B%3Fitem%20wdt%3AP625%20%3Fcoords%7D%0A%20%20SERVICE%20wikibase%3Alabel%20%7B%20bd%3AserviceParam%20wikibase%3Alanguage%20%22lv%2Cen%22.%20%7D%0A%7D%0Agroup%20by%20%3Fsaraksts%20%3FsarakstsLabel%0AORDER%20BY%20DESC(%3Fobjects)&format=json'  );
}


//get_sparql();

function getdata() {
	$riga = array('Q20566055','Q20566047','Q20566041','Q20566050','Q20566035');
	$out = array();
	$json = json_decode(file_get_contents('mapping.json'), true);
	$sparql_res = get_sparql();
	$sparql = json_decode($sparql_res, true)['results']['bindings'];//json_decode(file_get_contents('query(2).json'), true)['results']['bindings'];
	
	$rigadata = array('objs'=>0,'imgs'=>0);
	
	foreach ($sparql as $entry) {
		$listid = str_replace('http://www.wikidata.org/entity/','',$entry['saraksts']['value']);
		
		if (in_array($listid,$riga)) {
			$rigadata['objs'] += $entry['objects']['value'];
			$rigadata['imgs'] += $entry['images']['value'];
		}
		
		if (!array_key_exists($listid,$json)) { continue; }
		
		$jsondata = $json[$listid];
		
		$objects = $entry['objects']['value'];
		$images = $entry['images']['value'];
		$imageProc = round(($images/$objects)*100,2);
		$out[] = array("type"=>"Feature","properties"=>array("name"=>$jsondata['title'],"density"=>$imageProc),"geometry"=>$jsondata['geom']);
	}
	$imageProcRiga = round(($rigadata['imgs']/$rigadata['objs'])*100,2);
	
	$out[] = array("type"=>"Feature","properties"=>array("name"=>'Rīga',"density"=>$imageProcRiga),"geometry"=>$json['riga']['geom']);
	
	
	echo json_encode(array("type"=>"FeatureCollection","features"=>$out));//var_dump($row);//echo json_encode($row);
}

getdata();