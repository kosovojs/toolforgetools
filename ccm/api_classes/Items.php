<?php
namespace CCM;

class Items
{
	private $db;
	
	private $organizations = [];
	private $categoryMap = [];

    public function __construct($db)
    {
        $this->db = $db;
    }
	
	private function intToBool($value) {
		$toret = ($value === 1) ? true : false;
		
		//var_dump($toret);
		return $toret;
	}
	
    public function getDescriptions($id)
    {
        $descriptions = $this->db->query("SELECT * from items where id = ? and archived is null",[$id])->fetch("assoc");
		
		if (!$descriptions) {
			return false;
		}

        return $descriptions;
    }
	
    public function getRating($issue, $organization)
    {
		//var_dump([$issue,$organization]);
        $ratingData = $this->db->query("SELECT capacity, robustness, irrelevant, comment, id from ratings where item = ? and organization = ?",[$issue,$organization])->fetch("assoc");
		
		if (!$ratingData) {
			return [
				'irrelevant' => null,
				'capacity' => null,
				'robustness' => null,
				'comment' => ''
			];
		}
		
		if ($ratingData['irrelevant']) {
			$ratingData['irrelevant'] = $this->intToBool($ratingData['irrelevant']);
		} else {
			$ratingData['irrelevant'] = $this->intToBool(false);
		}
		
        return $ratingData;
    }
	
    public function getRatingHistory($ratingId)
    {
        $history = $this->db->query("SELECT capacity, robustness, irrelevant, user, add_time from rating_history where rating_id = ?",[$ratingId])->fetchAll("assoc");
		
		if (!$history) {
			return [];
		}

        return $history;
    }
	
	//mode - 'nākamais'/'random'
	//mode2 - vienīgi nenovērtētie/visi
	//,$mode2 = 'unrated'
	//mode2 - tad ir jāzin iepriekšējais item ID
	//getNextItem($args['org'],$args['curr'],$args['mode'],$args['mode2']);
	
	//item/next/{curr}/{org}/{mode}/{mode2}
	//curId - org - next - unrated
	public function getNextItem($organization,$currentId,$mode,$mode2) {
		$params = [];
		$modeSQL = '';
		$mode2SQL = '';
		$mode3SQL = '';
		$sqlWhere = '';
		
		$sql = "select id from items where  ";
		/*
		if ($mode2 == 'unrated') {
			$mode2SQL = " id not in (select item from ratings where organization = ?) ";
			$params[] = $organization;
		} else if ($mode2 == 'any') {
			$mode2SQL = "";
		}
		
		
		
		
		if ($mode == 'next') {
			$modeSQL = "";
			$modeSQL3 = " id > ? ";
			$params[] = $currentId;
		} else if ($mode == 'random') {
			$modeSQL = " ORDER BY RAND() ";
		}
		*/
		if ($mode == 'next' && $mode2 == 'unrated') {
			$sqlWhere = ' id > ? and  id not in (select item from ratings where organization = ?) ';
			$params[] = $currentId;
			$params[] = $organization;
		} else if ($mode == 'next' && $mode2 == 'any') {
			$sqlWhere = ' id > ? ';
			$params[] = $currentId;
		} else if ($mode == 'random' && $mode2 == 'unrated') {
			$sqlWhere = ' id not in (select item from ratings where organization = ?)  ORDER BY RAND()';
			$params[] = $organization;
		} else if ($mode == 'random' && $mode2 == 'any') {
			$sqlWhere = ' id not in (select item from ratings where organization = ?) ';
			$params[] = $organization;
		}
		
		$sqlQuery = "$sql $sqlWhere limit 1";
		
		//echo $sqlQuery;
		//var_dump($params);
		
		$item = $this->db->query($sqlQuery,$params)->fetch("assoc");
		
		return $item['id'];
	}
	
    public function getIssueData($issue, $organization)
    {
		$toReturn = [];
        $desc = $this->getDescriptions($issue);
		if ($desc) {
			$toReturn['desc'] = ['status'=>'ok','data'=>$desc];
		} else {
			$toReturn['desc'] = ['status'=>'error','message'=>'no data'];
		}
		
		
        $rating = $this->getRating($issue, $organization);
		if ($rating) {
			$toReturn['rating'] = ['status'=>'ok','data'=>$rating];
		} else {
			$toReturn['rating'] = ['status'=>'error','message'=>'no data'];
		}
		
		$itemId = array_key_exists('id',$rating) ? $rating['id'] : null;
		if ($itemId) {
			$history = $this->getRatingHistory($itemId);
			$toReturn['history'] = ['status'=>'ok','data'=>$history];
		} else {
			$toReturn['history'] = ['status'=>'ok','data'=>[]];
		}
		
		return $toReturn;
    }
	
	private function getOrgDetails() {
		$sql = "select id, name as title, link from organizations order by title";
		
		$sqlData = $this->db->query($sql)->fetchAll("keyPairArr");
		
		return $sqlData;
	}

	private function getCategories() {
		$sql = "SELECT id, title, category FROM items";
		
		$sqlData = $this->db->query($sql)->fetchAll("keyPairArr");

		return $sqlData;
	}

	private function getOverviewForOrganization($organizationID) {
		$sql = "select item, capacity, robustness, irrelevant from ratings where organization=?";
		$orgRatings = $this->db->query($sql,[$organizationID])->fetchAll("keyPairArr");
		
		$formattedRatings = [];

		foreach($this->categoryMap as $itemID => $itemMetadata) {
			$category = $itemMetadata['category'];

			$currRatings = isset($orgRatings[$itemID]) ? $orgRatings[$itemID] : ['capacity'=>null,'robustness'=>null,'irrelevant'=>null];

			$formattedRatings[$category][] = array_merge($currRatings, ['title'=> $itemMetadata['title'],'item'=> $itemID]);
		}

		return $formattedRatings;
	}
	
	public function getOverview() {
		$this->organizations = $this->getOrgDetails();
		$this->categoryMap = $this->getCategories();

		$finalData = [];

		foreach($this->organizations as $orgID => $ordData) {
			$finalData[] = [
				'details' => array_merge($ordData,['id'=>$orgID]),
				'ratings' => $this->getOverviewForOrganization($orgID)
			];
		}
		
		return $finalData;
	}
	
	public function getOverviewOLD() {
		$orgs = $this->getOrgDetails();
		
		$sql = "select organization, capacity, robustness, irrelevant, item from ratings";
		
		$sqlData = $this->db->query($sql)->fetchAll("assoc");
		
		$toret = [];
		
		foreach($sqlData as $row) {
			
			$currOrgData = [];
			
			$currOrgData['ratings'][] = ['capacity'=>$row['capacity'], 'robustness'=>$row['robustness'], 'irrelevant'=>$row['irrelevant']];
			
			//$currOrgData['title'] = 'Wikimedians of Latvia';
			//$currOrgData['id'] = $row['organization'];
			
			$toret[$row['organization']]['ratings'][$row['item']] = ['capacity'=>$row['capacity'], 'robustness'=>$row['robustness'], 'irrelevant'=>$row['irrelevant']];
			if (!array_key_exists('details',$toret[$row['organization']])) {
				$organization = $orgs[$row['organization']];
				
				$toret[$row['organization']]['details'] = ['title'=>$organization['name'],'link'=>$organization['link'],'id'=>$row['organization']];
			}
			
		}
		
		$toret2 = [];
		
		foreach($toret as $key => $value) {
			$toret2[] = $value;
		}
			
		
		return $toret2;
	}
	
	public function saveItem($data) {
		$currTime = date("YmdHis");//todo: pēc UTC
		
		$title = isset($data['title']) ? $data['title'] : null;
		$category = isset($data['category']) ? $data['category'] : null;
		$description = isset($data['description']) ? $data['description'] : null;
		$relevant = isset($data['relevant']) ? $data['relevant'] : null;
		$desc_none = isset($data['desc_none']) ? $data['desc_none'] : null;
		$desc_low = isset($data['desc_low']) ? $data['desc_low'] : null;
		$desc_med = isset($data['desc_med']) ? $data['desc_med'] : null;
		$desc_high = isset($data['desc_high']) ? $data['desc_high'] : null;
		$examples = isset($data['examples']) ? $data['examples'] : null;
		$resources = isset($data['resources']) ? $data['resources'] : null;
		$archived = isset($data['archived']) ? $data['archived'] : null;
		
		$insert = $this->db->query("INSERT INTO items (title, category, description, relevant, desc_none, desc_low, desc_med, desc_high, examples, resources, archived) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$title,$category,$description,$relevant,$desc_none,$desc_low ,$desc_med,$desc_high,$examples,$resources,$archived]);
		
		if ($insert->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}
		
		return ['status'=>'ok','message'=>'ok'];
	}
	
	
}