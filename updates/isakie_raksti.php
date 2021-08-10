<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('memory_limit', '-1');


require_once __DIR__.'/../php/oauth.php';
require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$oauth = new MW_OAuth('edgars','lv','wikipedia');

/* if ( !$oauth->isAuthOK() ) {
	echo 'Neesi ielogojies';
	return;
} */
/* $user_data = $oauth->getConsumerRights();
$username = $user_data->query->userinfo->name;

$usersOK = ['Edgars2007','Treisijs']; */

/* if (!in_array($username,$usersOK)) {
	echo 'Nav atļauts';
	return;
} */

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
        return json_decode($response, true);
    }
}

class IsakieRaksti extends SPARQLQueryDispatcher {
	private $sparqlEndpoint = 'https://query.wikidata.org/sparql';

	private $wikidataSPARQLResults;
	private $lvwikiDBResults;
	private $result;

	private $sparqlQueryString = <<< 'SPARQL'
		SELECT ?item ?articleLV ?article WHERE {
		values ?badges {wd:Q17437796  wd:Q17437798}  .
		?articleLV schema:about ?item; schema:isPartOf <https://lv.wikipedia.org/> .
		?article schema:about ?item;
				wikibase:badge ?badges .
		}
SPARQL;

	public function __construct() {
		parent::__construct($this->sparqlEndpoint);
	}

	private function dbQuery() {
		global $conn;
		//$fileConts = json_decode(file_get_contents('quarry-34761-untitled-run356685.json'),true)["rows"];
		$ex = $conn->query('select p.page_title, count(l.ll_lang), p.page_len
		from langlinks l
		join page p on p.page_id=l.ll_from and p.page_namespace=0
		group by l.ll_from
		having count(l.ll_lang)>99
		order by p.page_len asc')->fetchAll('num');
		return $ex;
	}

	private function doSparql() {
		//$this->query($this->sparqlQueryString);
		$fileConts = $this->query($this->sparqlQueryString)["results"]["bindings"];
		//$fileConts = json_decode(file_get_contents('query.json'),true)["results"]["bindings"];
		return $fileConts;
	}

	private function parseWikidata() {
		$intermediate = [];

		foreach($this->wikidataSPARQLResults as $sparqlOne) {
			$lvname = urldecode(str_replace('https://lv.wikipedia.org/wiki/','',$sparqlOne['articleLV']['value']));
			$lvname = str_replace(' ','_',$lvname);
			//$lvname = urldecode($sparqlOne['articleLV']['value'].str_replace('https://lv.wikipedia.org/wiki/','',$lvname)).str_replace(' ','_',$lvname);
			$insts = isset($sparqlOne['article']['value']) ? $sparqlOne['article']['value'] : "";
			preg_match('/https:\/\/([^\.]+)\.wikipedia\.org\/wiki\/.*/', $insts, $output_array);
			if (sizeof($output_array)>0 && strlen($output_array[1]) == 2) {
				$intermediate[$lvname][] = $output_array[1];
			}
		}

		$this->wikidataSPARQLResults = $intermediate;
	}

	private function parseResults() {
		
		$res = [];

		$counter = 1;
		foreach($this->lvwikiDBResults as $oneArticle) {
			$article = str_replace(' ','_',$oneArticle[0]);

			if ($counter == 201) {
				break;
			}
			if (array_key_exists($article, $this->wikidataSPARQLResults) == false) {
				continue;
			}

			//echo $article;
			//print_r($this->wikidataSPARQLResults[$article]);

			if (strpos($article, '_gads') !== false) {
				continue;
			}

			$wikidata = $this->wikidataSPARQLResults[$article];
			if (sizeof($wikidata)<1) {
				continue;
			}

			$numberofLngs = sizeof($wikidata);

			$title = str_replace('_',' ',$oneArticle[0]);
			$garums = $oneArticle[2];
			$iws = $oneArticle[1];

			$res[] = "|-\n| $counter. || [[$title]] || $garums || $numberofLngs";
			$counter += 1;
		}

		$this->result = $res;
	}
	
	public function main() {
		global $oauth;
		$this->lvwikiDBResults = $this->dbQuery();
		$this->wikidataSPARQLResults = $this->doSparql();
		$this->parseWikidata();
		$this->parseResults();

		$currdate = date('Y-m-d H:i:s');

		$header = "Upd: $currdate

{| class=\"wikitable sortable\"
|-
! Nr.p.k. !! Raksts !! Raksta garums !! FA/GA\n";
		$finalText = $header.implode("\n",$this->result)."\n|}";

		file_put_contents(__DIR__.'/aaa.txt',$finalText);
		$oauth->setPageText ( 'Dalībnieks:Edgars2007/Īsākie raksti' , $finalText );
		echo 'OK';
		//var_dump($this->wikidataSPARQLResults);
	}
}

$isakie = new IsakieRaksti();
$isakie->main();