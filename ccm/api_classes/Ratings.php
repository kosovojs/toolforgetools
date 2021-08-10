<?php
namespace CCM;
use Respect\Validation\Validator as v;//https://respect-validation.readthedocs.io/en/1.1/rules/Numeric/

class Ratings
{
    private $db;
	private $item;
	private $organization;
	private $oauth;
	private $shouldCheck;

    public function __construct($db, $item, $organization)
    {
        $this->db = $db;
        $this->item = $item;
        $this->organization = $organization;
    }
	
	//vai man šo vajag?
	private function boolToInt($value) {
		$toret = ($value === true) ? 1 : (($value === false) ? 0 : null);
		
		//var_dump($toret);
		return $toret;
	}

	public function setOauth($obj, $shouldCheck) {
		$this->oauth = $obj;
		$this->shouldCheck = $shouldCheck;
	}
	
	private function isRatingSet() {
		$sql = $this->db->query("SELECT id from ratings where item = ? and organization = ?",[$this->item,$this->organization])->fetch("assoc");
		
		if (!$sql) {
			return false;
		}

        return $sql['id'];
	}
	/*
	{ capacity: "2", robustness: "4" }
	irrelevant - true
	*/
	
	private function insertRating($data) {
		$currTime = date("YmdHis");//todo: pēc UTC
		
		/*
		$capacity = isset($data['capacity']) ? $data['capacity'] : null;
		if (isset($capacity) && v::numeric()->validate($capacity)) {
			$capacity = (int)$capacity;
		} else {
			$capacity = null;
		}
		
		$robustness = isset($data['robustness']) ? $data['robustness'] : null;
		if (isset($robustness) && v::numeric()->validate($robustness)) {
			$robustness = (int)$robustness;
		} else {
			$robustness = null;
		}
		
		$irrelevant = isset($data['irrelevant']) ? $data['irrelevant'] : null;
		if (isset($irrelevant)) {//validate if bool
			$irrelevant = $this->boolToInt($irrelevant);
		} else {
			$irrelevant = null;
		}
		*/
		$parsed = $this->parseParams($data);
		
		$insert = $this->db->query("INSERT INTO ratings (item, organization, capacity, robustness, irrelevant, comment) values (?, ?, ?, ?, ?, ?)",[$this->item,$this->organization,$parsed['capacity'],$parsed['robustness'],$parsed['irrelevant'],$parsed['comment']]);
		
		if ($insert->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}
		
		$itemId = $insert->insertId();
		
		$username = isset($data['username']) ? $data['username'] : null;//"Test user"

		$insert = $this->db->query("INSERT INTO rating_history (rating_id, capacity, robustness, irrelevant, user, add_time, comment) values (?, ?, ?, ?, ?, ?, ?)",[$itemId,$parsed['capacity'],$parsed['robustness'],$parsed['irrelevant'],$username,$currTime,$parsed['comment']]);
		
		if ($insert->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}
		
		return ['status'=>'ok','message'=>'ok'];
	}
	
	private function parseParams($data) {
		//ja dati neatbilst 'validate' - error return
		$toReturn = [];
		
		$capacity = isset($data['capacity']) ? $data['capacity'] : null;
		if (isset($capacity) && v::numeric()->validate($capacity)) {
			$capacity = (int)$capacity;
		} else {
			$capacity = null;
		}
		
		$robustness = isset($data['robustness']) ? $data['robustness'] : null;
		if (isset($robustness) && v::numeric()->validate($robustness)) {
			$robustness = (int)$robustness;
		} else {
			$robustness = null;
		}

		$comment = isset($data['comment']) ? $data['comment'] : null;
		
		//var_dump($data['irrelevant']);
		$irrelevant = isset($data['irrelevant']) ? $data['irrelevant'] : null;
		//var_dump($irrelevant);
		
		if ($irrelevant !== null) {
			//echo 'one1';
			$irrelevant = $this->boolToInt($irrelevant);
		} else {
			//echo 'one2';
			$irrelevant = null;
		}
		//echo $irrelevant;
		
		if ($irrelevant === 1) {
			$robustness = 0;
			$capacity = 0;
		}
		
		return [
			'irrelevant'=>$irrelevant,
			'robustness'=>$robustness,
			'capacity'=>$capacity,
			'comment'=>$comment
		];
	}
	
	private function updateRating($itemId,$data) {
		$currTime = date("YmdHis");//todo: pēc UTC
		
		$parsed = $this->parseParams($data);
		//echo 'parsed: ';
		//var_dump($parsed);
		
		$forSaving = [
			'columns'=>[],
			'values'=>[]
		];
		
		if ($parsed["capacity"] !== null) {
			if ($parsed["capacity"] == 0) {
				$parsed["capacity"] = null;
			}
			$forSaving['columns'][] = 'capacity';
			$forSaving['values'][] = $parsed["capacity"];
		}

		if ($parsed["robustness"] !== null) {
			if ($parsed["robustness"] == 0) {
				$parsed["robustness"] = null;
			}
			$forSaving['columns'][] = 'robustness';
			$forSaving['values'][] = $parsed["robustness"];
		}

		if ($parsed["irrelevant"] !== null) {
			if ($parsed["irrelevant"] == 0) {
				$parsed["irrelevant"] = null;
			}
			$forSaving['columns'][] = 'irrelevant';
			$forSaving['values'][] = $parsed["irrelevant"];
		}

		if ($parsed["comment"] !== null) {
			if ($parsed["comment"] == '') {
				$parsed["comment"] = null;
			}
			$forSaving['columns'][] = 'comment';
			$forSaving['values'][] = $parsed["comment"];
		}
		/*
		if (array_key_exists("capacity",$data)) {
			$forSaving['columns'][] = 'capacity';
			$forSaving['values'][] = (int)$data["capacity"];
		}

		if (array_key_exists("robustness",$data)) {
			$forSaving['columns'][] = 'robustness';
			$forSaving['values'][] = (int)$data["robustness"];
		}

		if (array_key_exists("irrelevant",$data)) {
			$forSaving['columns'][] = 'irrelevant';
			$forSaving['values'][] = (int)$data["irrelevant"];
		}
		*/
		$forSaving['values'][] = $itemId;//adding issue id to query

		$columnsToUpdate = implode(" = ?, ", $forSaving['columns']) . " = ?";
		$columnValues = $forSaving['values'];
		
		//echo "UPDATE ratings SET $columnsToUpdate where id = ?";
		
		$update = $this->db->query("UPDATE ratings SET $columnsToUpdate where id = ?", $columnValues);

		if ($update->affectedRows()<1) {
			return ['status'=>'error','message'=>'dati netika mainīti'];
		}

		$username = isset($data['username']) ? $data['username'] : null;//"Test user"
		
		$insert = $this->db->query("INSERT INTO rating_history (rating_id, capacity, robustness, irrelevant, user, add_time, comment) values (?, ?, ?, ?, ?, ?, ?)",[$itemId,$parsed["capacity"],$parsed["robustness"],$parsed["irrelevant"],$username,$currTime,$parsed["comment"]]);
		
		if ($insert->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}
		
		return ['status'=>'ok','message'=>'ok'];
	}
	
    public function ratingUpdate($params)
    {
/* 		if ( $this->shouldCheck && !$this->oauth->isAuthOK() ) {
			return ['status'=>'error','message'=>'not logged in'];
		} */
		
		//var_dump($params);
		$itemId = $this->isRatingSet();
		
		if ($itemId) {
			return $this->updateRating($itemId,$params);
		} else {
			return $this->insertRating($params);
		}
		
		//$this->updateRatingHistory($data);
    }
}