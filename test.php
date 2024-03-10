<?php

interface ITravel {
	public function getTravels();
}

class Travel implements ITravel
{
	public const URL = "https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels";
	private $travels = [];

	public function __construct() {
		$ch = curl_init(self::URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$travels   = json_decode(curl_exec($ch), true);
			       
		$this->travels = json_decode($travels, true);
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
		$ch = curl_init(self::URL);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$companies = json_decode(curl_exec($ch), true);

		$this->companies = json_decode($companies, true);
	}

	public function getTotalSum(ITravel $travel, $parentId = '0') {
    	$tree = [];

    	foreach($this->companies as $element) {
    		if($element['parentId'] == $parentId) {
    			$children = $this->getTotalSum($travel, $element['id']);
    			if ($children) {
    				$prices = array_column($children, 'cost');
    				
    				$element['cost'] = array_sum($prices);
    				$element['children'] = $children;
    				
    			} else {
    				$element['children'] = [];
    			}
    			
    			 if ($element['children'] == []) {
 			        $travelsFound = array_filter($travel->getTravels(), function ($company) use ($element) {
			        	return $company['companyId'] == $element['id'];
			    	}); 
			    	$prices = array_column($travelsFound, 'price');
    				$element['cost'] = array_sum($prices);
    			}
    	
				$tree[] = $element; 
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