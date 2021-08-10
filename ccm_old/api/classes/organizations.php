<?php
//use Respect\Validation\Validator as v;//https://respect-validation.readthedocs.io/en/1.1/rules/Numeric/

class Organizations
{
    private $db;
	
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
	
	public function getOrganizationData($id) {
        $data = $this->db->query("SELECT name, link, org_type from organizations where id = ?",[$id])->fetch("assoc");
		
		if (!$data) {
			return ['status'=>'error','message'=>'no data'];
		}
		
		$data['org_type'] = $this->orgTypes($data['org_type']);

        return ['status'=>'ok','data'=>$data];
	}
	
	
	public function saveOrg($data) {
		$currTime = date("YmdHis");//todo: pēc UTC
		$theLink = isset($data['link']) ? $data['link'] : null;
		$title = $data['title'];
		$org_type = $data['type'];
		
		
		$insert = $this->db->query("INSERT INTO organizations (name, link, org_type) values (?, ?, ?)",[$title,$theLink,$org_type]);
		
		if ($insert->affectedRows()<1) {
			return ['status'=>'error','message'=>'error'];
		}
		
		return ['status'=>'ok','message'=>'ok'];
	}
	
}