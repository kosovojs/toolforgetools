<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header ( "Access-Control-Allow-Headers: *") ;

require_once __DIR__.'/../php/ToolforgeCommon.php';

$LANG = 'uk';

$CAMPAIGN = "$LANG-P569-20210718";

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
                    'Accept: application/sparql-results+json',
                    'User-Agent: WDQS-example PHP/' . PHP_VERSION, // TODO adjust this; see https://w.wiki/CX6
                ],
            ],
        ];
        $context = stream_context_create($opts);

        $url = $this->endpointUrl . '?query=' . urlencode($sparqlQuery);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }
}

$endpointUrl = 'https://query.wikidata.org/sparql';
$sparqlQueryString = "
select ?item ?lv_title where {
  ?item wdt:P31 wd:Q5 .
  ?lv_title schema:about ?item; schema:isPartOf <https://$LANG.wikipedia.org/> .
  filter not exists {?item wdt:P569 [] .}
}";

$queryDispatcher = new SPARQLQueryDispatcher($endpointUrl);
$queryResult = $queryDispatcher->query($sparqlQueryString)['results']['bindings'];

$items = [];

foreach($queryResult as $entry) {
	$wd = str_replace('http://www.wikidata.org/entity/','', $entry['item']['value']);
	
	$lvname = urldecode(str_replace('https://'.$LANG.'.wikipedia.org/wiki/','',$entry['lv_title']['value']));
	$lvname = str_replace(' ','_',$lvname);

	$items[] = [$wd, $lvname, $LANG, $CAMPAIGN];
}

var_dump(sizeof($items));

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$conn = $tfc->openDBtool('meta_p');

date_default_timezone_set('Europe/Riga');

$sqlTpl = "insert into datestoadd (wikidata, article, wiki, category) values (?, ?, ?, ?)";

$conn->atomicQuery($sqlTpl, $items);

echo 'done';