<?php

require_once __DIR__ . '/api.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

//$db_wd = $tfc -> openDB ( 'lv' , 'wikipedia' ) ;
// $db = $tfc -> openDBwiki ( 'lv.wikipedia' ) ;
/* 
$sql = 'select page_title from page limit 5';
$pagesCat = $tfc->getSQL($db,$sql,0);
var_dump($pagesCat);
exit;

$dfdfsdfd = $tfc -> getPagesInCategory ( $db , "Zirnekļcilvēka filmu sērija" , 0 );
var_dump($dfdfsdfd);

 */
class DB
{
    public $pdo;

    public function __construct($db, $username = null, $password = null, $host = '127.0.0.1', $port = 5432, $options = [])
    {
        $default_options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($default_options, $options);
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public function run($sql, $args = null)
    {
        if (!$args) {
            return $this->pdo->query($sql);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}

function getPlaceholder($items) {
	return str_repeat('?, ', count($items) - 1) . '?';
}

class ArticlesToCategorize {
	private $wikis = ["aawiki","abwiki","acewiki","adywiki","afwiki","akwiki","alswiki","altwiki","amwiki","anwiki","angwiki","arwiki","arcwiki","arywiki","arzwiki","aswiki","astwiki","atjwiki","avwiki","avkwiki","awawiki","aywiki","azwiki","azbwiki","bawiki","banwiki","barwiki","bat_smgwiki","bclwiki","bewiki","be_x_oldwiki","bgwiki","bhwiki","biwiki","bjnwiki","bmwiki","bnwiki","bowiki","bpywiki","brwiki","bswiki","bugwiki","bxrwiki","cawiki","cbk_zamwiki","cdowiki","cewiki","cebwiki","chwiki","chowiki","chrwiki","chywiki","ckbwiki","cowiki","crwiki","crhwiki","cswiki","csbwiki","cuwiki","cvwiki","cywiki","dawiki","dagwiki","dewiki","dinwiki","diqwiki","dsbwiki","dtywiki","dvwiki","dzwiki","eewiki","elwiki","emlwiki","enwiki","eowiki","eswiki","etwiki","euwiki","extwiki","fawiki","ffwiki","fiwiki","fiu_vrowiki","fjwiki","fowiki","frwiki","frpwiki","frrwiki","furwiki","fywiki","gawiki","gagwiki","ganwiki","gcrwiki","gdwiki","glwiki","glkwiki","gnwiki","gomwiki","gorwiki","gotwiki","guwiki","gvwiki","hawiki","hakwiki","hawwiki","hewiki","hiwiki","hifwiki","howiki","hrwiki","hsbwiki","htwiki","huwiki","hywiki","hywwiki","hzwiki","iawiki","idwiki","iewiki","igwiki","iiwiki","ikwiki","ilowiki","inhwiki","iowiki","iswiki","itwiki","iuwiki","jawiki","jamwiki","jbowiki","jvwiki","kawiki","kaawiki","kabwiki","kbdwiki","kbpwiki","kgwiki","kiwiki","kjwiki","kkwiki","klwiki","kmwiki","knwiki","kowiki","koiwiki","krwiki","krcwiki","kswiki","kshwiki","kuwiki","kvwiki","kwwiki","kywiki","lawiki","ladwiki","lbwiki","lbewiki","lezwiki","lfnwiki","lgwiki","liwiki","lijwiki","lldwiki","lmowiki","lnwiki","lowiki","lrcwiki","ltwiki","ltgwiki","lvwiki","madwiki","maiwiki","map_bmswiki","mdfwiki","mgwiki","mhwiki","mhrwiki","miwiki","minwiki","mkwiki","mlwiki","mnwiki","mniwiki","mnwwiki","mrwiki","mrjwiki","mswiki","mtwiki","muswiki","mwlwiki","mywiki","myvwiki","mznwiki","nawiki","nahwiki","napwiki","ndswiki","nds_nlwiki","newiki","newwiki","ngwiki","niawiki","nlwiki","nnwiki","nowiki","novwiki","nqowiki","nrmwiki","nsowiki","nvwiki","nywiki","ocwiki","olowiki","omwiki","orwiki","oswiki","pawiki","pagwiki","pamwiki","papwiki","pcdwiki","pdcwiki","pflwiki","piwiki","pihwiki","plwiki","pmswiki","pnbwiki","pntwiki","pswiki","ptwiki","quwiki","rmwiki","rmywiki","rnwiki","rowiki","roa_rupwiki","roa_tarawiki","ruwiki","ruewiki","rwwiki","sawiki","sahwiki","satwiki","scwiki","scnwiki","scowiki","sdwiki","sewiki","sgwiki","shwiki","shiwiki","shnwiki","siwiki","simplewiki","skwiki","skrwiki","slwiki","smwiki","smnwiki","snwiki","sowiki","sqwiki","srwiki","srnwiki","sswiki","stwiki","stqwiki","suwiki","svwiki","swwiki","szlwiki","szywiki","tawiki","taywiki","tcywiki","tewiki","tetwiki","tgwiki","thwiki","tiwiki","tkwiki","tlwiki","tnwiki","towiki","tpiwiki","trwiki","trvwiki","tswiki","ttwiki","tumwiki","twwiki","tywiki","tyvwiki","udmwiki","ugwiki","ukwiki","urwiki","uzwiki","vewiki","vecwiki","vepwiki","viwiki","vlswiki","vowiki","wawiki","warwiki","wowiki","wuuwiki","xalwiki","xhwiki","xmfwiki","yiwiki","yowiki","zawiki","zeawiki","zhwiki","zh_classicalwiki","zh_min_nanwiki","zh_yuewiki","zuwiki"];

	function getAllSubcats($root , &$subcats , $depth = -1 ) {
		$check = array() ;
		$c = array() ;
		foreach ( $root AS $r ) {
			if ( in_array ( $r, $subcats ) ) continue ;
			$subcats[] =  $r  ;
			$c[] =  $r  ;
		}
		if ( count ( $c ) == 0 ) return ;
		if ( $depth == 0 ) return ;
	
		$placeholders = getPlaceholder($c);
	
		$result = $this->sourceConn->run("SELECT DISTINCT page_title FROM page,categorylinks WHERE page_id=cl_from AND cl_to IN ($placeholders) AND cl_type='subcat'", $c)->fetchAll();
	
		foreach($result as $row) {
			if ( in_array ( $row['page_title'], $subcats ) ) continue ;
			$check[] = $row['page_title'] ;
		}
		
		if ( count ( $check ) == 0 ) return ;
		$this->getAllSubcats ( $check , $subcats , $depth - 1 ) ;
	}

	function getPagesInCategory ( $category , $depth = 0, $targetWiki = 'lvwiki' , $namespace = 0 , $no_redirects = false ) {
		$depth *= 1 ;
		if ($depth < 0 || $depth > 10) {
			return [];
		}
		$namespace *= 1 ;
		$ret = array() ;
		$cats = array() ;
		$category = str_replace ( ' ' , '_' , $category ) ;
		$this->getAllSubcats( array($category) , $cats , $depth ) ;
	
		if ( $namespace == 14 ) return $cats ; // Faster, and includes root category
	
		$namespace *= 1 ;
		
		$placeholders = getPlaceholder($cats);
		
		$sql = "SELECT m2.ll_title as target_lang_article
		FROM page
		join categorylinks on cl_from=page_id
		join langlinks m2 on m2.ll_from=page.page_id and m2.ll_lang=?
		where page_namespace=0 AND cl_to IN ($placeholders)" ;
	
		$res = $this->sourceConn->run($sql, array_merge(...[[str_replace('wiki', '', $targetWiki)], $cats]))->fetchAll();
	
		$retArr = [];
	
		foreach($res as $row) {
			$retArr[] = $row['target_lang_article'];
		}
		
		return $retArr;
	}

	function getPagesInNegativeCategory ( $category , $depth = 0 , $namespace = 0 , $no_redirects = false ) {
		if (empty($category)) {
			return [];
		}
		if ($depth < 0 || $depth > 10) {
			return [];
		}
		$depth *= 1 ;
		$namespace *= 1 ;
		$ret = array() ;
		$cats = array() ;
		$category = str_replace ( ' ' , '_' , $category ) ;
		$this->getAllSubcats( array($category) , $cats , $depth ) ;
	
		if ( $namespace == 14 ) return $cats ; // Faster, and includes root category
	
		$namespace *= 1 ;
		
		$placeholders = getPlaceholder($cats);
		
		$sql = "SELECT page.page_title
		FROM page
		join categorylinks on cl_from=page_id
		where page_namespace=0 AND cl_to IN ($placeholders)" ;
	
		$res = $this->sourceConn->run($sql, $cats)->fetchAll();
	
		$retArr = [];
	
		foreach($res as $row) {
			$retArr[] =str_replace ('_' , ' ' ,  $row['page_title']);
		}
		
		return $retArr;
	}
	
	public function handle($sourceWiki, $sourceCat, $sourceCatDepth, $targetWiki, $targetNegativeCat = null, $targetNegativeCatDepth = 0) {
		if (!in_array($sourceWiki, $this->wikis)) {
			return [];
		}
		if (!in_array($targetWiki, $this->wikis)) {
			return [];
		}
		$passwordfile = '/data/project/edgars/replica.my.cnf' ;
		$config = parse_ini_file( $passwordfile );
		if ( isset( $config['user'] ) ) {
			$mysql_user = $config['user'];
		}
		if ( isset( $config['password'] ) ) {
			$mysql_password = $config['password'];
		}

		$this->sourceConn = new DB("{$sourceWiki}_p", $mysql_user, $mysql_password, "{$sourceWiki}.web.db.svc.wikimedia.cloud");


		$articlesForTargetWiki = $this->getPagesInCategory($sourceCat, $sourceCatDepth, $targetWiki);

		if (empty($articlesForTargetWiki)) {
			return [];
		}
		
		$this->sourceConn = new DB("{$targetWiki}_p", $mysql_user, $mysql_password, "{$targetWiki}.web.db.svc.wikimedia.cloud");

		$negativeArticlesTargetWiki = $this->getPagesInNegativeCategory($targetNegativeCat, $targetNegativeCatDepth);

		$finalArticles = array_values(array_diff($articlesForTargetWiki, $negativeArticlesTargetWiki));
		
		return $finalArticles;
	}
}

function parseSitematrix() {
	$resp = json_decode(file_get_contents('https://meta.wikimedia.org/w/api.php?action=sitematrix&format=json'), true);

	$data = $resp['sitematrix'];

	$ret = [];

	foreach($data  as $idx => $lang) {
		if (!is_numeric($idx)) {
			continue;
		}

		foreach($lang['site']  as $site) {
			if ($site['code'] !== 'wiki') {
				continue;
			}

			$ret[] = $site['dbname'];
		}
	}

	file_put_contents(__DIR__ . '/../../sites.json', json_encode($ret));
	
}

//parseSitematrix();

/* $res = (new ArticlesToCategorize())->handle('ruwiki', 'Родившиеся в Дюссельдорфе', '0', 'lvwiki');
var_dump($res);

$res = (new ArticlesToCategorize())->handle('ruwiki', 'Родившиеся в Дюссельдорфе', '0', 'lvwiki', 'Ziemeļreinā-Vestfālenē dzimušie', 0);
var_dump($res); */
/* 
$res = (new ArticlesToCategorize())->handle('ruwiki', 'Родившиеся в Северном Рейне-Вестфалии', '1', 'lvwiki', 'Vācijā dzimušie', 2);
var_dump($res);


function testing() {
	var_dump(array_diff(['a', 'b', 'c'], ['a', 'b', 'c']));
	var_dump(array_diff(['a', 'b', 'c'], ['d']));
	var_dump(array_diff(['a', 'b', 'c'], ['a']));
} */
//testing();

$sourceWiki = $_GET['source_wiki'];
$sourceCat = $_GET['source_cat'];
$sourceCatDepth = $_GET['source_cat_depth'] ?? 0;
$targetWiki = $_GET['target_wiki'];
$targetNegativeCat = $_GET['target_wiki_cat'] ?? null;
$targetNegativeCatDepth = $_GET['target_wiki_cat_depth'] ?? 0;

if (empty($sourceWiki) || empty($sourceCat) || empty($targetWiki)) {
	echo json_encode([]);
	exit();
}

$res = (new ArticlesToCategorize())->handle($sourceWiki, $sourceCat, $sourceCatDepth, $targetWiki, $targetNegativeCat, $targetNegativeCatDepth);

echo json_encode($res);
