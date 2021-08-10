<?php
require_once __DIR__.'/lib/oauth.php';
require_once __DIR__.'/lib/ToolforgeCommon.php';

date_default_timezone_set('Europe/Riga');

class NPP
{
    private $oauth = null;
    private $tfc = null;
    private $conn = null;
    private $connWiki = null;
    
    private $requestParams = [];
    
    private $allowedUsers = ['Biafra','Edgars2007'];

    public function __construct()
    {
        $this->tfc = new ToolforgeCommon('edgars');
        $this->oauth = new MW_OAuth('edgars', 'lv', 'wikipedia');
        $this->tfc->tool_user_name = 'edgars';
        $this->conn = $this->tfc->openDBtool('npp_p');
		
		if (PHP_SAPI !== 'cli') {
			$this->getAllRequestParameters();
		}
    }
    
    private function getAllRequestParameters()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->requestParams = $_GET;
        } else {
            $this->requestParams = json_decode(file_get_contents('php://input'), true);
        }
    }

    public function getRequest($key, $default = "")
    {
        //if ( isset ( $this->prefilled_requests[$key] ) ) return $this->prefilled_requests[$key] ;
        if (isset($this->requestParams[$key])) {
            return str_replace("\'", "'", $this->requestParams[$key]) ;
        }
        return $default ;
    }

    public function authorize()
    {
        $this->oauth->doAuthorizationRedirect();
        exit(0) ;
    }

    public function userInfo()
    {
        return json_encode($this->oauth->getConsumerRights());
    }

    public function logout()
    {
        return json_encode($this->oauth->logout());
    }

    public function commentPage()
    {
        $result = $this->conn->query("SELECT id, title, date, user, comment from diskusijas where comment is not NULL and reviewed is NULL")->fetchAll('assoc');
        
        return json_encode($result);
    }

    public function mainList()
    {
        $result = $this->conn->query("SELECT id, title, date, user, comment from diskusijas where comment is NULL and reviewed is NULL")->fetchAll('assoc');
        
        return json_encode($result);
    }
    
    public function search($searchPhrase)
    {
        $result = $this->conn->query("SELECT id,title from diskusijas WHERE (comment is NULL and reviewed is NULL) and title like '%$searchPhrase%' limit 15")->fetchAll('assoc');
        
        return json_encode($result);
    }
    
    private function checkAuth()
    {
        /* if (!$this->oauth->isAuthOK()) {
            return false;
        } */
        
        return true;
    }
    
    private function getUserName()
    {
        return $this->oauth->getConsumerRights()->query->userinfo->name;
    }
    
    public function archive($articleID)
    {
        if ($articleID == '' || $articleID == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        if (!$this->checkAuth()) {
            return json_encode(array('status' => 'error','message'=> 'not logged in'));
        }
        
        $username = $this->getUserName();
        
        //if (in_array($username, $this->allowedUsers)) {
            $cur_time = date("YmdHis");
            $stmt = $this->conn->query("UPDATE diskusijas SET reviewed=?, reviewed_time=? WHERE id=?", [$username,$cur_time,$articleID]);
            
            if ($stmt->affectedRows() < 1) {
                return json_encode(array('status' => 'error','message'=> 'failed'));
            } else {
                return json_encode(array('status' => 'success','message'=> 'Everything is ok'));
            }
        //} else {
        //    return json_encode(array('status' => 'error','message'=> 'not allowed'));
        //}
    }
    
    public function getArticle($lastID, $mode)
    {
        if ($lastID == '' || $lastID == null || $mode == '' || $mode == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        
        if ($mode == 'this') {
            $comm = "SELECT id,title from diskusijas WHERE id=".$lastID." limit 1";
        } else {
            $comm = "SELECT id,title from diskusijas WHERE (comment is NULL and reviewed is NULL) and id>";
            
            if ($mode=="rnd") {
                $comm .= "0 ORDER BY RAND() limit 1";
            } elseif ($mode=="next") {
                $comm .= $lastID." limit 1";
            }
        }
        
        $row = $this->conn->query($comm)->fetch('assoc');
        
        $numofres = $this->conn->query("SELECT count(*) as cnt from diskusijas WHERE (comment is NULL and reviewed is NULL)")->fetch('assoc')['cnt'];
        $latest = $this->conn->query("SELECT max(date) as m_date from diskusijas")->fetch('assoc')['m_date'];

        //fixme: if no results
        return json_encode([
            'article' => $row,
            'results' => $numofres,
            'last_article' => $latest
        ]);
    }
    
    public function saveArticle($articleID)
    {
        if ($articleID == '' || $articleID == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        if (!$this->checkAuth()) {
            return json_encode(array('status' => 'error','message'=> 'not logged in'));
        }
        
        $username = $this->getUserName();
        
        //if (in_array($username, $this->allowedUsers)) {
            $cur_time = date("YmdHis");
            $stmt = $this->conn->query("UPDATE diskusijas SET reviewed=?, reviewed_time=?  WHERE id=?", [$username,$cur_time,$articleID]);
            
            if ($stmt->affectedRows() < 1) {
                return json_encode(array('status' => 'error','message'=> 'failed'));
            } else {
                return json_encode(array('status' => 'success','message'=> 'Everything is ok'));
            }
       /// } else {
        //    return json_encode(array('status' => 'error','message'=> 'not allowed'));
        //}
    }
    
    public function setArticleToDeletion($articleName, $days, $reason)
    {
        if ($articleName == '' || $articleName == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        if (!$this->checkAuth()) {
            return json_encode(array('status' => 'error','message'=> 'not logged in'));
		}
		
		$username = $this->getUserName();
		
        $ch = null;
        $articleContent = $this->oauth->doApiQuery([
            'format' => 'json',
            'action' => 'query',
            'prop' => 'revisions',
            'titles' => $articleName,
            'rvprop' => 'content',
            'rvslots' => 'main',
            'rvlimit' => '1',
            'redirects' => '1',
            'rvdir' => 'older'
        ], $ch);
        
        $articleContent= json_decode(json_encode($articleContent), true);
        $articleID = array_keys($articleContent['query']['pages'])[0];
        $articleData = $articleContent['query']['pages'][$articleID];
        $resolvedArticleName = $articleData['title'];
		$articleText = $articleData['revisions'][0]['slots']['main']['*'];

		$term = date('d.m.Y', strtotime($days.' days'));
		$timestamp = date('YmdHis');

		$tpls = "{{Dzēst|".$reason."; termiņš: ".$term."|lietotājs=".$username."|laiks=".$timestamp."}}\n{{termiņš|".$days."|".$term."}}";

		$newContent = $tpls."\n".$articleText;

		$res = $this->oauth->setPageText($resolvedArticleName, $newContent, 'raksts izvirzīts uz dzēšanu');

		if ($res) {
			return json_encode(array('status' => 'success','message'=> 'Everything is ok'));
		}

		return json_encode(array('status' => 'error','message'=> 'failed'));
    }
    
    public function putForLater($articleID, $comment = null)
    {
        if ($articleID == '' || $articleID == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        if (!$this->checkAuth()) {
            return json_encode(array('status' => 'error','message'=> 'not logged in'));
        }
        
        $username = $this->getUserName();
        
        //if (in_array($username, $this->allowedUsers)) {
            $cur_time = date("YmdHis");

            $stmt = $this->conn->query("UPDATE diskusijas SET comment=?, comment_time=?, commenter=? WHERE id=?", [$comment,$cur_time,$username,$articleID]);
            
            if ($stmt->affectedRows() < 1) {
                return json_encode(array('status' => 'error','message'=> 'failed'));
            } else {
                return json_encode(array('status' => 'success','message'=> 'Everything is ok'));
            }
        //} else {
        //    return json_encode(array('status' => 'error','message'=> 'not allowed'));
        //}
    }

    private function timecard()
    {
        $theQuery = "SELECT DAYOFWEEK(m.reviewed_time) AS `y`, (ROUND(HOUR(m.reviewed_time)/2) * 2) AS `x`, COUNT(id) AS `value`
		from diskusijas m
		WHERE m.reviewed_time is not null
		GROUP BY y, x";

        $totals = $this->conn->query($theQuery)->fetchAll('assoc');
        
        // Scale the radii: get the max, then scale each radius.
        // This looks inefficient, but there's a max of 72 elements in this array.
        $max = 0;
        foreach ($totals as $total) {
            $max = max($max, $total['value']);
        }
        foreach ($totals as &$total) {
            $total['value'] = round($total['value'] / $max * 100);
        }

        // Fill in zeros for timeslots that have no values.
        $sortedTotals = [];
        $index = 0;
        $sortedIndex = 0;
        foreach (range(1, 7) as $day) {
            foreach (range(0, 24, 2) as $hour) {
                if (isset($totals[$index]) && (int)$totals[$index]['x'] === $hour) {
                    $sortedTotals[$sortedIndex] = $totals[$index];
                    $index++;
                } else {
                    $sortedTotals[$sortedIndex] = [
                        'y' => $day,
                        'x' => $hour,
                        'value' => 0,
                    ];
                }
                $sortedIndex++;
            }
        }
        //
        //echo json_encode($sortedTotals);

        $dataset = [
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(171, 212, 235, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(178, 223, 138, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(251, 154, 153, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(253, 191, 111, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(202, 178, 214, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(207, 182, 128, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(141, 211, 199, 1)",
        'data'=>[]
        ]
        ,
        [
        'label'=>"Total",
        'backgroundColor'=>"rgba(252, 205, 229, 1)",
        'data'=>[]
        ]
        ];


        //echo json_encode($dataset);

        foreach ($sortedTotals as $total) {
            //$total = {"y":"1","x":"0","value":3}
            //entry.y = Math.abs(8 - entry.y);
            //timeCardDatasets[0].data.push(entry);

            $finalKey = ((int)$total['y'] - 1);//norādu indeksu $dataset masīvā, kur šo pievienot
            $total['y'] = abs(8 - (int)$total['y']);

            $dataset[$finalKey]['data'][] = $total;
        }


        return $dataset;
	}
	
	private function setPeriodDates($period) {
		if ($period === '30d') {
			return [
				'start' => date('Ymd', strtotime('-30 days')),
				'end' => date('Ymd')
			];
		}

		if ($period === '7d') {
			return [
				'start' => date('Ymd', strtotime('-7 days')),
				'end' => date('Ymd')
			];
		}

		if ($period === 'currY') {
			return [
				'start' => date('Y')."0101",
				'end' => date('Ymd')
			];
		}

		//if ($period === 'all') {
			return [
				'start' => "20170101",
				'end' => date('Ymd')
			];
		//}
	}
    
    public function graphdata($period)
    {
		$periodDates = $this->setPeriodDates($period);
		$start = $periodDates['start'];
		$end = $periodDates['end'];
        $row = $this->conn->query("select left(timest,10), articles from stats where timest between ? and ?", [$start, $end])->fetchAll('num');
        $result = array('dates'=>[],'values'=>[]);
        
        foreach ($row as $indrow) {
            $result['dates'][] = $indrow[0];
            $result['values'][] = $indrow[1];
        }
        
        $row2 = $this->conn->query("select left(reviewed_time,10) as newtime, count(*) from diskusijas where reviewed_time between ? and ? and reviewed_time is not NULL group by newtime", [$start, $end])->fetchAll('num');
        $result2 = array('dates'=>[],'values'=>[]);
        
        foreach ($row2 as $indrow2) {
            $result2['dates'][] = $indrow2[0];
            $result2['values'][] = $indrow2[1];
        }
        
        $row3 = $this->conn->query("select left(date,10) as newtime, count(*) from diskusijas where reviewed_time is NULL and comment is NULL group by newtime")->fetchAll('num');
        $result3 = array('dates'=>[],'values'=>[]);
        
        foreach ($row3 as $indrow3) {
            $result3['dates'][] = $indrow3[0];
            $result3['values'][] = $indrow3[1];
        }
        
        echo json_encode(array($result,$result2,$result3));//,$this->timecard()
	}

	public function addIWFromWD($wdItem, $title) {
        if ($wdItem == '' || $wdItem == null || $title == '' || $title == null) {
            return json_encode(array('status' => 'error','message'=> 'no data'));
        }
        if (!$this->checkAuth()) {
            return json_encode(array('status' => 'error','message'=> 'not logged in'));
		}
		/* 
        $ch = null;
        $articleContent = $this->oauth->doApiQuery([
            'format' => 'json',
            'action' => 'query',
            'prop' => 'pageprops',
            'titles' => $articleName,
            'ppprop' => 'wikibase_item'
		], $ch,'',5,-1, "https://$language.wikipedia.org/w/api.php");
		
        $articleContent= json_decode(json_encode($articleContent), true);
        $articleID = array_keys($articleContent['query']['pages'])[0];
        $articleData = $articleContent['query']['pages'][$articleID];
        $resolvedArticleName = $articleData['title'];
		$wdItem = $articleData['pageprops']['wikibase_item'];
 */
		$resultStatus = $this->oauth->setSitelink($wdItem , 'lvwiki' , $title, '');

		if ($resultStatus) {
			return json_encode(array('status' => 'success','message'=> 'Everything is ok'));
		}
		
		return json_encode(array('status' => 'error','message'=> 'Not successfull'));
	}
	
    public function test($lvTitle, $language, $articleName)
    {
		/* $language = 'en';
		$articleName = 'Opposition (politics)';
		$lvTitle = 'Opozīcija (politika)'; */
	}
	
	public function importData() {
		$this->connWiki = $this->tfc->openDBwiki('lvwiki',true, true);
		
		$this->updateArticleCount();
		$maxTime = $this->conn->query("select DATE_FORMAT(max(date),'%Y%m%d%H%i%s') as maxd from diskusijas")->fetch('assoc')['maxd'];
		$maxTimeUTC = $this->convertDatetime($maxTime, 'utc');

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

		$mainDataSQL = $this->tfc->getSQL($this->connWiki, $mainSQL);
		$otherDataSQL = $this->tfc->getSQL($this->connWiki, $otherSQL);

		$mainData = [];
		$otherData = [];
		
		while($row = $mainDataSQL->fetch_assoc()){
			$mainData[] = $row;
		}

		while($row = $otherDataSQL->fetch_assoc()){
			$otherData[] = $row;
		}
		
		$this->addDataToDB($mainData, null);
		$this->addDataToDB($otherData, 1);

		$articlesKopsumma = sizeof($mainData) + sizeof($otherData);

		return "Tika importēti $articlesKopsumma raksti";
	}

	private function addDataToDB($data, $addType = null) {
		$sqlTpl = 'INSERT INTO `main` (`title`, `date`, `user`, `addition_type`) VALUES (?, ?, ?, ?)';

		$rows = [];

		foreach($data as $row) {
			$user = $row['user'];
			$timest = $this->convertDatetime($row['timest'], 'Europe/Riga');
			$pageTitle = $row['title'];

			$rows[] = [
				str_replace('_',' ',$pageTitle),
				$timest,
				str_replace('_',' ',$user),
				$addType
			];
		}

		$this->conn->atomicQuery($sqlTpl, $rows);
	}

	private function updateArticleCount() {
		$cnt = $this->conn->query('SELECT count(*) as cnt from diskusijas WHERE (comment is NULL and reviewed is NULL)')->fetch('assoc')['cnt'];

		$this->conn->query('INSERT INTO `stats` (`timest`, `articles`) VALUES (?, ?)',[date('YmdHis'),$cnt]);
	}

	private function convertDatetime($input, $to) {
		if ($to == 'utc') {
			$given = new DateTime($input);
			$given->setTimezone(new DateTimeZone("UTC"));
			return $given->format("YmdHis");
		}
		
		if ($to == 'Europe/Riga') {
			$given = new \DateTime($input, new DateTimeZone('UTC'));
			$given->setTimezone(new DateTimeZone("Europe/Riga"));
			return $given->format("YmdHis");
		}
	}
    
}
