<?php
/* error_reporting( E_ALL );
ini_set( 'display_errors', '1' ); */
//header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

/*
include_once __DIR__.'/data.php';

$arrayToRET = $arrayToRET['results']['bindings'];
*/
//echo sizeof($arrayToRET);


class SPARQLQueryDispatcher
{
    private $endpointUrl;

    public function __construct(string $endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
    }

    public function query(string $sparqlQuery): array
    {
		$url = $this->endpointUrl . '?query=' . urlencode($sparqlQuery);
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, ['Accept: application/sparql-results+json']);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'tools.wmflabs.org/edgars/wle/');
		$query = curl_exec($curl_handle);
		curl_close($curl_handle);
		
        return json_decode($query, true)["results"]["bindings"];
    }
}

$endpointUrl = 'https://query.wikidata.org/sparql';
$sparqlQueryString = <<< 'SPARQL'
select ?item ?itemLabel ?coords ?image ?comcat ?web ?sitelink ?sastavno1 ?iadt {
  ?item wdt:P17 wd:Q211 .
  values ?itemtype {wd:Q11876497 wd:Q28055306 wd:Q28055278 wd:Q28054706 wd:Q28055269 wd:Q29549240 }
  ?item wdt:P31 ?itemtype;
        wdt:P625 ?coords .
  optional { ?item wdt:P18 ?image }
  optional { ?item wdt:P373 ?comcat }
  optional { ?item wdt:P856 ?web }
  optional { ?item wdt:P4029 ?iadt }
  optional { ?item wdt:P527 ?sastavno. ?sastavno1 schema:about ?sastavno; schema:isPartOf <https://lv.wikipedia.org/> }
  
  optional {?sitelink schema:about ?item; schema:isPartOf <https://lv.wikipedia.org/>}
  SERVICE wikibase:label { bd:serviceParam wikibase:language "lv,en". }
}
SPARQL;

$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);
$queryResult = $queryDispatcher->query($sparqlQueryString);

$toRET_tmp = [];

foreach($queryResult as $entry) {
	$coords = str_replace(')','',str_replace('Point(','',$entry['coords']['value']));
	$coords = explode(" ",$coords);
	$coords = [floatval($coords[0]),floatval($coords[1])];
	//["item","itemLabel","coords","image","comcat","web","sitelink","sastavno1"]

	$wdItem = $entry['item']['value'];//str_replace('http://www.wikidata.org/entity/','',$entry['item']['value']);
	$sitelink = isset($entry['sitelink']) ? $entry['sitelink']['value'] : null;
	$iadt = isset($entry['iadt']) ? $entry['iadt']['value'] : null;

	if (isset($entry['iadt']) == false) {
		//var_dump($entry);
		//echo '<br>';
	}
	$label = $entry['itemLabel']['value'];
	$image = isset($entry['image']) ? str_replace('http://commons.wikimedia.org/wiki/Special:FilePath/','',$entry['image']['value']) : null;

	$groupTOAdd = isset($entry['image']) ? 'with' : 'wo';

	$props = [
		'coords'=>$coords,
		'comcat'=>isset($entry['comcat']) ? $entry['comcat']['value'] : null,
		'web'=>isset($entry['web']) ? $entry['web']['value'] : null,
		'sastavno1'=>isset($entry['sastavno1']) ? $entry['sastavno1']['value'] : null,
		'item'=>$wdItem,
		'lvwiki'=>$sitelink,
		'iadt'=>$iadt,
		'label'=>$label,
		'image'=>$image
	];

	$finalProps = array("type" => "Feature","geometry"=> array("type"=> "Point", "coordinates"=> $coords),"properties"=>$props);

	$toRET_tmp[$groupTOAdd][] = $finalProps;
}

$toRET = [
	'with' => array("type"=> "FeatureCollection", "features" => $toRET_tmp['with']),
	'wo' => array("type"=> "FeatureCollection", "features" => $toRET_tmp['wo']),
];

echo json_encode($toRET);