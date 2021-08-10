<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../php/ToolforgeCommon.php';

$tfc = new ToolforgeCommon('edgars');
$tfc->tool_user_name = 'edgars';

$connWiki = $tfc->openDBwiki('lvwiki',true, true);

$conn = $tfc->openDBtool('meta_p');

$timeStamp = $conn->query("select upd_timestamp from cee2020 where year='2020'")->fetch('assoc')['upd_timestamp'];

//$timeStamp = '20200320051819';

$newPageTpl = "SELECT rc_timestamp, rc_title, page.page_id
FROM recentchanges
JOIN page ON page.page_title=recentchanges.rc_title AND page_is_redirect=0 AND page_namespace=0
WHERE rc_namespace=0 and rc_type=1 AND rc_timestamp>'$timeStamp'";

function getRows($data) {
	$ret = [];

	while($row = $data->fetch_assoc()){
		$ret[] = $row;
	}

	return $ret;
}

function getRows_Mapping($data, $mainKey) {
	$ret = [];

	while($row = $data->fetch_assoc()){
		$ret[$row[$mainKey]] = $row;
		//unset($row[$mainKey]);
	}

	return $ret;
}


$articles = getRows($tfc->getSQL($connWiki, $newPageTpl));

$pageIDs = [];

foreach ($articles as $article) {
	$pageIDs[] = $article['page_id'];
}

if (sizeof($pageIDs) < 1) {
	echo json_encode([]);
	exit();
	//exit('No pages');
}

$whereInPageIds = implode(", ", $pageIDs);

$categoriesTpl = "SELECT cl.cl_from, GROUP_CONCAT(cl_to SEPARATOR '|') cats
FROM categorylinks cl
where cl.cl_from IN ($whereInPageIds)
GROUP BY cl.cl_from";


$templatesPageTpl = "SELECT tl_from, GROUP_CONCAT(tl_title SEPARATOR '|') tpls
FROM templatelinks
WHERE tl_namespace = 10 and tl_from IN ($whereInPageIds)
GROUP BY tl_from";

$categories = getRows_Mapping($tfc->getSQL($connWiki, $categoriesTpl), 'cl_from');

$templates = getRows_Mapping($tfc->getSQL($connWiki, $templatesPageTpl), 'tl_from');

$pagesWithTpls = "SELECT p.page_id
from page p
where p.page_namespace IN(0)  AND p.page_is_redirect=0
and exists (SELECT * FROM templatelinks,page pt WHERE MOD(p.page_namespace,2)=0 AND pt.page_title=p.page_title
			AND pt.page_namespace=p.page_namespace+1
AND tl_from=pt.page_id AND tl_namespace=10 AND tl_title = 'CEE_Spring_2020')";

$tplAlready = $tfc->getSQL($connWiki, $pagesWithTpls);

$tplAlreadyList = [];

foreach($tplAlready as $rr) {
	$tplAlreadyList[] = $rr['page_id'];
}

//var_dump($categories);
//var_dump($templates);

$lastArr = [];

foreach($articles as $article) {
	$id = $article['page_id'];
	$stamp = $article['rc_timestamp'];
	$title = $article['rc_title'];
	$cats = $categories[$id]['cats'] ?? '';
	$tpls = $categories[$id]['tpls'] ?? '';

	if (in_array($id, $tplAlreadyList)) {
		continue;
	}

	$lastArr[] = [
		'title'=>$title, 'timestamp' =>$stamp, 'cats'=>str_replace("_"," ",$cats), 'tpls'=>str_replace("_"," ",$tpls)//, 'already' => in_array($id, $tplAlreadyList) ? true : false
	];
}

header("Content-type: application/json");
echo json_encode(['timestamp' => $timeStamp, 'data' => $lastArr]);

/*



$connWiki = $tfc->openDBwiki('lvwiki',true, true);



$mainSQL = "select actor_name as `user`, rev_timestamp as `timest`, orig.page_title as `title`
from revision
join page orig on orig.page_id=rev_page and orig.page_is_redirect=0 and orig.page_namespace=0
join actor on actor_id=rev_actor
where rev_timestamp>'$maxTimeUTC' and rev_parent_id=0
order by rev_timestamp";

$otherSQL = "select actor_name as `user`, rc_timestamp as `timest`, rc_title as `title`
from change_tag ch
join change_tag_def def on def.ctd_id=ch.ct_tag_id
join recentchanges rc on rc.rc_id = ch.ct_rc_id
join actor on actor_id=rc_actor
where ch.ct_tag_id=3#mw-removed-redirect
#where rc_user=347
and rc_title in (select page_title from page where page.page_title=rc_title and page_namespace=0 and page_is_redirect=0)
and rc_timestamp>'$maxTimeUTC'
order by rc_timestamp desc
limit 500";


$otherDataSQL = $this->tfc->getSQL($this->connWiki, $otherSQL);

$mainData = [];
$otherData = [];
 */
