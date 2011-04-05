<?php

class IndexController extends Custom_Controller_Action
{
 
    public function init()
    {
        /* Initialize action controller here */
    }
 
    public function indexAction()
    {
		$Regions = new Searchdata_Model_Regions();
		// Ukraine has ID = 1
		$CountryCodeUkraine = 1;
		$this->view->regions = $Regions->getItems($CountryCodeUkraine);
		
		// get recent additions
		$problemsIndex = new Search_Model_Problems();
		$this->view->lastItems = $problemsIndex->getLastItems();
    }
    
    public function postAction()
    {
    	$this->_helper->layout->disableLayout();
    	
    	/**
    	 * Save data to database and retrieve record ID
    	 */
		$mobileAppData = $this->_request->getPost();
		
		$Problems = new Search_Model_Problems();
		
		try {

			$elem = $Problems->createItem(
				$mobileAppData['msg'],
				$mobileAppData['long'], 
				$mobileAppData['lat'],
				$mobileAppData['type']
			);
			
		} catch (Search_Model_ProblemsException $e) {
			
			$this->fail_message = $e->getMessage();
			return;
		}
		
		// connect to URL shorterer and generate beautifull short URL
		// TODO: this should be moved to model level
		$this->view->shortUrl = $this->view->url(array('city' => 'тернопіль', 'problem' => 'інші-скарги', 'id' => $elem->id) ,'city_problem_item');
   }
}