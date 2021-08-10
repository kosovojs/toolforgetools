<?php

namespace Missing;

class Database {
	private $limit = 800;
	private $oneChunkSize = 50;
	private $langTo = 'lv';
	private $langFrom = 'ru';
	private $langMake = 'en';
	private $dbCache = [];

	private $wikiDBConn = null;
	private $toolDBConn = null;

	private $pageInfoToReturn = [
		'enTitle','wikidata','frInfoboxes'
	];
	
	private	$db_servers = [
		'fast' => '.web.db.svc.eqiad.wmflabs' ,
		'slow' => '.analytics.db.svc.eqiad.wmflabs' ,
		'old' => '.labsdb'
	] ;

	private $tasks = [
		/*
		['lang'=>'fr','name'=>'Monuments','group'=>'other','mode'=>'template','modeSpecificSource'=>'Infobox Monument'],
		['lang'=>'fr','name'=>'Gratte-ciel','group'=>'other','mode'=>'template','modeSpecificSource'=>'Infobox Gratte-ciel'],
		['lang'=>'ru','name'=>'Geoobjekti','group'=>'other','mode'=>'template','modeSpecificSource'=>'Геокар'],
		['lang'=>'ru','name'=>'Apskates vietas','group'=>'other','mode'=>'template','modeSpecificSource'=>'Достопримечательность'],
		*/
		/*
		['lang'=>'fr','name'=>'Stokholma','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Stockholm'],
		['lang'=>'fr','name'=>'Malta','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Malte'],
		['lang'=>'fr','name'=>'Berlīne','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Berlin'],
		['lang'=>'fr','name'=>'DĀR','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Afrique_du_Sud'],
		['lang'=>'fr','name'=>'Japāna','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Japon'],
		['lang'=>'fr','name'=>'Irāka','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Irak'],
		['lang'=>'fr','name'=>'Āfrika','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Afrique'],
		['lang'=>'fr','name'=>'Senā Ēģipte','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Égypte antique'],
		['lang'=>'fr','name'=>'Indija','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Monde_indien'],
		*/
		
		//['lang'=>'fr','name'=>'Senā pasaule','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Monde antique'],

		/////////////////////,
		/*
		['lang'=>'fr','name'=>'Parīze','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Paris'],
		['lang'=>'fr','name'=>'Roma','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Rome'],
		['lang'=>'fr','name'=>'Londona','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Londres'],
		['lang'=>'fr','name'=>'Jeruzaleme','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Jérusalem'],
		['lang'=>'fr','name'=>'Maskava','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Moscou'],
		['lang'=>'fr','name'=>'Sanktpēterburga','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Saint-Pétersbourg'],
		['lang'=>'fr','name'=>'Otrais pasaules karš','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Seconde Guerre mondiale'],
		['lang'=>'fr','name'=>'Pirmais pasaules karš','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Première Guerre mondiale']
		*/
		/*
		['lang'=>'en','name'=>'FA (en)','group'=>'other','mode'=>'enCategory','modeSpecificSource'=>'Featured articles'],
		['lang'=>'en','name'=>'GA (en)','group'=>'other','mode'=>'enCategory','modeSpecificSource'=>'Good articles'],
		['lang'=>'en','name'=>'RTT','group'=>'other','mode'=>'enCategory','modeSpecificSource'=>'RTT'],
		*/
		
		//['lang'=>'fr','name'=>'Irāna','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Iran et monde iranien'],
	];
	/*
	#{'group':'other','name':'Reliģiskas ēkas','template':'Культовое сооружение','lang':conn_ru},
	*/
	
	public function __construct() {
		$this->toolDBConn = $this->openDBtool('mis_lists_p');
	}

	private function setConnectionForThisQuery($lang) {
		if (array_key_exists($lang, $this->dbCache)) {
			$this->wikiDBConn = $this->dbCache[$lang];
			return;
		}

		$conn = $this->openWikiDB($lang);
		$this->dbCache[$lang] = $conn;
		$this->wikiDBConn = $conn;
	}

	private function checkDBConn($dbConn) {

	}

	private function normalizeNameForSQL($input) {
		$input = str_replace(' ','_',$input);
		return $input;
	}

	private function prepareSQLInfo($arr) {
		$sqlConstruct = [];
		foreach($arr as $colName => $colSQL) {
			$sqlConstruct[] = " ($colSQL) as $colName";
		}
		
		if (sizeof($sqlConstruct) > 0) {
			return ", ".implode(', ',$sqlConstruct);
		}

		return '';
	}

	private function prepareInfoForReturn($pageId, $options = []) {
		$arr = [];

		$finalOps = sizeof($options) > 0 ? $options : $this->pageInfoToReturn;

		if ($this->langFrom !== 'en' && in_array('enTitle',$finalOps)) {
			$arr['en'] = "select m2.ll_title from langlinks m2 where m2.ll_from=$pageId and m2.ll_lang='en'";//bet vispār jāievieto $this->langMake
		}

		if ($this->langFrom === 'fr' && in_array('frInfoboxes',$finalOps)) {
			$arr['infs'] = "SELECT GROUP_CONCAT(tl_title SEPARATOR '|') FROM templatelinks WHERE tl_title like 'Infobox%' and tl_namespace = 10 and tl_from=$pageId";
		}

		if (in_array('wikidata',$finalOps)) {
			$arr['wikidata'] = "select pp.pp_value from page_props pp where pp.pp_page=$pageId and pp_propname='wikibase_item'";
		}

		return $arr;
	}

	private function makeFinalQuery($subquery = '', $subqueryParams = []) {
		$sqlTmp = "select ll_from, count(l.ll_lang) as langs
		from langlinks l
		where not exists (select * from langlinks m where m.ll_from=l.ll_from and m.ll_lang=?)
			and exists ($subquery)
		group by l.ll_from
		order by count(l.ll_lang) desc
		limit ?";

		$sqlParams = [];

		$sqlParams[] = $this->langTo;
		$sqlParams = array_merge($sqlParams,$subqueryParams);
		$sqlParams[] = $this->limit;

		return ['sql'=>$sqlTmp, 'params'=>$sqlParams];
	}

	private function getPageInfo($pageIDs) {
		$optionsForSQL = $this->prepareInfoForReturn('p.page_id');
		$optionsSQL =  $this->prepareSQLInfo($optionsForSQL);

		$clause = $this->wikiDBConn->whereIn($pageIDs);

		$sqlConstruct = "select page_id as pageId, page_title as title

		$optionsSQL

		from page p
		where p.page_namespace=0 and p.page_is_redirect=0 and p.page_id in ($clause)";

		$res = $this->wikiDBConn->query($sqlConstruct, $pageIDs)->fetchAll('keyPairArr');

		return $res;
	}

	private function mergeIwCountWithPageInfo($iwArray, $infoArray) {
		$finalArr = [];

		foreach($iwArray as $pageId => $iws) {
			if (array_key_exists($pageId, $infoArray) === false) {
				var_dump($pageId);
				echo 'PROBLEM!';
				continue;
			}
			
			$finalArr[] = array_merge(['iws' => $iws],$infoArray[$pageId]);
		}

		return $finalArr;
	}

	private function saveToFile($arr) {
		$jsonStr = json_encode($arr, JSON_UNESCAPED_UNICODE);

		file_put_contents('file-'.date ( 'YmdHis' ).'.txt',$jsonStr);
	}

	private function saveToDB($name, $group, $data) {
		$sql_insert = 'INSERT INTO `entries` (`name`, `group_name`, `jsondata`,`last_upd`) VALUES (?, ?, ?, ?)';
		$sql_update = 'UPDATE `entries` SET jsondata=?, last_upd=? where group_name=? and name=?';

		$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

		$isAlreadyInDB_sql = $this->toolDBConn->query('select id from entries where group_name=? and name=?',[$group, $name])->fetch('assoc')['id'];
		if ($isAlreadyInDB_sql === null) {
			$this->toolDBConn->query($sql_insert,[$name, $group,$jsonData,date('YmdHis')]);
		} else {
			$this->toolDBConn->query($sql_update,[$jsonData,date('YmdHis'), $group,$name]);
		}
	}

	private function makeAllActions($sql, $params) {
		
		$initialData = $this->wikiDBConn->query($sql, $params)->fetchAll('keyPair');
		$pageIds = array_keys($initialData);

		$chunks = array_chunk($pageIds, $this->oneChunkSize);

		$allResults = [];

		foreach($chunks as $chunk) {
			$currRes = $this->getPageInfo($chunk);
			$allResults = $allResults + $currRes;//array_merge($allResults, $currRes);
		}
		
		$mergedArray = $this->mergeIwCountWithPageInfo($initialData, $allResults);
		
		$order = $this->prepareForToolDB($mergedArray);
		//$this->saveToFile($order);

		return $order;
		/*
		$data = json_decode($this->fakeRes,true);
		var_dump($order);
		*/
	}

	private function prepareForToolDB($data) {
		$res = [];
		foreach($data as $entry) {
			$title = $this->langFrom == 'en' ? $entry['title'] : $entry['en'];
			if (!$title) {
				continue;
			}

			$res[] = [
				str_replace('_',' ',$title),
				$entry['iws'],
				$entry['wikidata']
			];
		}

		usort($res, function($a, $b) {
			return $a[1] < $b[1];
		});

		return $res;
	}

	private function initSettingsForQuery($lang) {
		$this->langFrom = $lang;
		$this->langMake = $lang;
		if ($lang !== 'en') {
			$this->langMake = 'en';
		}

		$this->setConnectionForThisQuery($lang);
	}

	public function frPortal($portal,$name,$group, $lang) {
		$this->initSettingsForQuery($lang);
		
		$sqlSubquery = 'select l.ll_from from categorylinks cla where cla.cl_type="page" and l.ll_from=cla.cl_from and cla.cl_to=?';

		$fullPortal = 'Portail:'.$portal.'/Articles_liés';
		
		$subqueryParams = [$this->normalizeNameForSQL($fullPortal)];

		$finalSQLData = $this->makeFinalQuery($sqlSubquery, $subqueryParams);

		$ordered = $this->makeAllActions($finalSQLData['sql'], $finalSQLData['params']);
		
		$this->saveToDB($name, $group, $ordered);
	}

	public function fromCategory($category,$name,$group, $lang) {
		$this->initSettingsForQuery($lang);
		
		$sqlSubquery = 'select l.ll_from from categorylinks cla where cla.cl_type="page" and l.ll_from=cla.cl_from and cla.cl_to=?';

		$fullPortal = str_replace(' ','_',$category);
		
		$subqueryParams = [$this->normalizeNameForSQL($fullPortal)];

		$finalSQLData = $this->makeFinalQuery($sqlSubquery, $subqueryParams);

		$ordered = $this->makeAllActions($finalSQLData['sql'], $finalSQLData['params']);
		
		$this->saveToDB($name, $group, $ordered);
	}

	public function enProjectTemplate($wikiprojectTemplate,$name,$group, $lang) {
		/*
		$this->initSettingsForQuery($lang);
		
		$sqlSubquery = 'select l.ll_from from categorylinks cla where cla.cl_type="page" and l.ll_from=cla.cl_from and cla.cl_to=?';

		$fullPortal = 'Portail:'.$portal.'/Articles_liés';
		
		$subqueryParams = [$this->normalizeNameForSQL($fullPortal)];

		$finalSQLData = $this->makeFinalQuery($sqlSubquery, $subqueryParams);

		$ordered = $this->makeAllActions($finalSQLData['sql'], $finalSQLData['params']);
		
		$this->saveToDB($name, $group, $ordered);
		*/
	}

	public function forTemplate($template,$name,$group, $lang) {
		$this->initSettingsForQuery($lang);
		
		$sqlSubquery = 'select l.ll_from from templatelinks tl where l.ll_from=tl.tl_from and tl_namespace=10 and tl.tl_from_namespace=0 and tl.tl_title=?';
		$subqueryParams = [$this->normalizeNameForSQL($template)];

		$finalSQLData = $this->makeFinalQuery($sqlSubquery, $subqueryParams);

		$ordered = $this->makeAllActions($finalSQLData['sql'], $finalSQLData['params']);
		
		$this->saveToDB($name, $group, $ordered);
	}

	public function handleRUWIKISports() {
		$allCategoriesSQL = "SELECT name FROM entries WHERE group_name=? AND last_upd<20190713";
		
		$allCategories = $this->toolDBConn->query($allCategoriesSQL,['rusports'])->fetchAll('col');

		foreach($allCategories as $category) {
			echo "{$category}<br>";
			$fullCategory = "$category по алфавиту";
			$this->fromCategory($fullCategory,$category,'rusports','ru');
			echo 'Done<br>';
		}
		echo 'OK!';
	}

	public function handleENWIKIInfoboxes() {
		//todo: vēl vajag apstrādāt veidņu nosaukumus, vai ir aktuālie
		/*
		
		$allCategoriesSQL = "SELECT name FROM entries WHERE group_name=? AND last_upd<20190713 limit 1";
		
		$allCategories = $this->toolDBConn->query($allCategoriesSQL,['eninfobox'])->fetchAll('col');

		foreach($allCategories as $category) {
			echo "{$category}<br>";
			$fullCategory = "$category по алфавиту";
			$this->forTemplate($fullCategory,$category,'eninfobox','en');
			echo 'Done<br>';
		}
		echo 'OK!';
		*/
	}

	public function executeTask() {
		foreach($this->tasks as $task) {
			echo "{$task['name']}<br>";
			$mode = $task['mode'];
			if ($mode == 'template') {
				$this->forTemplate($task['modeSpecificSource'],$task['name'],$task['group'], $task['lang']);
			} else if ($mode === 'frPortal') {
				$this->frPortal($task['modeSpecificSource'],$task['name'],$task['group'], $task['lang']);
			} else if ($mode === 'enProjectTemplate') {
				$this->enProjectTemplate($task['modeSpecificSource'],$task['name'],$task['group'], $task['lang']);
			} else if ($mode === 'enCategory') {
				$this->fromCategory($task['modeSpecificSource'],$task['name'],$task['group'], $task['lang']);
			}
			
			echo 'Done<br>';
		}
		echo 'OK!';
	}

	public function handleWAM2019() {
		$task = ['lang'=>'fr','name'=>'WAM2019','group'=>'other','mode'=>'frPortal','modeSpecificSource'=>'Asie'];

		$this->frPortal($task['modeSpecificSource'],$task['name'],$task['group'], $task['lang']);
	}
	
	//////////////////////////////////////////////////////
	
	public /*string*/ $toolname ;
	public $prefilled_requests = [] ;
	public /*string*/ $tool_user_name = 'edgars' ; # force different DB user name
	public $use_db_cache = true ;
	public $db;

	private $browser_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:57.0) Gecko/20100101 Firefox/57.0" ;
	
	private $cookiejar ; # For doPostRequest
	private/*string*/  $mysql_user , $mysql_password ;
	private $db_cache = [] ;

	
	function getDBname ( $language , $project ) {
		$ret = $language ;
		if ( $language == 'commons' ) $ret = 'commonswiki_p' ;
		elseif ( $language == 'wikidata' || $project == 'wikidata' ) $ret = 'wikidatawiki_p' ;
		elseif ( $language == 'mediawiki' || $project == 'mediawiki' ) $ret = 'mediawikiwiki_p' ;
		elseif ( $language == 'species' || $project == 'wikimedia' ) $ret = 'specieswiki_p' ;
		elseif ( $language == 'meta' && $project == 'wikimedia' ) $ret = 'metawiki_p' ;
		elseif ( $project == 'wikipedia' ) $ret .= 'wiki_p' ;
		elseif ( $project == 'wikisource' ) $ret .= 'wikisource_p' ;
		elseif ( $project == 'wiktionary' ) $ret .= 'wiktionary_p' ;
		elseif ( $project == 'wikibooks' ) $ret .= 'wikibooks_p' ;
		elseif ( $project == 'wikinews' ) $ret .= 'wikinews_p' ;
		elseif ( $project == 'wikiversity' ) $ret .= 'wikiversity_p' ;
		elseif ( $project == 'wikivoyage' ) $ret .= 'wikivoyage_p' ;
		elseif ( $project == 'wikiquote' ) $ret .= 'wikiquote_p' ;
		elseif ( $project == 'wikispecies' ) $ret = 'specieswiki_p' ;
		elseif ( $language == 'meta' ) $ret .= 'metawiki_p' ;
		else if ( $project == 'wikimedia' ) $ret .= $language.$project."_p" ;
		else die ( "Cannot construct database name for $language.$project - aborting." ) ;
		return $ret ;
	}
	

	private function getDBpassword () /*:string*/ {
		if ( isset ( $this->tool_user_name ) and $this->tool_user_name != '' ) $user = $this->tool_user_name ;
		else $user = str_replace ( 'tools.' , '' , get_current_user() ) ;
		$passwordfile = '/data/project/' . $user . '/replica.my.cnf' ;
		if ( $user == 'magnus' ) $passwordfile = '/home/' . $user . '/replica.my.cnf' ; // Command-line usage
		$config = parse_ini_file( $passwordfile );
		if ( isset( $config['user'] ) ) {
			$this->mysql_user = $config['user'];
		}
		if ( isset( $config['password'] ) ) {
			$this->mysql_password = $config['password'];
		}
	}

	public function openDBtool ( $dbname = '' , $server = '' , $force_user = '' , $persistent = false ) {
		$this->getDBpassword() ;
		if ( $dbname == '' ) $dbname = '_main' ;
		else $dbname = "__$dbname" ;
		if ( $force_user == '' ) $dbname = $this->mysql_user.$dbname;
		else $dbname = $force_user.$dbname;
		if ( $server == '' ) $server = "tools.labsdb" ; //"tools-db" ;
		if ( $persistent ) $server = "p:$server" ;
		$db = new \SimpleMySQLi($server, $this->mysql_user, $this->mysql_password, $dbname, "utf8mb4", "assoc");//$db = @new mysqli($server, $this->mysql_user, $this->mysql_password , $dbname);
		assert ( $db->connect_errno == 0 , 'Unable to connect to database [' . $db->connect_error . ']' ) ;
		return $db ;
	}
	
	private function openWikiDB($lang) {
		$slow_queries = true;

		$this->getDBpassword() ;
		$dbname = $this->getDBname ( $lang , 'wikipedia' ) ;
		$server = substr( $dbname, 0, -2 ) . ( $slow_queries ? $this->db_servers['slow'] : $this->db_servers['fast'] ) ;
		
		$db = new \SimpleMySQLi($server, $this->mysql_user, $this->mysql_password, $dbname, "utf8mb4", "assoc");
		return $db;
	}

	
	public function getSQL ( &$db , &$sql , $max_tries = 3 , $message = '' ) {
		while ( $max_tries > 0 ) {
			$pinging = 10 ;
			while ( !@$db->ping() ) {
				if ( $pinging < 0 ) break ;
	//			print "RECONNECTING..." ;
				sleep ( 1 ) ;
				@$db->connect() ;
				$pinging-- ;
			}
			if($ret = @$db->query($sql)) return $ret ;
			$max_tries-- ;
		}
		$e = new \Exception;
		var_dump($e->getTraceAsString());
		die ( 'There was an error running the query [' . $db->error . '/' . $db->errno . ']'."\n$sql\n$message\n" ) ;
	}

	public function findSubcats ( &$db , $root , &$subcats , $depth = -1 ) {
		$check = [] ;
		$c = [] ;
		foreach ( $root AS $r ) {
			if ( isset ( $subcats[$r] ) ) continue ;
			$subcats[$r] = $db->real_escape_string ( $r ) ;
			$c[] = $db->real_escape_string ( $r ) ;
		}
		if ( count ( $c ) == 0 ) return ;
		if ( $depth == 0 ) return ;
		$sql = "SELECT DISTINCT page_title FROM page,categorylinks WHERE page_id=cl_from AND cl_to IN ('" . implode ( "','" , $c ) . "') AND cl_type='subcat'" ;
		$result = $this->getSQL ( $db , $sql , 2 ) ;
		while($row = $result->fetch_assoc()){
			if ( isset ( $subcats[$row['page_title']] ) ) continue ;
			$check[] = $row['page_title'] ;
		}
		if ( count ( $check ) == 0 ) return ;
		$this->findSubcats ( $db , $check , $subcats , $depth - 1 ) ;
	}

	public function getPagesInCategory ( &$db , $category , $depth = 0 , $namespace = 0 , $no_redirects = false ) {
		$depth *= 1 ;
		$namespace *= 1 ;
		$ret = [] ;
		$cats = [] ;
		$category = str_replace ( ' ' , '_' , $category ) ;
		$this->findSubcats ( $db , [$category] , $cats , $depth ) ;

		if ( $namespace == 14 ) return $cats ; // Faster, and includes root category

		$namespace *= 1 ;
		$sql = "SELECT DISTINCT page_title FROM page,categorylinks WHERE cl_from=page_id AND page_namespace=$namespace AND cl_to IN ('" . implode("','",$cats) . "')" ;
		if ( $no_redirects ) $sql .= " AND page_is_redirect=0" ;

		$result = $this->getSQL ( $db , $sql , 2 ) ;
		while($o = $result->fetch_object()){
			$ret[$o->page_title] = $o->page_title ;
		}
		return $ret ;
	}
}