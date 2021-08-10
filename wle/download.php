<?php
//error_reporting( E_ALL );
//ini_set( 'display_errors', '1' );
//header("Access-Control-Allow-Origin: *");
//header('Content-Type: application/json');
header("Content-disposition: attachment; filename=wle_dati.txt");
header("Content-type: text/csv");

class SPARQLQueryDispatcher
{
    private $endpointUrl;

    public function __construct(string $endpointUrl)
    {
        $this->endpointUrl = $endpointUrl;
    }

    public function query(string $sparqlQuery): array
    {

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: application/sparql-results+json'
                ],
            ],
        ];
        $context = stream_context_create($opts);

        $url = $this->endpointUrl . '?query=' . urlencode($sparqlQuery);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true)["results"]["bindings"];
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
	
	$label = $entry['itemLabel']['value'];
	$iadt = isset($entry['iadt']) ? $entry['iadt']['value'] : 'NAV';
	
	$hasImage = isset($entry['image']) ? 'Ir' : 'Nav';
	

	$finalProps = [$label, $iadt, $hasImage, $coords[1],$coords[0]];

	$toRET_tmp[] = implode("\t",$finalProps);
}

$header = implode("\t",["Nosaukums","ĪADT","Ir attēls","Lat","Lon"]);

echo $header ."\n".implode("\n",$toRET_tmp);//json_encode($toRET_tmp);