<?php

interface ITravel {
	public function getTravels();
}

class Travel implements ITravel
{
	public const URL = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels";
	private $travels = [];

	public function __construct() {
		$this->initTravels();
	}
	
	private function initTravels() {
		$ch = curl_init(self::URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			       
		$this->travels = json_decode(curl_exec($ch), true) ?? [];
	}

	public function getTravels() {
		return $this->travels;
	}
}

class Company 
{
	public const URL = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies";
	private $companies = [];

	public function __construct() {
		$this->initCompanies();
	}

	public function initCompanies() {
		$ch = curl_init(self::URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->companies = json_decode(curl_exec($ch), true) ?? [];
	}

	public function getTotalSum(ITravel $travel, $parentId = '0') {
    	$tree = [];

    	foreach($this->companies as $company) {
    		if($company['parentId'] == $parentId) {
    			$children = $this->getTotalSum($travel, $company['id']);
    			if ($children) {
    				$prices = array_column($children, 'cost');
    				
    				$company['cost'] = array_sum($prices);
    				$company['children'] = $children;
    				
    			} else {
    				$company['children'] = [];
    			}
    			
    			 if ($company['children'] == []) {
 			        $travelsFound = array_filter($travel->getTravels(), function ($travel) use ($company) {
			        	return $travel['companyId'] == $company['id'];
			    	}); 
			    	$prices = array_column($travelsFound, 'price');
    				$company['cost'] = array_sum($prices);
    			}
    	
				$tree[] = $company; 
    		}
    	}

    	return $tree;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);

		$travels = new Travel();
		$company = new Company();
		$summary = $company->getTotalSum($travels);

		echo json_encode($summary);

        echo 'Total time: '.  (microtime(true) - $start);
    }
}
(new TestScript())->execute();