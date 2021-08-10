<?php
namespace CCM;
//use Respect\Validation\Validator as v;//https://respect-validation.readthedocs.io/en/1.1/rules/Numeric/

class Organizations
{
    private $db;
    private $oauth;
	private $shouldCheck;
	
    public function __construct($db)
    {
        $this->db = $db;
    }
	
	private function orgTypes($input) {
		$mapper = [
			'0'=>'user group',
			'1'=>'Wikimedia community',
			'2'=>'Wikimedia affiliate'
		];
		
		return (isset($mapper[$input]) ? $mapper[$input] : 'unknown');
	}

	public function setOauth($obj, $shouldCheck) {
		$this->oauth = $obj;
		$this->shouldCheck = $shouldCheck;
	}

	private function getCategories() {
		$sql = "SELECT id, title, category FROM items";
		
		$sqlData = $this->db->query($sql)->fetchAll("keyPairArr");

		return $sqlData;
	}
	
	public function getOrganizationData($id) {
        $data = $this->db->query("SELECT name, link, org_type, comment from organizations where id = ?",[$id])->fetch("assoc");
		
		if (!$data) {
			return ['status'=>'error','message'=>'no data'];
		}
		
		//$data['org_type'] = $this->orgTypes($data['org_type']);

        return ['status'=>'ok','data'=>$data];
	}
	
	public function getOverviewForOrganization($organizationID) {
		$this->categoryMap = $this->getCategories();

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

	private function updateOrg($id, $data) {
		$mapping = [
			'name' => $data['name'],
			'link' => $data['link'],
			'org_type' => $data['org_type'],
			'comment' => $data['comment']
		];
		
		$updSt = $this->makeUpdateStmt($mapping, 'organizations', ['id'=>$id],$this->db,true);
		
		if ($updSt->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}

		return ['status'=>'ok','message'=>'ok'];
	}
	
    public function makeUpdateStmt($fields, $table, $whereArray, $dbConnection = null, $execute = true) {
        $cols = [];
        $values = [];
        $cols_where = [];

        foreach($fields as $field => $val) {
            $cols[] = $field;
            $values[] = $val;
        }

        foreach($whereArray as $field => $val) {
            $cols_where[] = $field;
            $values[] = $val;
        }
        
        $formattedCols = implode(" = ?, ", $cols);
        $formattedColsWhere = implode(" = ? and ", $cols_where);

        $queryString = "update $table set $formattedCols = ? where $formattedColsWhere = ?";
        if ($execute && $dbConnection) {
            $update = $dbConnection->query($queryString, $values);
    
            return $update;
        }

        return ['query'=>$queryString,'values'=>$values];
    }


	public function getSQLQueryFieldString($fields) {
		$fieldsString = [];

		$fieldMap = [];

		foreach($fields as $field => $value) {
			$fieldsString[] = $field;
			$fieldMap[] = $value;
		}

		return ['fields'=>$fieldsString,'values'=>$fieldMap];
    }
    
	
	public function saveOrg($data) {
		$username = isset($data['username']) ? $data['username'] : null;

		/* if ( $this->shouldCheck && !$this->oauth->isAuthOK() ) {
			return ['status'=>'error','message'=>'not logged in'];
		} */
		
		$currTime = date("YmdHis");//todo: pēc UTC
		$theLink = isset($data['link']) ? $data['link'] : null;
		$comment = isset($data['comment']) ? $data['comment'] : null;
		$title = isset($data['title']) ? $data['title'] : null;
		$org_type = isset($data['type']) ? $data['type'] : null;
		$org_id = isset($data['org']) ? $data['org'] : null;
		
		if ($org_id) {
			return $this->updateOrg($org_id, [
				'name' => $title,
				'link' => $theLink,
				'org_type' => $org_type,
				'comment' => $comment
			]);
			//$insert = $this->db->query("UPDATE organizations set name, link, org_type) values (?, ?, ?)",[$title,$theLink,$org_type]);
		} else {
			$insert = $this->db->query("INSERT INTO organizations (name, link, org_type, comment) values (?, ?, ?, ?)",[$title,$theLink,$org_type,$comment]);
			if ($insert->affectedRows()<1) {
				return ['status'=>'error','message'=>'error'];
			}

			return ['status'=>'ok','message'=>'ok', 'orgId' => $insert->insertId()];
		}
	}
	
}