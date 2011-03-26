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
		$elem = $Problems->createItem(
			$mobileAppData['msg'],
			$mobileAppData['adr'],
			$mobileAppData['long'], 
			$mobileAppData['lat'],
			$mobileAppData['type']
		);
		
		$this->view->id = $elem->id;
   }
}