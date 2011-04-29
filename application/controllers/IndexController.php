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
		// TODO: move this outside of controller
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
		$data = $this->_request->getPost();
		$Problems = new Search_Model_Problems();

		// to allow user to drop the report during some time (~15 minutes) after it was added
		// we should generate a hash value - a special password to drop records from database
		$dropHash = md5(microtime());
		$this->view->dropHash = $dropHash;
		
		try {
			$elem = $Problems->createItem(
				$data['msg'],
				$data['long'], 
				$data['lat'],
				$data['type'],
				$dropHash
			);
		} catch (Search_Model_ProblemsException $e) {
			$this->view->fail_message = $e->getMessage();
			// QUICK FIX: we need to create an error reporting mechanism
			mail(ADMIN_EMAIL, '[POVIDOM-VLADU] Report FAIL at povidom-vladu.org.ua', $e->getMessage());
			return;
		}
		
		// get city and category information
		$Cities = new Searchdata_Model_Cities();
		$cityInfo = $Cities->find($elem->city_id)->current();

		$Categories = new Searchdata_Model_Category();
		$categoryInfo = $Categories->find($elem->category_id)->current();
		
		// connect to URL shorterer and generate beautifull short URL
		// TODO: this should be moved to model level
		$apiKeyGoogleShorter = 'AIzaSyBEIpKhCjmeYGR6SmYjXcW2WEzKu-Pwo6A';
		$GooglShort = new Custom_GoogleShort($apiKeyGoogleShorter);
		
		$url = $this->view->absoluteUrl(array('city' => $cityInfo->seo_name_uk, 'problem' => $categoryInfo->seo_name, 'id' => $elem->id) ,'city_problem_item');
		$this->view->shortUrl = $GooglShort->shorten($url);
   }
   
   /**
    * This is used to remove information
    */
   public function removeAction()
   {
   		$this->_helper->layout->disableLayout();
   		$data = $this->_request->getPost();
   		
   		$Problems = new Search_Model_Problems();
   		
   		try {
   			$Problems->dropReport($data['drophash']);
   		} catch (Search_Model_ProblemsException $e) {
			$this->view->fail_message = $e->getMessage();
			return;
		}
   }

}